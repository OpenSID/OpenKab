<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menu Peta Gabungan (KAB-04 / OpenSID/OpenKab#1299).
 *
 * Tata letak dua blok: blok filter (kecamatan + isian bertanda filter) dan blok
 * lapisan/peta. Konteks DataSpasial ditentukan oleh lapisan sektor yang dipilih;
 * tidak ada pemilih sektor terpisah. Seluruh data dibaca dari API Satu Data.
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
        return $this->dataspasial->geojsonGabungan($this->filters($request));
    }

    public function index(Request $request): View
    {
        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'result' => $this->fetch($request),
        ]);
    }

    /**
     * Endpoint proxy GeoJSON + meta untuk pembaruan peta tanpa muat ulang.
     *
     * Selalu HTTP 200; kegagalan ditandai pada `meta.unavailable`/`meta.empty`
     * agar UI menampilkan pesan generik (tanpa stack trace).
     */
    public function data(Request $request): JsonResponse
    {
        $sektor = $this->stringInput($request, 'sektor');
        $result = $this->dataspasial->geojsonGabungan($this->filters($request));

        $payload = [
            'type' => 'FeatureCollection',
            'features' => [],
            'meta' => [
                'unavailable' => false,
                'empty' => false,
                'sektor' => $sektor,
                'periode' => $this->stringInput($request, 'periode'),
            ],
        ];

        if ($result->isUnavailable()) {
            $payload['meta']['unavailable'] = true;
            $payload['meta']['message'] = $result->userMessage();

            return response()->json($payload, 200);
        }

        $features = [];
        if ($result->isData()) {
            $data = $result->data;
            if (isset($data[0]) && is_array($data[0]) && ($data[0]['type'] ?? null) === 'FeatureCollection') {
                $features = $data[0]['features'] ?? [];
            }
        }

        $features = $this->applyIsianFilters($features, (array) $request->input('filter', []));

        $payload['features'] = $features;
        $payload['meta'] = array_merge(
            $payload['meta'],
            $this->deriveMeta($features, $this->skemaIsian($sektor))
        );
        $payload['meta']['empty'] = count($features) === 0;

        return response()->json($payload, 200, [
            'Content-Type' => 'application/geo+json',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function filters(Request $request): array
    {
        $filters = [
            'sektor' => $this->stringInput($request, 'sektor') ?? $this->stringInput($request, 'sektor_id'),
            'kode_kabupaten' => $this->stringInput($request, 'kode_kabupaten'),
            'kode_kecamatan' => $this->stringInput($request, 'kode_kecamatan'),
            'kode_desa' => $this->stringInput($request, 'kode_desa') ?? $this->stringInput($request, 'desa'),
            'desa' => $this->stringInput($request, 'kode_desa') ?? $this->stringInput($request, 'desa'),
            'periode' => $this->stringInput($request, 'periode'),
        ];

        return array_filter($filters, static fn (?string $value): bool => $value !== null && $value !== '');
    }

    private function stringInput(Request $request, string $key): ?string
    {
        $value = $request->input($key);

        if (is_array($value) || $value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * Saring fitur menurut nilai isian yang dipilih (filter[tampil_filter]=nilai).
     *
     * @param  array<int, array<string, mixed>>  $features
     * @param  array<string, mixed>  $isianFilters
     * @return array<int, array<string, mixed>>
     */
    private function applyIsianFilters(array $features, array $isianFilters): array
    {
        $isianFilters = array_filter(
            $isianFilters,
            static fn ($value): bool => $value !== null && $value !== ''
        );

        if ($isianFilters === []) {
            return $features;
        }

        return array_values(array_filter($features, function (array $feature) use ($isianFilters): bool {
            $props = $feature['properties'] ?? [];
            $dataspasial = $props['dataspasial'] ?? null;

            if (! is_array($dataspasial)) {
                return false;
            }

            foreach ($isianFilters as $kode => $expected) {
                $actual = $this->featureValue($dataspasial, (string) $kode);

                if ($actual === null || ! $this->valueMatches($actual, $expected)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param  array<string, mixed>  $dataspasial
     */
    private function featureValue(array $dataspasial, string $kode): mixed
    {
        $map = $dataspasial['nilai_map'] ?? null;
        if (is_array($map) && array_key_exists($kode, $map)) {
            return $map[$kode];
        }

        if (($dataspasial['isian_kode'] ?? null) === $kode) {
            return $dataspasial['nilai'] ?? null;
        }

        return null;
    }

    private function valueMatches(mixed $actual, mixed $expected): bool
    {
        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        return strcasecmp((string) $actual, (string) $expected) === 0;
    }

    /**
     * Metadata isian dari skema sektor aktif (isian ikut pada respons skema).
     *
     * @return array<int, array<string, mixed>>
     */
    private function skemaIsian(?string $sektor): array
    {
        if ($sektor === null) {
            return [];
        }

        $result = $this->dataspasial->listSkema(['sektor' => $sektor]);

        if (! $result->isData()) {
            return [];
        }

        $chosen = null;
        foreach ($result->data as $item) {
            $attributes = $item['attributes'] ?? [];
            $status = $attributes['versi_skema']['status'] ?? null;

            if ($status === 'terbit') {
                $chosen = $attributes;

                break;
            }

            $chosen ??= $attributes;
        }

        $isian = $chosen['isian'] ?? [];

        return is_array($isian) ? $isian : [];
    }

    /**
     * Turunkan lapisan, periode, opsi tematik, filter, dan isian popup.
     *
     * @param  array<int, array<string, mixed>>  $features
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<string, mixed>
     */
    private function deriveMeta(array $features, array $isian): array
    {
        $layers = [];
        $periods = [];

        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $source = $props['source'] ?? null;
            $dataspasial = $props['dataspasial'] ?? null;

            if (is_array($dataspasial)) {
                $key = (string) ($props['sektor_kode'] ?? 'dataspasial');
                $label = (string) ($props['sektor_nama'] ?? $props['sektor_kode'] ?? 'DataSpasial');
                $this->addLayer($layers, $key, $label, 'dataspasial');
                $kategori = $props['kategori'] ?? $props['simbol'] ?? null;
                if ($kategori) {
                    $this->addLayer($layers, 'kategori:'.(string) $kategori, (string) $kategori, 'kategori');
                }
            } else {
                $key = (string) ($props['kategori'] ?? $props['simbol'] ?? 'lainnya');
                $this->addLayer($layers, $key, $key, 'geospasial_lama');
                if ($key !== '') {
                    $this->addLayer($layers, 'kategori:'.$key, $key, 'kategori');
                }
            }

            $period = $dataspasial['periode'] ?? ($props['periode'] ?? null);
            if (is_string($period) && $period !== '' && ! in_array($period, $periods, true)) {
                $periods[] = $period;
            }
        }

        rsort($periods);

        return [
            'layers' => array_values($layers),
            'periods' => $periods,
            'filters' => $this->isianFlags($isian, 'tampil_filter', $features),
            'popup_isian' => $this->isianFlags($isian, 'tampil_popup', $features),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $layers
     */
    private function addLayer(array &$layers, string $key, string $label, string $jenis): void
    {
        if (! isset($layers[$key])) {
            $layers[$key] = [
                'key' => $key,
                'label' => $label,
                'jenis' => $jenis,
                'jumlah' => 0,
            ];
        }

        $layers[$key]['jumlah']++;
    }

    /**
     * Daftar isian menurut penanda (tampil_filter/tampil_popup), dengan fallback
     * dari fitur bila metadata skema tidak tersedia.
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @param  array<int, array<string, mixed>>  $features
     * @return array<int, array<string, mixed>>
     */
    private function isianFlags(array $isian, string $flag, array $features): array
    {
        $result = [];

        foreach ($isian as $item) {
            if (empty($item[$flag])) {
                continue;
            }

            $result[] = [
                'kode' => $item['kode'] ?? null,
                'label' => $item['label'] ?? ($item['kode'] ?? null),
                'tipe' => $item['tipe'] ?? 'teks',
                'satuan' => $item['satuan'] ?? null,
                'opsi' => $item['opsi'] ?? null,
            ];
        }

        if ($result !== []) {
            return $result;
        }

        if ($flag !== 'tampil_popup') {
            return [];
        }

        $seen = [];
        foreach ($features as $feature) {
            $dataspasial = $feature['properties']['dataspasial'] ?? null;
            if (! is_array($dataspasial)) {
                continue;
            }

            $kode = $dataspasial['isian_kode'] ?? null;
            if ($kode === null || isset($seen[$kode])) {
                continue;
            }

            $seen[$kode] = true;
            $result[] = [
                'kode' => $kode,
                'label' => $dataspasial['isian_label'] ?? $kode,
                'tipe' => $dataspasial['isian_tipe'] ?? 'teks',
                'satuan' => $dataspasial['isian_satuan'] ?? null,
                'opsi' => null,
            ];
        }

        return $result;
    }
}
