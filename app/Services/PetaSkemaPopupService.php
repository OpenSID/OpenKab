<?php

declare(strict_types=1);

namespace App\Services;

class PetaSkemaPopupService
{
    public function __construct(private DataSpasialApiService $dataspasial) {}

    /**
     * @param  array<int, array<string, mixed>>  $features
     * @return array{features: array<int, array<string, mixed>>, lokasi: array<string, array{skema: array, unavailable: bool}>}
     */
    public function susun(array $features, ?string $periode = null): array
    {
        $skemaPerSektor = [];
        $lokasi = [];
        $perFitur = [];
        $versiPerSektor = [];

        foreach ($features as $feature) {
            $props = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $kode = $props['sektor_kode'] ?? null;
            if (! is_string($kode) || $kode === '') {
                continue;
            }
            foreach ($this->entries($props) as $entry) {
                if (isset($entry['skema_versi_id']) && is_scalar($entry['skema_versi_id'])) {
                    $versiPerSektor[$kode][(string) $entry['skema_versi_id']] = true;
                }
            }
        }

        foreach ($features as $index => $feature) {
            $props = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $kode = $props['sektor_kode'] ?? null;
            if (! is_string($kode) || $kode === '') {
                continue;
            }

            if (! array_key_exists($kode, $skemaPerSektor)) {
                $skemaPerSektor[$kode] = $this->skemaTerbit($kode, array_keys($versiPerSektor[$kode] ?? []));
            }

            $skema = $skemaPerSektor[$kode];
            $groups = [];
            $versiFitur = [];
            foreach ($this->entries($props) as $entry) {
                if (isset($entry['skema_versi_id']) && is_scalar($entry['skema_versi_id'])) {
                    $versiFitur[(string) $entry['skema_versi_id']] = true;
                }
            }
            foreach ($skema['versi'] as $id => $versi) {
                if ($versi['isian'] !== [] && ((string) $id === $skema['terbaru'] || isset($versiFitur[(string) $id]))) {
                    $groups[] = $this->kelompok($props, $versi, $periode);
                }
            }

            $detail = ['skema' => $groups, 'unavailable' => $skema['unavailable']];
            $perFitur[$index] = $detail;
            $lokasiId = $props['lokasi_id'] ?? null;
            if (! is_scalar($lokasiId) || (string) $lokasiId === '') {
                continue;
            }

            $key = (string) $lokasiId;
            $lokasi[$key] ??= ['skema' => [], 'unavailable' => false];
            array_push($lokasi[$key]['skema'], ...$groups);
            $lokasi[$key]['unavailable'] = $lokasi[$key]['unavailable'] || $detail['unavailable'];
        }

        foreach ($features as $index => $feature) {
            $props = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $lokasiId = $props['lokasi_id'] ?? null;
            $detail = is_scalar($lokasiId) && (string) $lokasiId !== ''
                ? ($lokasi[(string) $lokasiId] ?? null)
                : ($perFitur[$index] ?? null);
            if ($detail !== null) {
                $features[$index]['properties']['popup_skema'] = $detail['skema'];
                $features[$index]['properties']['popup_skema_unavailable'] = $detail['unavailable'];
            }
        }

        return ['features' => $features, 'lokasi' => $lokasi];
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return array{versi: array<string, array>, terbaru: string|null, unavailable: bool}
     */
    private function skemaTerbit(string $kode, array $ids): array
    {
        $result = $this->dataspasial->listSkema(['sektor' => $kode]);
        $hasil = ['versi' => [], 'terbaru' => null, 'unavailable' => $result->isUnavailable()];
        if ($result->isUnavailable()) {
            return $hasil;
        }
        $terbaru = $this->bacaSkema($result);
        if ($terbaru !== null) {
            $id = (string) $terbaru['versi']['id'];
            $hasil['versi'][$id] = $terbaru;
            $hasil['terbaru'] = $id;
        }

        $lainnya = array_diff(array_map('strval', $ids), array_keys($hasil['versi']));
        if ($lainnya === []) {
            return $hasil;
        }

        $riwayat = $this->dataspasial->riwayatSkema(['sektor' => $kode]);
        $hasil['unavailable'] = $hasil['unavailable'] || $riwayat->isUnavailable();
        foreach ($riwayat->data as $item) {
            $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
            $id = (string) ($item['id'] ?? $atribut['id'] ?? '');
            $nomor = $atribut['nomor_versi'] ?? null;
            if (! in_array($id, $lainnya, true) || ($atribut['status'] ?? null) !== 'terbit'
                || ! is_numeric($nomor) || (int) $nomor < 1) {
                continue;
            }
            $resultVersi = $this->dataspasial->listSkema(['sektor' => $kode, 'versi' => (int) $nomor]);
            $hasil['unavailable'] = $hasil['unavailable'] || $resultVersi->isUnavailable();
            $versi = $this->bacaSkema($resultVersi);
            if ($versi !== null && (string) $versi['versi']['id'] === $id) {
                $hasil['versi'][$id] = $versi;
            }
        }

        return $hasil;
    }

    /**
     * @return array{versi: array<string, mixed>, isian: array<int, array<string, mixed>>}|null
     */
    private function bacaSkema(DataSpasialResult $result): ?array
    {
        $item = $result->first();
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $versi = is_array($atribut['versi_skema'] ?? null) ? $atribut['versi_skema'] : null;

        if (($versi['status'] ?? null) !== 'terbit' || ! isset($versi['id']) || ! is_scalar($versi['id'])) {
            return null;
        }

        $isian = is_array($atribut['isian'] ?? null) ? $atribut['isian'] : [];

        return [
            'versi' => $versi,
            'isian' => array_values(array_filter($isian, static fn ($item): bool => is_array($item)
                && ($item['tampil_popup'] ?? null) === true
                && empty($item['diarsipkan'])
                && is_string($item['kode'] ?? null)
                && $item['kode'] !== '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<int, array<string, mixed>>
     */
    private function entries(array $props): array
    {
        $entries = $props['dataspasial'] ?? [];
        if (! is_array($entries)) {
            return [];
        }

        return array_values(array_filter(array_is_list($entries) ? $entries : [$entries], 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array{versi: array<string, mixed>, isian: array<int, array<string, mixed>>}  $skema
     * @return array<string, mixed>
     */
    private function kelompok(array $props, array $skema, ?string $periode): array
    {
        $nilai = [];
        $periodeTersedia = [];
        $isianPublik = array_fill_keys(array_column($skema['isian'], 'kode'), true);

        foreach ($this->entries($props) as $entry) {
            if (! isset($entry['skema_versi_id']) || ! is_scalar($entry['skema_versi_id'])
                || (string) $entry['skema_versi_id'] !== (string) $skema['versi']['id']) {
                continue;
            }

            $kode = $entry['isian_kode'] ?? null;
            if (! is_string($kode) || ! isset($isianPublik[$kode])) {
                continue;
            }
            $periodeNilai = $entry['periode'] ?? null;
            if ($periodeNilai !== null && ! is_scalar($periodeNilai)) {
                continue;
            }
            $periodeNilai = (string) ($periodeNilai ?? '');
            $periodeTersedia[$periodeNilai] = true;
            $nilai[$kode][$periodeNilai] ??= $entry;
        }

        if ($periode !== null && $periode !== '') {
            $periodeTersedia[$periode] = true;
        }
        $periodeTersedia = array_map('strval', array_keys($periodeTersedia));
        rsort($periodeTersedia, SORT_STRING);
        $periodeAktif = $periode !== null && $periode !== '' ? $periode : ($periodeTersedia[0] ?? null);

        $isian = [];
        foreach ($skema['isian'] as $item) {
            $entry = $periodeAktif !== null ? ($nilai[$item['kode']][$periodeAktif] ?? []) : [];
            $value = $entry['nilai'] ?? null;
            $perPeriode = [];
            foreach ($nilai[$item['kode']] ?? [] as $key => $data) {
                $nilaiPeriode = $data['nilai'] ?? null;
                $perPeriode[] = [
                    'periode' => (string) $key,
                    'nilai' => is_scalar($nilaiPeriode) || $nilaiPeriode === null ? $nilaiPeriode : json_encode($nilaiPeriode, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'sumber' => is_scalar($data['sumber'] ?? null) ? (string) $data['sumber'] : null,
                ];
            }
            $isian[] = [
                'label' => $item['label'] ?? $item['kode'],
                'nilai' => is_scalar($value) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'satuan' => $item['satuan'] ?? null,
                'nilai_periode' => $perPeriode,
            ];
        }

        return [
            'sektor' => $props['sektor_nama'] ?? $props['sektor_kode'],
            'fasilitas' => $props['nama'] ?? null,
            'versi' => $skema['versi']['nomor_versi'] ?? null,
            'periode' => $periodeTersedia,
            'periode_aktif' => $periodeAktif,
            'isian' => $isian,
        ];
    }
}
