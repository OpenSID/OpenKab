<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Enums\StatusSkemaEditor;
use App\Http\Requests\DataSpasial\StoreDrafSkemaRequest;
use App\Http\Requests\DataSpasial\TerbitSkemaRequest;
use App\Services\AdopsiDesaService;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use App\Services\SkemaEditorService;
use App\Services\SkemaVersiService;
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
        private readonly SkemaVersiService $versi,
        private readonly AdopsiDesaService $adopsi,
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
     * Ringkasan perubahan + peringatan sebelum penerbitan (US1 / US3, FR-010, FR-040).
     *
     * Berisi sekaligus isi layar terbitan: ringkasan perubahan, peringatan
     * dampak/bentrok, daftar versi (US2), dan tabel adopsi desa (US4) sehingga
     * dialog hanya butuh **satu** pemanggilan (FR-046).
     *
     * ⚠️ Tidak ada efek samping: endpoint ini tidak pernah mengirim
     * permintaan terbitan (FR-046).
     */
    public function previewTerbit(Request $request): JsonResponse
    {
        $sektorKode = trim((string) $request->input('sektor', ''));

        if ($sektorKode === '') {
            return response()->json([
                'success' => false,
                'message' => 'Sektor belum dipilih.',
            ], 422);
        }

        $riwayat = $this->versi->riwayatSektor($sektorKode);

        if (! $riwayat['tersedia']) {
            Log::warning('DataSpasial KAB-03: riwayat versi tidak terbaca', [
                'sektor' => $sektorKode,
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->pesanTidakTersedia(),
            ], 503);
        }

        $riwayatVersi = $riwayat['versi'];
        $versiDraf = $this->versi->versiDraf($sektorKode, $riwayatVersi);

        if ($versiDraf === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada draf aktif pada sektor ini. Simpan draf terlebih dahulu.',
            ], 409);
        }

        // Isian draf yang sedang disunting di layar — ringkasan harus
        // mencerminkan apa yang AKAN diterbitkan, bukan isi draf tersimpan (FR-010).
        $isianDraf = $this->isianDrafDariRequest($request);
        $versiTerbit = $this->versi->versiTerbitTerakhir($sektorKode, $riwayatVersi);

        $ringkasan = $isianDraf === null
            ? $this->versi->ringkasanPerubahan($sektorKode, $this->isianDrafTersimpan($sektorKode), $riwayatVersi)
            : $this->versi->ringkasanPerubahan($sektorKode, $isianDraf, $riwayatVersi);

        // FR-016: tidak ada perubahan → tidak boleh diterbitkan.
        if (! $ringkasan['ada_perubahan']) {
            return response()->json([
                'success' => false,
                'tanpa_perubahan' => true,
                'message' => 'Tidak ada perubahan untuk diterbitkan pada draf ini.',
            ], 422);
        }

        // FR-045: pra-pemeriksaan yang pasti ditolak server.
        $pemeriksaan = $this->versi->praPemeriksaanTerbit(
            $sektorKode,
            $isianDraf ?? $this->isianDrafTersimpan($sektorKode),
            $riwayatVersi
        );

        if (! $pemeriksaan['polos']) {
            return response()->json([
                'success' => false,
                'message' => $pemeriksaan['alasan'],
                'isian' => $pemeriksaan['isian'],
            ], 422);
        }

        // US3: dampak & bentrok. Endpoint pra-terbit belum ada (Utang U10) →
        // `unavailable` dan sistem menampilkan "belum dapat dihitung" (FR-043).
        $pratinjau = $this->dataspasial->praTerbit($sektorKode, (int) $versiDraf['nomor_versi']);

        if ($pratinjau->isUnavailable()) {
            Log::warning('DataSpasial KAB-03: pratinjau pra-terbit belum tersedia', [
                'sektor' => $sektorKode,
                'http_status' => $pratinjau->httpStatus,
                'reason' => $pratinjau->reason?->value,
            ]);
        }

        $peringatan = $this->versi->peringatanTerbit($isianDraf ?? $this->isianDrafTersimpan($sektorKode), $pratinjau);

        return response()->json([
            'success' => true,
            'sektor' => $sektorKode,
            'ringkasan' => $ringkasan,
            'peringatan' => $peringatan,
            'riwayat' => $riwayatVersi,
            'bisa_dibandingkan' => count($riwayatVersi) >= 2,
            'versi_terbit' => $versiTerbit === null ? null : [
                'nomor_versi' => $versiTerbit['nomor_versi'],
                'status_label' => $versiTerbit['status_label'],
            ],
        ]);
    }

    /**
     * Riwayat versi satu sektor (US2 / FR-050).
     */
    public function riwayat(Request $request): JsonResponse
    {
        $sektorKode = trim((string) $request->query('sektor', ''));

        if ($sektorKode === '') {
            return response()->json(['success' => false, 'message' => 'Sektor belum dipilih.'], 422);
        }

        $riwayat = $this->versi->riwayatSektor($sektorKode);

        if (! $riwayat['tersedia']) {
            return response()->json(['success' => false, 'message' => $this->pesanTidakTersedia()], 503);
        }

        return response()->json([
            'success' => true,
            'versi' => $riwayat['versi'],
            'bisa_dibandingkan' => count($riwayat['versi']) >= 2,
        ]);
    }

    /**
     * Perbandingan dua versi (US2 / FR-052 … FR-057).
     *
     * Bila detail salah satu versi gagal dimuat, sistem **menyebut versi mana**
     * yang gagal dan tidak menampilkan perbandingan yang menyesatkan (FR-057).
     */
    public function bandingkan(Request $request): JsonResponse
    {
        $sektorKode = trim((string) $request->query('sektor', ''));
        $dari = (int) $request->query('dari', 0);
        $ke = (int) $request->query('ke', 0);

        if ($sektorKode === '' || $dari < 1 || $ke < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih dua versi untuk dibandingkan.',
            ], 422);
        }

        if ($dari === $ke) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih dua versi yang berbeda.',
            ], 422);
        }

        $lama = $this->versi->isianVersi($sektorKode, $dari);
        $baru = $this->versi->isianVersi($sektorKode, $ke);

        if (! $lama['tersedia']) {
            return response()->json([
                'success' => false,
                'gagal_dimuat' => $dari,
                'message' => 'Versi '.$dari.' tidak dapat dimuat, perbandingan tidak ditampilkan.',
            ], 503);
        }

        if (! $baru['tersedia']) {
            return response()->json([
                'success' => false,
                'gagal_dimuat' => $ke,
                'message' => 'Versi '.$ke.' tidak dapat dimuat, perbandingan tidak ditampilkan.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'dari' => $lama['versi'],
            'ke' => $baru['versi'],
            'baris' => $this->versi->bandingkanDuaVersi($lama['isian'], $baru['isian']),
        ]);
    }

    /**
     * Tabel adopsi desa untuk sektor terpilih (US4 / FR-047, FR-060 … FR-063).
     */
    public function adopsi(Request $request): JsonResponse
    {
        $sektorKode = trim((string) $request->query('sektor', ''));

        if ($sektorKode === '') {
            return response()->json(['success' => false, 'message' => 'Sektor belum dipilih.'], 422);
        }

        $sektorId = $this->sektorId($sektorKode);

        $semua = $this->adopsi->ambilSemua();

        if (! $semua['tersedia']) {
            // FR-047: kegagalan tabel TIDAK boleh menggagalkan bagian dialog lain.
            return response()->json([
                'success' => false,
                'message' => $this->pesanTidakTersedia(),
                'baris' => [],
                'ringkasan' => null,
            ], 503);
        }

        $versiTerbaru = $this->adopsi->versiTerbaruSektor($sektorKode);

        $baris = $this->adopsi->saring(
            $this->adopsi->barisSektor($semua['baris'], $sektorId, $versiTerbaru),
            $request->only(['status', 'q'])
        );

        $halaman = $this->adopsi->paginate(
            $baris,
            max(1, (int) $request->query('page', 1))
        );

        return response()->json([
            'success' => true,
            'sektor' => $sektorKode,
            'sektor_id' => $sektorId,
            'versi_terbaru' => $versiTerbaru,
            'baris' => $halaman['baris'],
            'ringkasan' => $halaman['ringkasan'],
            'halaman' => $halaman['halaman'],
            'total_halaman' => $halaman['total_halaman'],
            'total' => $halaman['total'],
            'per_halaman' => $halaman['per_halaman'],
            'pilihan_status' => $this->adopsi->pilihanStatus(),
            'total_api' => $semua['total_api'],
        ]);
    }

    /**
     * Ekspor daftar desa (US4 / FR-066, FR-067).
     *
     * Berkas berisi **seluruh** hasil saringan — bukan hanya halaman aktif —
     * memakai pola `LaporanDesaAktifController::exportExcel()`. Ekspor **tidak
     * pernah** mengirim permintaan terbitan (FR-047).
     */
    public function eksporAdopsi(Request $request)
    {
        $sektorKode = trim((string) $request->query('sektor', ''));

        if ($sektorKode === '') {
            return response()->json(['success' => false, 'message' => 'Sektor belum dipilih.'], 422);
        }

        $sektorId = $this->sektorId($sektorKode);
        $semua = $this->adopsi->ambilSemua();

        if (! $semua['tersedia']) {
            return response()->json(['success' => false, 'message' => $this->pesanTidakTersedia()], 503);
        }

        $ekspor = $this->adopsi->barisEkspor(
            $this->adopsi->barisSektor(
                $semua['baris'],
                $sektorId,
                $this->adopsi->versiTerbaruSektor($sektorKode)
            ),
            $request->only(['status', 'q'])
        );

        // FR-068: hasil kosong → pesan, bukan berkas kosong.
        if ($ekspor['baris'] === []) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada desa yang cocok dengan saringan.',
            ], 422);
        }

        $ringkasan = $this->adopsi->ringkasan($ekspor['baris']);
        $filter = $request->only(['status', 'q']);

        return response()
            ->view('dataspasial.skema.partials.adopsi-ekspor', [
                'baris' => $ekspor['baris'],
                'ringkasan' => $ringkasan,
                'sektorKode' => $sektorKode,
                'filter' => $filter,
                'dipangkas' => $ekspor['dipangkas'],
                'total' => $ekspor['total'],
                'excel' => true,
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="adopsi-skema-'.preg_replace('/[^a-z0-9\-_]/i', '', $sektorKode).'-'.now()->format('Ymd-His').'.xls"');
    }

    /**
     * Terbitkan draf skema menjadi versi `terbit` (US1 / FR-017 … FR-023).
     *
     * ⚠️ Endpoint `POST /dataspasial/skema/{sektor}/terbit` sudah ada di API yang
     * hidup: `409` bila tidak ada draf aktif, `404` bila sektor tidak dikenal,
     * `422` bila draf kosong atau tipe isian terbit berubah — lihat
     * `contracts/penerbitan-adopsi-api.md` §6.
     *
     * ⚠️ `diterbitkan_oleh` **tidak pernah** dikirim: identitas penerbit diisi
     * server dari pengguna terautentikasi (FR-019).
     */
    public function terbit(TerbitSkemaRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $sektorKode = trim((string) $validated['sektor']);

        $result = $this->dataspasial->terbitkanSkema($sektorKode, array_filter([
            'catatan_perubahan' => $validated['catatan_perubahan'] ?? null,
        ], fn ($v): bool => filled($v)));

        if ($result->isUnavailable()) {
            $pesan = $this->pesanTerbitan($result);

            Log::warning('DataSpasial: penerbitan skema gagal', [
                'sektor' => $sektorKode,
                'reason' => $result->reason?->value,
                'http_status' => $result->httpStatus,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $pesan], 503);
            }

            return back()->withInput()->with('error', $pesan);
        }

        // Jejak percobaan terbitan di sisi OpenKab (Prinsip III). API juga
        // mencatat audit resminya sendiri — lihat research.md R11.
        Log::info('DataSpasial KAB-03: skema diterbitkan', [
            'sektor' => $sektorKode,
            'versi_terbit' => $result->first()['attributes']['versi_skema']['nomor_versi'] ?? null,
            'pengakuan_bentrok' => (bool) ($validated['pengakuan_bentrok'] ?? false),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Skema berhasil diterbitkan.',
                'sektor' => $sektorKode,
                'data' => $result->first(),
            ]);
        }

        return redirect()
            ->route('dataspasial.skema', array_filter([
                'sektor' => $sektorKode,
                'skema' => $result->first()['id'] ?? $validated['skema'] ?? null,
            ]))
            ->with('success', 'Skema berhasil diterbitkan.');
    }

    /**
     * Pesan generik saat sumber data bermasalah (FR-005) — tanpa detail teknis.
     */
    private function pesanTidakTersedia(): string
    {
        return 'Data sementara tidak tersedia.';
    }

    /**
     * Terjemahkan penolakan sumber data menjadi pesan yang dapat dipahami admin
     * (FR-020).
     */
    private function pesanTerbitan(DataSpasialResult $result): string
    {
        return match ($result->httpStatus) {
            409 => 'Draf sudah tidak aktif — mungkin sudah diterbitkan oleh admin lain.',
            404 => 'Sektor tidak ditemukan di sumber data.',
            422 => $result->message ?? 'Penerbitan ditolak karena perubahan tidak kompatibel.',
            default => $this->pesanTidakTersedia(),
        };
    }

    /**
     * Isian draf yang dikirim klien (bentuk POST), atau `null` bila tidak dikirim.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function isianDrafDariRequest(Request $request): ?array
    {
        $isian = $request->input('isian');

        if (! is_array($isian) || $isian === []) {
            return null;
        }

        return array_values(array_map(
            fn (array $baris): array => $this->editor->petakanIsian($baris + ['attributes' => $baris]),
            array_filter($isian, 'is_array')
        ));
    }

    /**
     * Isian draf tersimpan di sumber data (dipakai bila klien tidak mengirim).
     *
     * @return array<int, array<string, mixed>>
     */
    private function isianDrafTersimpan(string $sektorKode): array
    {
        $result = $this->dataspasial->listSkema(['sektor' => $sektorKode]);

        if ($result->isUnavailable()) {
            return [];
        }

        $item = is_array($result->first()) ? $result->first() : null;

        if ($item === null) {
            return [];
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $isian = is_array($atribut['isian'] ?? null) ? $atribut['isian'] : [];

        return $this->editor->petakanDaftarIsian($isian);
    }

    /**
     * Id internal sektor dari kode sektor (untuk pencocokan baris adopsi).
     */
    private function sektorId(string $kodeSektor): int
    {
        $result = $this->dataspasial->listSektor(['page[size]' => 200]);

        if ($result->isUnavailable()) {
            return 0;
        }

        foreach ($this->editor->petakanDaftarSektor($result->data) as $sektor) {
            if ($sektor['kode'] === $kodeSektor) {
                return (int) $sektor['id'];
            }
        }

        return 0;
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
