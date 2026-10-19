<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menu Peta Gabungan.
 *
 * Kerangka menu pada fase fondasi ini. Peta geospasial penuh menjadi lingkup
 * KAB-04 (OpenSID/OpenKab#1299).
 */
class PetaGabunganController extends DataSpasialController
{
    protected function pageTitle(): string
    {
        return 'Peta Gabungan';
    }

    protected function viewName(): string
    {
        return 'dataspasial.peta.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return $this->dataspasial->geojsonGabungan($request->only([
            'sektor_id',
            'kode_kabupaten',
            'kode_kecamatan',
            'kode_desa',
            'periode',
        ]));
    }

    public function index(Request $request): View
    {
        $sektor = $this->dataspasial->listSektor();

        return view($this->viewName(), [
            'title'  => $this->pageTitle(),
            'result' => $this->fetch($request),
            'sektor' => $sektor,
        ]);
    }

    /**
     * AJAX endpoint returning GeoJSON + meta for map updates (no full page reload).
     */
    public function data(Request $request): JsonResponse
    {
        $result = $this->dataspasial->geojsonGabungan($request->only([
            'sektor_id',
            'kode_kabupaten',
            'kode_kecamatan',
            'kode_desa',
            'periode',
        ]));

        $payload = [
            'type' => 'FeatureCollection',
            'features' => [],
            'meta' => [
                'sektor_id' => $request->input('sektor_id'),
                'periode' => $request->input('periode'),
                'unavailable' => false,
                'empty' => false,
            ],
        ];

        if ($result->isUnavailable()) {
            $payload['meta']['unavailable'] = true;
            $payload['meta']['message'] = $result->userMessage();

            return response()->json($payload, 200);
        }

        if ($result->isEmpty()) {
            $payload['meta']['empty'] = true;

            return response()->json($payload, 200);
        }

        $features = [];
        $data = $result->data;
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            $fc = $data[0];
            if (isset($fc['type']) && $fc['type'] === 'FeatureCollection') {
                $payload['type'] = 'FeatureCollection';
                $features = $fc['features'] ?? [];
                $payload['features'] = $features;
            }
        }

        $payload['meta'] = array_merge($payload['meta'], $this->deriveMeta($features));
        $payload['meta']['empty'] = count($features) === 0;

        return response()->json($payload, 200, [
            'Content-Type' => 'application/geo+json',
        ]);
    }

    /**
     * Turunkan periode dan pilihan isian tematik (angka) dari fitur yang tersedia.
     *
     * @param  array<int, array<string, mixed>>  $features
     * @return array<string, mixed>
     */
    private function deriveMeta(array $features): array
    {
        $periods = [];
        $isian = [];

        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $ds = $props['dataspasial'] ?? null;

            $period = $ds['periode'] ?? ($props['periode'] ?? null);
            if (is_string($period) && $period !== '' && ! in_array($period, $periods, true)) {
                $periods[] = $period;
            }

            if (is_array($ds)) {
                $kode = $ds['isian_kode'] ?? null;
                if ($kode && ! isset($isian[$kode])) {
                    $isian[$kode] = [
                        'value' => $kode,
                        'label' => $ds['isian_label'] ?? $kode,
                    ];
                }
            }
        }

        rsort($periods);

        return [
            'periods' => $periods,
            'isian_tematik_options' => array_values($isian),
        ];
    }
}
