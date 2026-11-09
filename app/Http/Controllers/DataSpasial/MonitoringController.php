<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Http\Requests\DataSpasial\MonitoringDataRequest;
use App\Services\DataSpasialResult;
use App\Services\MonitoringDataSpasialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman Monitoring pengisian & kualitas data desa (KAB-07).
 *
 * Kerangka menu lama diperluas mengikuti pola `DasborDataSpasialController`:
 * index = skeleton tanpa data desa (FR-003); data/ekspor via AJAX.
 */
class MonitoringController extends DataSpasialController
{
    public function __construct(
        protected MonitoringDataSpasialService $monitoring,
    ) {
        parent::__construct($this->monitoring->dataspasial);
    }

    protected function pageTitle(): string
    {
        return 'Monitoring Pengisian';
    }

    protected function viewName(): string
    {
        return 'dataspasial.monitoring.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        // Skeleton halaman tidak membaca data desa (FR-003).
        return DataSpasialResult::empty();
    }

    public function index(Request $request): View
    {
        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'result' => $this->fetch($request),
            'sektorList' => $this->daftarSektorTersimpan(),
        ]);
    }

    /**
     * Kartu + tabel + meta (kontrak contracts/monitoring-view-contract.md §2).
     * Validasi konteks via `MonitoringDataRequest` (FR-003: 422 `meta.validation`).
     */
    public function data(MonitoringDataRequest $request): JsonResponse
    {
        $built = $this->monitoring->buildMonitoring($request->validated());

        return response()->json([
            'meta' => $built['meta'],
            'kartu' => $built['kartu'],
            'tabel' => $built['tabel'],
        ]);
    }

    /**
     * Ekspor laporan bulanan CSV (kontrak §3).
     */
    public function ekspor(MonitoringDataRequest $request): StreamedResponse|JsonResponse
    {
        $hasil = $this->monitoring->eksporCsv($request->validated());

        if ($hasil === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang cocok dengan saringan.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $hasil['response'];
    }

    // -----------------------------------------------------------------------

    /**
     * Daftar sektor untuk dropdown filter (di-cache 30 menit, pola dasbor).
     *
     * @return array<int, array<string, string>>
     */
    private function daftarSektorTersimpan(): array
    {
        $cached = Cache::get('kab07:sektor-list:v1');

        if (is_array($cached)) {
            return $cached;
        }

        $out = [];
        $result = $this->dataspasial->listSektor();

        if ($result->isData()) {
            foreach ($result->data as $item) {
                $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
                $kode = $atribut['kode'] ?? null;

                if ($kode === null || $kode === '') {
                    continue;
                }

                $out[] = ['kode' => (string) $kode, 'nama' => (string) ($atribut['nama'] ?? $kode)];
            }
        }

        Cache::put('kab07:sektor-list:v1', $out, now()->addMinutes(30));

        return $out;
    }
}
