<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Enums\StatusSkemaEditor;
use App\Enums\TipeIsian;
use App\Http\Requests\DataSpasial\StoreDrafSkemaRequest;
use App\Http\Requests\DataSpasial\StoreSektorRequest;
use App\Http\Requests\DataSpasial\TerbitSkemaRequest;
use App\Http\Requests\DataSpasial\UpdateSektorRequest;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use App\Services\SkemaEditorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Menu Skema DataSpasial — editor skema kabupaten (KAB-02 / #1297).
 *
 * Ketentuan yang berlaku:
 * - HANYA membaca/menulis lewat `DataSpasialApiService` (FR-004/SC-010);
 * - daftar skema baru muncul SETELAH admin memilih sektor (FR-007a);
 * - penerbitan memakai KODE sektor pada path, bukan id (FR-041);
 * - kegagalan API diterjemahkan menjadi pesan generik, bukan halaman error
 *   (FR-005/FR-021).
 */
class SkemaDataSpasialController extends DataSpasialController
{
    public function __construct(
        DataSpasialApiService $dataspasial,
        private readonly SkemaEditorService $editor,
    ) {
        parent::__construct($dataspasial);
    }

    protected function pageTitle(): string
    {
        return 'Skema DataSpasial';
    }

    protected function viewName(): string
    {
        return 'dataspasial.skema.index';
    }

    /**
     * Data awal untuk kerangka & komponen tiga-keadaan.
     *
     * Halaman editor memerlukan daftar sektor sebagai sumber kebenarannya,
     * sehingga `fetch()` membaca sektor (bukan daftar skema seperti kerangka
     * sebelumnya).
     */
    protected function fetch(Request $request): DataSpasialResult
    {
        return $this->dataspasial->listSektor(['page[size]' => 200]);
    }

    public function index(Request $request): View
    {
        // ⚠️ KUNJUNGAN PERTAMA (FR-007a-1/Q7): yang dipanggil HANYA daftar sektor.
        $resultSektor = $this->fetch($request);
        $daftarSektor = $this->editor->petakanDaftarSektor($resultSektor->data);

        $sektorTerpilih = $this->sektorTerpilih($request, $daftarSektor);

        // ⚠️ FR-007a-1 — endpoint daftar skema yang membutuhkan parameter sektor
        // DIPASTIKAN GAGAL bila dipanggil tanpa sektor. Karena itu setelah
        // kunjungan pertama ia **tidak boleh** dipanggil sama sekali, bukan
        // sekadar "dipanggil lalu hasilnya diabaikan".
        $resultSkema = null;
        $skemaSektor = [];
        $skemaTerpilih = null;
        $isian = [];
        $resultDetail = null;

        if ($sektorTerpilih !== null) {
            // ⚠️ Q9 — daftar skema diambil dari endpoint RIWAYAT VERSI.
            // `GET /skema?sektor=` hanya mengembalikan SATU versi (aktif) sehingga
            // tidak bisa menjadi sumber daftar. Parameternya KODE sektor.
            $resultRiwayat = $this->dataspasial->riwayatSkema([
                'sektor' => $sektorTerpilih['kode'],
            ]);

            $skemaSektor = array_map(
                fn (array $item): array => $this->editor->petakanRiwayatVersi($item),
                $resultRiwayat->data
            );

            // Urut dari versi terbaru (API sudah mengurutkan `nomor_versi` DESC).
            $skemaTerpilih = $this->skemaTerpilih($request, $skemaSektor);

            // ⚠️ K2 — isian hanya diambil bila sebuah versi benar-benar dipilih.
            if ($skemaTerpilih !== null) {
                // ⚠️ K1: endpoint versi aktif memakai parameter `sektor` (WAJIB),
                // dan responsnya sudah memuat `isian[]` + `pemetaan[]`.
                $resultSkema = $this->dataspasial->listSkema([
                    'sektor' => $sektorTerpilih['id'],
                ]);

                [$isian, $resultDetail] = $this->muatIsian($resultSkema);
            } else {
                $isian = [];
                $resultDetail = null;
            }
        }

        // Status global: bila sektor tidak dapat diperoleh, seluruh tombol mati.
        if ($resultSektor->isUnavailable()) {
            $statusEditor = StatusSkemaEditor::fromValue(StatusSkemaEditor::BelumTersedia);
        } elseif ($skemaTerpilih === null) {
            $statusEditor = $this->editor->statusEditor(null, $skemaSektor);
        } else {
            $statusEditor = $this->editor->statusEditor($skemaTerpilih, $skemaSektor);
        }

        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'result' => $resultSektor,
            'resultSektor' => $resultSektor,
            'resultSkema' => $resultSkema,
            'resultDetail' => $resultDetail,
            'daftarSektor' => $daftarSektor,
            'sektorTerpilih' => $sektorTerpilih,
            'skemaSektor' => $skemaSektor,
            'skemaTerpilih' => $skemaTerpilih,
            'isian' => $isian,
            'statusEditor' => $statusEditor,
            'daftarSektorSumber' => $daftarSektor,
            'tipeIsian' => TipeIsian::class,
        ]);
    }

    /**
     * Tambah sektor baru (FR-007d).
     *
     * ⚠️ UTANG KONTRAK U1 — `POST /dataspasial/sektor` belum ada di kontrak
     * v0.1.0. `404`/`405` dari API otomatis menjadi pesan "data sementara tidak
     * tersedia" lewat pemetaan `DataSpasialResult` (FR-005).
     */
    public function storeSektor(StoreSektorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // ⚠️ Keunikan nama sektor diperiksa API (spec Q13) — bukan di sini.
        // Validasi bentuk sudah dijaga `StoreSektorRequest`.
        $result = $this->dataspasial->buatSektor([
            'nama' => trim((string) $validated['nama']),
            'ikon' => $validated['ikon'] ?? null,
        ]);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial: pembuatan sektor gagal', [
                'reason' => $result->reason?->value,
                'http_status' => $result->httpStatus,
            ]);

            return back()
                ->withInput($request->only('nama', 'ikon'))
                ->with('error', $result->userMessage());
        }

        return redirect()
            ->route('dataspasial.skema', array_filter([
                'sektor' => $result->first()['attributes']['kode'] ?? null,
            ]))
            ->with('success', 'Sektor berhasil disimpan.');
    }

    /**
     * Simpan draf skema (FR-029 … FR-033).
     */
    public function storeDraf(StoreDrafSkemaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $body = $this->editor->composeDrafPayload($validated['isian'] ?? [], [
            'sektor' => $validated['sektor'] ?? null,
            'nomor_versi' => $validated['nomor_versi'] ?? null,
            'catatan_perubahan' => $validated['catatan_perubahan'] ?? null,
        ]);

        $result = $this->dataspasial->buatDrafSkema($body);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial: penyimpanan draf gagal', [
                'sektor' => $validated['sektor'] ?? null,
                'reason' => $result->reason?->value,
                'http_status' => $result->httpStatus,
            ]);

            return back()
                ->withInput()
                ->with('error', $result->userMessage());
        }

        return redirect()
            ->route('dataspasial.skema', array_filter([
                'sektor' => $validated['sektor'] ?? null,
                'skema' => $result->first()['id'] ?? null,
            ]))
            ->with('success', 'Draf skema berhasil disimpan.');
    }

    /**
     * Ubah sektor yang sudah ada (US7, FR-007d-4 … FR-007d-9).
     *
     * ⚠️ UTANG KONTRAK U5 — `PUT /dataspasial/sektor/{id}` belum ada di kontrak
     * v0.1.0 maupun API hidup. `404`/`405` otomatis menjadi pesan "data
     * sementara tidak tersedia" dan daftar sektor tidak berubah (FR-007d-7).
     *
     * ⚠️ **Kode sektor tidak pernah dikirim** — kode bersifat baca-saja
     * (FR-007d-5).
     */
    public function updateSektor(UpdateSektorRequest $request, int $sektor): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->dataspasial->ubahSektor($sektor, [
            'nama' => trim((string) $validated['nama']),
            'ikon' => $validated['ikon'] ?? null,
        ]);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial: perubahan sektor gagal', [
                'sektor_id' => $sektor,
                'reason' => $result->reason?->value,
                'http_status' => $result->httpStatus,
            ]);

            return back()
                ->withInput($request->only('nama', 'ikon'))
                ->with('error', $result->userMessage());
        }

        $kode = $result->first()['attributes']['kode'] ?? $request->query('sektor');

        return redirect()
            ->route('dataspasial.skema', array_filter(['sektor' => $kode]))
            ->with('success', 'Sektor berhasil diperbarui.');
    }

    /**
     * Terbitkan draf skema menjadi versi `terbit` (US8, FR-041 … FR-044).
     *
     * ⚠️ UTANG KONTRAK U6 — `POST /dataspasial/skema/{sektor}/terbit` belum ada
     * di kontrak v0.1.0 maupun API hidup. Kegagalan **tidak boleh** mengubah
     * draf yang sudah ada (FR-043).
     *
     * ⚠️ Path memakai **kode** sektor, bukan id numerik.
     */
    public function terbit(TerbitSkemaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $sektorKode = trim((string) $validated['sektor']);

        $result = $this->dataspasial->terbitkanSkema($sektorKode, array_filter([
            'catatan_perubahan' => $validated['catatan_perubahan'] ?? null,
        ], fn ($v): bool => filled($v)));

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial: penerbitan skema gagal', [
                'sektor' => $sektorKode,
                'reason' => $result->reason?->value,
                'http_status' => $result->httpStatus,
            ]);

            return back()
                ->withInput()
                ->with('error', $result->userMessage());
        }

        return redirect()
            ->route('dataspasial.skema', array_filter([
                'sektor' => $sektorKode,
                'skema' => $result->first()['id'] ?? $validated['skema'] ?? null,
            ]))
            ->with('success', 'Skema berhasil diterbitkan.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $daftarSektor
     * @return array<string, mixed>|null
     */
    private function sektorTerpilih(Request $request, array $daftarSektor): ?array
    {
        $kode = (string) $request->query('sektor', '');

        if ($kode === '') {
            return null;
        }

        foreach ($daftarSektor as $sektor) {
            if ($sektor['kode'] === $kode) {
                return $sektor;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $skemaSektor
     * @return array<string, mixed>|null
     */
    private function skemaTerpilih(Request $request, array $skemaSektor): ?array
    {
        $id = (int) $request->query('skema', 0);

        if ($id > 0) {
            foreach ($skemaSektor as $skema) {
                if ($skema['id'] === $id) {
                    return $skema;
                }
            }
        }

        // Tanpa pilihan eksplisit: utamakan draf berjalan, lalu versi terbaru.
        foreach ($skemaSektor as $skema) {
            if ($skema['status'] === StatusSkemaEditor::Draf) {
                return $skema;
            }
        }

        return $skemaSektor[0] ?? null;
    }

    /**
     * Muat isian skema terpilih beserta pemetaannya.
     *
     * @param  array<string, mixed>|null  $skemaTerpilih
     * @return array{0: array<int, array<string, mixed>>, 1: DataSpasialResult|null}
     */
    private function muatIsian(?DataSpasialResult $resultSkema): array
    {
        // ⚠️ K2 — endpoint `GET /skema/{id}` tidak ada (404). Isian ikut pada
        // respons `GET /skema?sektor=`. Bila respons belum ada, tidak ada yang
        // dimuat dan TIDAK ada request sia-sia yang dikirim.
        $item = is_array($resultSkema?->first()) ? $resultSkema->first() : null;

        if ($item === null) {
            return [[], $resultSkema];
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $itemIsian = is_array($atribut['isian'] ?? null) ? $atribut['isian'] : [];

        return [$this->editor->petakanDaftarIsian($itemIsian), $resultSkema];
    }
}
