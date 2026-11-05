<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

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
        protected DasborDataSpasialService $dasbor
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

        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'sektorList' => $sektorList,
            'indikatorBySektor' => $indikatorBySektor,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
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
