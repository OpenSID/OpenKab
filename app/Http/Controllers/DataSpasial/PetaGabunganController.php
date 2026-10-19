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

        $data = $result->data;
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            $fc = $data[0];
            if (isset($fc['type']) && $fc['type'] === 'FeatureCollection') {
                $payload['type'] = 'FeatureCollection';
                $payload['features'] = $fc['features'] ?? [];
            } else {
                $payload['features'] = [];
            }
        }

        return response()->json($payload, 200, [
            'Content-Type' => 'application/geo+json',
        ]);
    }
}
