<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use App\Services\PetaSkemaPopupService;
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
    public function data(Request $request, PetaSkemaPopupService $popup): JsonResponse
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
        $features = $this->withPopupIsian($features, $sektor);
        $periode = $this->stringInput($request, 'periode');
        $features = $popup->susun($features, $periode)['features'];
        if ($sektor !== null || $periode !== null) {
            $filters = $this->filters($request);
            unset($filters['sektor'], $filters['periode']);
            $semua = $this->dataspasial->geojsonGabungan($filters);
            if ($semua->isUnavailable()) {
                foreach ($features as $i => $feature) {
                    $features[$i]['properties']['popup_skema_unavailable'] = true;
                }
            } else {
                $collection = $semua->first();
                $referensi = is_array($collection['features'] ?? null) ? $collection['features'] : [];
                $rincian = $popup->susun($referensi, $periode);
                $lokasi = $rincian['lokasi'];
                $fasilitas = [];
                foreach ($rincian['features'] as $feature) {
                    $props = $feature['properties'] ?? [];
                    $id = $props['id'] ?? null;
                    $kode = $props['sektor_kode'] ?? null;
                    if (is_scalar($id) && is_string($kode) && $kode !== '' && isset($props['popup_skema'])) {
                        $fasilitas[$kode][(string) $id] = [
                            'skema' => $props['popup_skema'],
                            'unavailable' => $props['popup_skema_unavailable'],
                        ];
                    }
                }
                foreach ($features as $i => $feature) {
                    $props = $feature['properties'] ?? [];
                    $id = $props['lokasi_id'] ?? null;
                    $detail = null;
                    if (is_scalar($id) && isset($lokasi[(string) $id])) {
                        $detail = $lokasi[(string) $id];
                    } elseif (is_scalar($props['id'] ?? null) && is_string($props['sektor_kode'] ?? null)) {
                        $detail = $fasilitas[$props['sektor_kode']][(string) $props['id']] ?? null;
                    }
                    if ($detail !== null) {
                        $features[$i]['properties']['popup_skema'] = $detail['skema'];
                        $features[$i]['properties']['popup_skema_unavailable'] = $detail['unavailable'];
                    }
                }
            }
        }

        $payload['features'] = $features;
        $payload['meta'] = array_merge(
            $payload['meta'],
            $this->deriveMeta($features, $this->isianUntukFitur($sektor, $features))
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

            if (! is_array($dataspasial) || $dataspasial === []) {
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
     * `dataspasial` dapat berupa satu entri (objek) atau daftar entri per isian.
     *
     * @return array<int, array<string, mixed>>
     */
    private function entriDataspasial(mixed $dataspasial): array
    {
        if (! is_array($dataspasial) || $dataspasial === []) {
            return [];
        }

        $list = array_is_list($dataspasial) ? $dataspasial : [$dataspasial];

        return array_values(array_filter($list, 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $dataspasial
     */
    private function featureValue(array $dataspasial, string $kode): mixed
    {
        foreach ($this->entriDataspasial($dataspasial) as $entri) {
            $value = $this->entriValue($entri, $kode);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $dataspasial
     */
    private function entriValue(array $dataspasial, string $kode): mixed
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
     * Isian untuk popup/filter. Bila sektor tidak dipilih (mis. halaman Peta
     * Gabungan awal), ambil isian dari seluruh sektor yang muncul pada fitur
     * sehingga lokasi ber-DataSpasial tetap menampilkan informasinya.
     *
     * @param  array<int, array<string, mixed>>  $features
     * @return array<int, array<string, mixed>>
     */
    private function isianUntukFitur(?string $sektor, array $features): array
    {
        if ($sektor !== null) {
            return $this->skemaIsian($sektor);
        }

        $sektorKode = [];
        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $kode = $props['sektor_kode'] ?? null;
            if (! empty($props['dataspasial']) && is_string($kode) && $kode !== '') {
                $sektorKode[$kode] = true;
            }
        }

        $merged = [];
        foreach (array_slice(array_keys($sektorKode), 0, 10) as $kode) {
            foreach ($this->skemaIsian((string) $kode) as $item) {
                $key = (string) ($item['kode'] ?? '');
                if ($key !== '' && ! isset($merged[$key])) {
                    $merged[$key] = $item;
                }
            }
        }

        return array_values($merged);
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

            if (is_array($dataspasial) && $dataspasial !== []) {
                $key = (string) ($props['sektor_kode'] ?? 'dataspasial');
                $label = (string) ($props['sektor_nama'] ?? $props['sektor_kode'] ?? 'DataSpasial');
                $this->addLayer($layers, $key, $label, 'dataspasial');
            } else {
                $key = (string) ($props['kategori'] ?? $props['simbol'] ?? 'lainnya');
                $this->addLayer($layers, $key, $key, 'geospasial_lama');
            }

            $candidates = [$props['periode'] ?? null];
            foreach ($this->entriDataspasial($dataspasial) as $entri) {
                $candidates[] = $entri['periode'] ?? null;
            }
            foreach ($candidates as $period) {
                if (is_string($period) && $period !== '' && ! in_array($period, $periods, true)) {
                    $periods[] = $period;
                }
            }
        }

        rsort($periods);

        return [
            'layers' => array_values($layers),
            'periods' => $periods,
            'filters' => $this->isianFlags($isian, 'tampil_filter'),
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
     * Isian skema yang ditandai (tampil_filter), dalam bentuk ringkas.
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<int, array<string, mixed>>
     */
    private function isianFlags(array $isian, string $flag): array
    {
        $result = [];

        foreach ($isian as $item) {
            if (empty($item[$flag])) {
                continue;
            }

            $result[] = $this->ringkasIsian($item);
        }

        return $result;
    }

    /**
     * Isian popup per fitur. Setiap lokasi hanya menerima isian dari sektornya
     * sendiri (skema sektor fitur, atau entri datanya bila skema tidak tersedia),
     * sehingga data antar-sektor tidak tercampur di popup.
     *
     * @param  array<int, array<string, mixed>>  $features
     * @return array<int, array<string, mixed>>
     */
    private function withPopupIsian(array $features, ?string $sektor): array
    {
        $popupPerSektor = [];

        foreach ($features as $i => $feature) {
            $props = $feature['properties'] ?? [];
            $dataspasial = $props['dataspasial'] ?? null;

            if (! is_array($dataspasial) || $dataspasial === []) {
                continue;
            }

            $kodeSektor = (string) ($props['sektor_kode'] ?? $sektor ?? '');

            if ($kodeSektor !== '' && ! array_key_exists($kodeSektor, $popupPerSektor)) {
                $popupPerSektor[$kodeSektor] = array_map(
                    fn (array $item): array => $this->ringkasIsian($item),
                    array_values(array_filter(
                        $this->skemaIsian($kodeSektor),
                        static fn (array $item): bool => ! empty($item['tampil_popup'])
                    ))
                );
            }

            $items = $kodeSektor !== '' ? ($popupPerSektor[$kodeSektor] ?? []) : [];

            $features[$i]['properties']['popup_isian'] = $items !== []
                ? $items
                : $this->popupDariEntri($dataspasial);
        }

        return $features;
    }

    /**
     * Fallback popup dari entri data milik fitur itu sendiri (tanpa skema).
     *
     * @return array<int, array<string, mixed>>
     */
    private function popupDariEntri(mixed $dataspasial): array
    {
        $result = [];
        $seen = [];

        foreach ($this->entriDataspasial($dataspasial) as $entri) {
            $kode = $entri['isian_kode'] ?? null;
            if ($kode === null || isset($seen[$kode])) {
                continue;
            }

            $seen[$kode] = true;
            $result[] = [
                'kode' => $kode,
                'label' => $entri['isian_label'] ?? $kode,
                'tipe' => $entri['isian_tipe'] ?? 'teks',
                'satuan' => $entri['isian_satuan'] ?? null,
                'opsi' => null,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function ringkasIsian(array $item): array
    {
        return [
            'kode' => $item['kode'] ?? null,
            'label' => $item['label'] ?? ($item['kode'] ?? null),
            'tipe' => $item['tipe'] ?? 'teks',
            'satuan' => $item['satuan'] ?? null,
            'opsi' => $item['opsi'] ?? null,
        ];
    }
}
