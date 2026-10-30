<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Enums\StatusSkemaEditor;
use App\Http\Requests\DataSpasial\StoreDrafSkemaRequest;
use App\Http\Requests\DataSpasial\TerbitSkemaRequest;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use App\Services\SkemaEditorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
     * Data awal untuk kerangka editor dua panel.
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

        // ⚠️ Q22 — tidak ada lagi daftar skema. Setelah sektor dipilih, editor
        // langsung memuat isian skema terakhir via satu pemanggilan
        // `GET /dataspasial/skema?sektor={kode}` (parameter wajib).
        $isian = [];
        $skemaTerpilih = null;

        if ($sektorTerpilih !== null) {
            $resultSkema = $this->dataspasial->listSkema([
                'sektor' => $sektorTerpilih['kode'],
            ]);

            [$isian] = $this->muatIsian($resultSkema);

            $item = is_array($resultSkema?->first()) ? $resultSkema->first() : null;
            $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
            $versi = is_array($atribut['versi_skema'] ?? null) ? $atribut['versi_skema'] : [];
            $skemaTerpilih = [
                'status' => (string) ($versi['status'] ?? ''),
                'jumlah_skema' => 1,
            ];
        }

        // Status global: bila sektor tidak dapat diperoleh, seluruh tombol mati.
        if ($resultSektor->isUnavailable()) {
            $statusEditor = StatusSkemaEditor::BelumTersedia();
        } else {
            $statusEditor = $this->editor->statusEditor($skemaTerpilih, $daftarSektor);
        }

        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'resultSektor' => $resultSektor,
            'daftarSektor' => $daftarSektor,
            'sektorTerpilih' => $sektorTerpilih,
            'skemaTerpilih' => $skemaTerpilih,
            'isian' => $isian,
            'statusEditor' => $statusEditor,
            // Q-empty-sektor: belum ada sektor → editor langsung dalam mode "sektor baru".
            'modeSektorBaru' => $resultSektor->isEmpty(),
        ]);
    }

    /**
     * Simpan draf skema (FR-029 … FR-033, Q18/Q19/Q20/Q23).
     *
     * Sektor dan seluruh isian dikirim dalam satu permintaan
     * `POST /dataspasial/skema`. Kode sektor dibangkitkan sistem dari nama
     * (Q5) dan dikirim sebagai `sektor`; nama sektor dikirim sebagai
     * `sektor_nama`. Minimal satu isian wajib (Q23).
     *
     * Q21 — selalu via AJAX (JSON); balas JSON untuk request AJAX, redirect
     * untuk formulir biasa. Kegagalan → pesan "data sementara tidak tersedia",
     * isi editor utuh, tanpa muat ulang halaman.
     */
    public function storeDraf(StoreDrafSkemaRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $sektorNama = trim((string) ($validated['sektor_nama'] ?? ''));

        // ⚠️ FR-007d-5 — kode sektor TIDAK PERNAH berubah karena nama diubah.
        // Kode yang dikirim klien dipakai apa adanya; kode baru hanya
        // dibangkitkan dari nama bila klien tidak mengirimnya, yaitu pada mode
        // "sektor baru" (Q26/FR-008f).
        $sektorKode = trim((string) ($validated['sektor'] ?? ''));

        if ($sektorKode === '') {
            $sektorKode = $this->editor->kodeSektorDariNama($sektorNama);
        }

        $body = $this->editor->composeDrafPayload($validated['isian'] ?? [], [
            'sektor' => $sektorKode,
            'sektor_nama' => $sektorNama,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $result = $this->dataspasial->buatDrafSkema($body);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial: penyimpanan draf gagal', [
                'sektor' => $sektorKode,
                'reason' => $result->reason?->value,
                'http_status' => $result->httpStatus,
            ]);

            $pesan = $result->userMessage() ?: 'Data sementara tidak tersedia.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $pesan], 503);
            }

            return back()
                ->withInput()
                ->with('error', $pesan);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Draf skema berhasil disimpan.',
                'sektor' => $sektorKode,
                'data' => $result->first(),
            ]);
        }

        return redirect()
            ->route('dataspasial.skema', array_filter([
                'sektor' => $sektorKode,
            ]))
            ->with('success', 'Draf skema berhasil disimpan.');
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
