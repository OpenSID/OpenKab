<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Http\Requests\DataSpasial\ListImporRequest;
use App\Services\CakupanPenggunaService;
use App\Services\ConfigApiService;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialImporListService;
use App\Services\DataSpasialResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Daftar Data Hasil Impor (fitur 009, issue #1319). Hanya baca.
 *
 * Default bebas: pengguna tanpa pembatasan melihat semua wilayah dan sektor.
 * index = halaman dengan filter; data = JSON (baris, paginasi, ringkasan) via AJAX.
 */
class ListImporController extends DataSpasialController
{
    /** Baris per halaman di tampilan (bukan batas API). */
    private const PER_HALAMAN = 25;

    public function __construct(
        DataSpasialApiService $dataspasial,
        protected DataSpasialImporListService $daftar,
        protected ConfigApiService $configApiService,
        protected CakupanPenggunaService $cakupan,
    ) {
        parent::__construct($dataspasial);
    }

    protected function pageTitle(): string
    {
        return 'Daftar Data Hasil Impor';
    }

    protected function viewName(): string
    {
        return 'dataspasial.impor-list.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return DataSpasialResult::empty();
    }

    public function index(Request $request): View
    {
        $uid = (int) $request->user()->id;
        $wilayah = $this->cakupan->wilayahAkses($uid);
        $sektorAkses = $this->cakupan->sektorAkses($uid);

        $sektor = $this->dataspasial->listSektor();
        $sektorTampil = array_values(array_filter(
            $sektor->isData() ? $sektor->data : [],
            fn ($s) => $sektorAkses === null || in_array($s['attributes']['kode'] ?? null, $sektorAkses, true),
        ));

        // Dropdown kecamatan ikut dibatasi bila hak akses wilayah aktif.
        $kecamatan = collect($this->configApiService->kecamatan())
            ->filter(fn ($k) => $wilayah === null
                || ($this->kodeKecamatan($k) !== null && $this->cakupan->kecamatanDalamWilayah($wilayah, $this->kodeKecamatan($k))))
            ->values()
            ->all();

        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'sektors' => $sektorTampil,
            'kecamatans' => $kecamatan,
        ]);
    }

    public function data(ListImporRequest $request): JsonResponse
    {
        $filter = $request->validated();
        $uid = (int) $request->user()->id;

        // Sektor dibatasi hanya bila pengguna punya pembatasan sektor.
        if ($this->cakupan->sektorAkses($uid) !== null) {
            $kodeSektor = $this->kodeSektor((int) $filter['sektor_id']);
            if (! $this->cakupan->bolehSektor($uid, $kodeSektor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke sektor ini.',
                ], 403);
            }
        }

        // Tahun dan versi disaring setelah data diambil (lihat DataSpasialImporListService::saring).
        $baris = $this->daftar->ambilBaris(
            (int) $filter['sektor_id'],
            null,
            $filter['kode_kecamatan'] ?? null,
            isset($filter['versi']) ? (int) $filter['versi'] : null,
        );

        if ($this->daftar->adaGalat()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'pagination' => $this->paginasi(0, 1),
                    'unavailable' => true,
                    'message' => 'Data sementara tidak tersedia.',
                ],
                'ringkasan' => $this->daftar->ringkasan([]),
            ]);
        }

        $disaring = $this->daftar->saring(
            $this->cakupan->saringWilayah($baris, $this->cakupan->wilayahAkses($uid)),
            $filter,
        );
        $halaman = (int) ($filter['page'] ?? 1);
        $total = count($disaring);
        $potongan = array_slice($disaring, ($halaman - 1) * self::PER_HALAMAN, self::PER_HALAMAN);

        return response()->json([
            'data' => array_values($potongan),
            'meta' => [
                'pagination' => $this->paginasi($total, $halaman, count($potongan)),
                'empty' => $total === 0,
            ],
            'ringkasan' => $this->daftar->ringkasan($disaring),
        ]);
    }

    /**
     * Daftar versi skema untuk sektor yang dipilih (isi dropdown Versi). Dibatasi hak akses sektor lewat middleware.
     */
    public function versi(Request $request): JsonResponse
    {
        $data = $request->validate(['sektor_id' => ['required', 'integer', 'min:1']]);

        $kode = $this->kodeSektor((int) $data['sektor_id']);
        if ($kode === null) {
            return response()->json(['data' => []]);
        }

        $hasil = $this->dataspasial->riwayatSkema(['sektor' => $kode]);

        $versi = collect($hasil->isData() ? $hasil->data : [])
            ->map(fn ($v) => [
                // id riwayat versi = skema_versi_id pada API.
                'id' => (int) ($v['id'] ?? 0),
                'nomor_versi' => (int) ($v['attributes']['nomor_versi'] ?? 0),
                'status' => (string) ($v['attributes']['status'] ?? ''),
            ])
            ->filter(fn (array $v) => $v['nomor_versi'] > 0)
            ->unique('nomor_versi')
            ->sortByDesc('nomor_versi')
            ->values()
            ->all();

        return response()->json(['data' => $versi]);
    }

    /**
     * Kode sektor dari id sektor API (untuk dicocokkan dengan hak akses sektor).
     */
    private function kodeSektor(int $sektorId): ?string
    {
        return $this->cakupan->kodeSektorDariId($sektorId);
    }

    /**
     * Kode kecamatan dari item config (objek atau array).
     */
    private function kodeKecamatan(mixed $k): ?string
    {
        $attr = is_object($k) ? (array) $k : $k;

        return isset($attr['kode_kecamatan']) ? (string) $attr['kode_kecamatan'] : null;
    }

    /**
     * @return array{total: int, count: int, per_page: int, current_page: int, total_pages: int}
     */
    private function paginasi(int $total, int $halaman, ?int $count = null): array
    {
        return [
            'total' => $total,
            'count' => $count ?? 0,
            'per_page' => self::PER_HALAMAN,
            'current_page' => $halaman,
            'total_pages' => max(1, (int) ceil($total / self::PER_HALAMAN)),
        ];
    }
}
