<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\CakupanPenggunaService;
use App\Services\DasborDataSpasialService;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DasborDataSpasialController extends DataSpasialController
{
    public function __construct(
        protected DasborDataSpasialService $dasbor,
        protected CakupanPenggunaService $cakupan,
    ) {
        parent::__construct($this->dasbor?->dataspasial ?? app(DataSpasialApiService::class));
    }

    protected function pageTitle(): string
    {
        return 'Dasbor DataSpasial';
    }

    protected function viewName(): string
    {
        return 'dataspasial.dasbor.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return $this->dasbor->fetchRingkasan($request->only([
            'sektor', 'sektor_id', 'periode', 'kode_kabupaten', 'kode_kecamatan', 'kode_desa',
        ]));
    }

    public function index(Request $request): View
    {
        $cacheKey = 'dataspasial:dasbor:filter-meta:v1';
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && isset($cached['sektorList'])) {
            $sektorList = $cached['sektorList'];
            $indikatorBySektor = $cached['indikatorBySektor'] ?? [];
        } else {
            $sektorList = $this->daftarSektor();
            $indikatorBySektor = [];
            foreach ($sektorList as $s) {
                $kode = $s['kode'] ?? null;
                if (! $kode) {
                    continue;
                }
                $skema = $this->dataspasial->listSkema(['sektor' => $kode]);
                if (! $skema->isData() || empty($skema->data)) {
                    continue;
                }
                $attrs = $skema->data[0]['attributes'] ?? $skema->data[0] ?? [];
                $isian = $attrs['isian'] ?? [];
                if (is_array($isian)) {
                    foreach ($isian as $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $ikode = $item['kode'] ?? null;
                        if (! $ikode) {
                            continue;
                        }
                        $indikatorBySektor[$kode][] = [
                            'kode' => (string) $ikode,
                            'label' => $item['label'] ?? $ikode,
                            'satuan' => $item['satuan'] ?? null,
                        ];
                    }
                }
            }
            Cache::put($cacheKey, [
                'sektorList' => $sektorList,
                'indikatorBySektor' => $indikatorBySektor,
            ], now()->addMinutes(30));
        }

        // Cache bersifat global; pembatasan sektor diterapkan per pengguna di sini (issue #1320).
        $sektorAkses = $this->cakupan->sektorAkses((int) $request->user()->id);
        if ($sektorAkses !== null) {
            $sektorList = array_values(array_filter($sektorList, fn ($s) => in_array($s['kode'] ?? null, $sektorAkses, true)));
            $indikatorBySektor = array_intersect_key($indikatorBySektor, array_flip($sektorAkses));
        }

        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'sektorList' => $sektorList,
            'indikatorBySektor' => $indikatorBySektor,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $request = $this->batasiSektor($request);
        if ($request === null) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke sektor ini.'], 403);
        }

        $built = $this->dasbor->buildDashboard($request->all());
        $ringkasanResult = $this->dasbor->fetchRingkasan($request->only(['sektor', 'sektor_id', 'periode', 'kode_kabupaten', 'kode_kecamatan', 'kode_desa']));
        $unavailable = $ringkasanResult->isUnavailable();
        $meta = [
            'unavailable' => $unavailable,
            'empty' => false,
            'message' => $unavailable ? $ringkasanResult->userMessage() : null,
            'periode' => $request->input('periode'),
            'periods' => $built['periods'] ?? [],
            'indikator_terpilih' => $built['indikator_terpilih'] ?? null,
        ];
        if ($unavailable) {
            return response()->json([
                'meta' => $meta,
                'indikator' => [],
                'kartu' => [],
                'grafik' => ['per_kecamatan' => [], 'per_desa' => [], 'per_kategori' => []],
                'tabel' => ['rows' => []],
            ], 200);
        }
        $rows = $built['tabel']['rows'] ?? [];
        $meta['empty'] = count($rows) === 0;

        return response()->json([
            'meta' => $meta,
            'indikator' => $built['indikator'] ?? [],
            'kartu' => $built['kartu'] ?? [],
            'grafik' => $built['grafik'] ?? ['per_kecamatan' => [], 'per_desa' => [], 'per_kategori' => []],
            'tabel' => ['rows' => $rows],
        ], 200);
    }

    /**
     * Terapkan hak akses pengguna pada permintaan dasbor (issue #1320).
     *
     * Tanpa pembatasan, permintaan dibiarkan apa adanya.
     * - Sektor dibatasi: sektor kosong diisi sektor pertama yang diizinkan; sektor di luar hak akses → null (403).
     * - Wilayah dibatasi: filter kabupaten/kecamatan/desa dipaksa sesuai hak akses.
     *
     * @return Request|null null berarti ditolak (403).
     */
    private function batasiSektor(Request $request): ?Request
    {
        $uid = (int) $request->user()->id;
        $sektorAkses = $this->cakupan->sektorAkses($uid);
        $wilayah = $this->cakupan->wilayahAkses($uid);

        if ($sektorAkses !== null) {
            $kode = (string) $request->input('sektor', '');
            if ($kode === '' && $request->filled('sektor_id')) {
                $kode = (string) ($this->cakupan->kodeSektorDariId((int) $request->input('sektor_id')) ?? '');
                if ($kode === '') {
                    return null; // sektor_id tidak dikenali: tolak, jangan ganti diam-diam
                }
            }
            if ($kode === '') {
                $kode = $sektorAkses[0];
            }
            if (! in_array($kode, $sektorAkses, true)) {
                return null;
            }
            $request->merge(['sektor' => $kode]);
        }

        if ($wilayah !== null) {
            $request->merge($this->cakupan->filterDasbor($wilayah));
        }

        return $request;
    }

    /**
     * Daftar sektor untuk dropdown filter (kosong bila API tidak tersedia).
     *
     * @return array<int, array<string, string>>
     */
    private function daftarSektor(): array
    {
        $result = $this->dataspasial->listSektor();
        $out = [];
        if (! $result->isData()) {
            return $out;
        }

        foreach ($result->data as $item) {
            $attrs = is_array($item) ? ($item['attributes'] ?? $item) : [];
            $kode = $attrs['kode'] ?? null;
            if ($kode === null || $kode === '') {
                continue;
            }
            $out[] = [
                'kode' => (string) $kode,
                'nama' => (string) ($attrs['nama'] ?? $kode),
            ];
        }

        return $out;
    }

    public function ekspor(Request $request): mixed
    {
        $request = $this->batasiSektor($request);
        if ($request === null) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke sektor ini.'], 403);
        }

        $built = $this->dasbor->buildDashboard($request->all());
        $rows = $built['tabel']['rows'] ?? [];
        if ($rows === []) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang cocok dengan saringan.'], 422);
        }

        return response()
            ->view('dataspasial.dasbor.ekspor', ['rows' => $rows])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="dasbor-dataspasial-'.date('Ymd-His').'.xls"');
    }
}
