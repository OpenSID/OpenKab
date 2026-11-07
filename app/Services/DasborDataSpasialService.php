<?php

declare(strict_types=1);

namespace App\Services;

class DasborDataSpasialService
{
    public function __construct(
        protected DataSpasialApiService $dataspasial
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function buildDashboard(array $query): array
    {
        $filters = $this->extractFilters($query);
        $sektor = $filters['sektor'] ?? null;

        $ringkasanResult = $this->fetchRingkasan($filters);
        $ringkasanItems = [];
        if ($ringkasanResult->isData()) {
            $ringkasanItems = $ringkasanResult->data ?? [];
        }

        if ($sektor !== null && $sektor !== '') {
            $skemaIsian = $this->fetchSkemaIsian($sektor);
        } else {
            // "Semua Sektor" (Q1): gabungkan isian bertanda indikator dari seluruh sektor.
            $skemaIsian = $this->fetchAllSkemaIsian();
        }

        $indikatorList = $this->filterIndikator($skemaIsian);
        $indikatorMap = [];
        foreach ($indikatorList as $i => $isian) {
            $kode = (string) ($isian['kode'] ?? $i);
            $indikatorMap[$kode] = $isian;
        }

        $normalizedRingkasan = $this->normalizeRingkasan($ringkasanItems);
        $kartu = [];
        foreach ($normalizedRingkasan as $item) {
            $kode = (string) ($item['kode'] ?? '');
            if ($kode === '') {
                continue;
            }
            if (! array_key_exists($kode, $indikatorMap)) {
                continue;
            }
            $kartu[] = $this->mergeWithMeta($item, $indikatorMap[$kode]);
        }
        if ($kartu === [] && $indikatorList !== []) {
            foreach ($indikatorList as $isian) {
                $kode = (string) ($isian['kode'] ?? '');
                if ($kode === '') {
                    continue;
                }
                $kartu[] = [
                    'kode' => $kode,
                    'label' => $isian['label'] ?? $kode,
                    'satuan' => $isian['satuan'] ?? null,
                    'ukuran' => $isian['ukuran'] ?? null,
                    'nilai' => null,
                    'tersedia' => false,
                    'keterangan' => null,
                ];
            }
        }

        $nilaiItems = $this->fetchNilai($filters)->data ?? [];
        $normalizedNilai = $this->normalizeNilai($nilaiItems);
        $indikatorTerpilih = (string) ($query['indikator'] ?? '');
        // Indikator harus berasal dari isian sektor yang sedang dipilih (kascading).
        if ($indikatorTerpilih !== '' && $indikatorMap !== [] && ! array_key_exists($indikatorTerpilih, $indikatorMap)) {
            $indikatorTerpilih = '';
        }
        if ($indikatorTerpilih === '' && $indikatorList !== []) {
            $indikatorTerpilih = (string) ($indikatorList[0]['kode'] ?? '');
        }
        if ($indikatorTerpilih === '' && $kartu !== []) {
            $indikatorTerpilih = (string) ($kartu[0]['kode'] ?? '');
        }

        $kodeKecamatan = $filters['kode_kecamatan'] ?? null;
        if ($kodeKecamatan !== null && $kodeKecamatan !== '') {
            $grafik = [
                'per_kecamatan' => [],
                'per_desa' => $this->aggregatePerDesa($normalizedNilai, $indikatorTerpilih),
                'per_kategori' => $this->aggregatePerKategori($normalizedNilai, $indikatorTerpilih),
            ];
        } else {
            $grafik = [
                'per_kecamatan' => $this->aggregatePerKecamatan($normalizedNilai, $indikatorTerpilih),
                'per_desa' => [],
                'per_kategori' => $this->aggregatePerKategori($normalizedNilai, $indikatorTerpilih),
            ];
        }

        $tabelRows = [];
        foreach ($normalizedRingkasan as $item) {
            $kode = (string) ($item['kode'] ?? '');
            if ($kode !== '' && array_key_exists($kode, $indikatorMap)) {
                $tabelRows[] = $this->mergeWithMeta($item, $indikatorMap[$kode]);
            }
        }
        if ($tabelRows === [] && $indikatorList !== []) {
            foreach ($indikatorList as $isian) {
                $kode = (string) ($isian['kode'] ?? '');
                if ($kode === '') {
                    continue;
                }
                $tabelRows[] = [
                    'kode' => $kode,
                    'label' => $isian['label'] ?? $kode,
                    'satuan' => $isian['satuan'] ?? null,
                    'ukuran' => $isian['ukuran'] ?? null,
                    'nilai' => null,
                    'tersedia' => false,
                    'keterangan' => null,
                ];
            }
        }

        // Ringkasan API bersifat per-wilayah (tanpa isian_kode); nilai per isian
        // diagregasi dari endpoint nilai agar kartu & tabel terisi.
        $agregat = $this->aggregatePerIsian($normalizedNilai, $filters['periode'] ?? null);
        $kartu = $this->isiDariAgregat($kartu, $agregat);
        $tabelRows = $this->isiDariAgregat($tabelRows, $agregat);

        return [
            'indikator' => array_values($indikatorMap),
            'kartu' => $kartu,
            'grafik' => $grafik,
            'tabel' => ['rows' => $tabelRows],
            'indikator_terpilih' => $indikatorTerpilih,
            'periods' => $this->periods($normalizedRingkasan, $normalizedNilai),
            'sektor' => $sektor,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function extractFilters(array $query): array
    {
        $filters = [
            'sektor' => $this->stringInputArr($query, 'sektor'),
            'sektor_id' => $this->stringInputArr($query, 'sektor_id'),
            'kode_kabupaten' => $this->stringInputArr($query, 'kode_kabupaten'),
            'kode_kecamatan' => $this->stringInputArr($query, 'kode_kecamatan'),
            'kode_desa' => $this->stringInputArr($query, 'kode_desa'),
            'periode' => $this->stringInputArr($query, 'periode'),
            'indikator' => $this->stringInputArr($query, 'indikator'),
        ];
        if (($filters['sektor'] === null || $filters['sektor'] === '') && $filters['sektor_id'] !== null && $filters['sektor_id'] !== '') {
            $filters['sektor'] = $filters['sektor_id'];
        }

        return array_filter($filters, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @param  array<string, mixed>  $arr
     */
    protected function stringInputArr(array $arr, string $key): ?string
    {
        $value = $arr[$key] ?? null;
        if (is_array($value) || $value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function fetchRingkasan(array $filters): DataSpasialResult
    {
        return $this->dataspasial->ringkasan($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function fetchNilai(array $filters): DataSpasialResult
    {
        return $this->dataspasial->listNilai($filters);
    }

    /**
     * Seluruh isian (semua sektor) saat filter sektor kosong — "Semua Sektor".
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllSkemaIsian(int $maks = 20): array
    {
        $sektors = $this->dataspasial->listSektor()->data ?? [];
        $all = [];
        $hitung = 0;
        foreach ($sektors as $item) {
            $attrs = is_array($item) ? ($item['attributes'] ?? $item) : [];
            $kode = $attrs['kode'] ?? null;
            if ($kode === null || $kode === '' || $hitung >= $maks) {
                continue;
            }
            $hitung++;
            foreach ($this->fetchSkemaIsian((string) $kode) as $isian) {
                $k = $isian['kode'] ?? null;
                if ($k !== null && $k !== '' && ! array_key_exists((string) $k, $all)) {
                    $all[(string) $k] = $isian;
                }
            }
        }

        return array_values($all);
    }

    public function fetchSkemaIsian(string $sektor): array
    {
        $result = $this->dataspasial->listSkema(['sektor' => $sektor]);
        if (! $result->isData()) {
            return [];
        }
        $chosen = null;
        foreach ($result->data as $item) {
            $attributes = $item['attributes'] ?? [];
            $status = $attributes['versi_skema']['status'] ?? $attributes['status'] ?? null;
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
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function normalizeRingkasan(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $attrs = $item['attributes'] ?? $item;
            if (! is_array($attrs)) {
                continue;
            }
            $kode = $this->pick($attrs, ['isian_kode', 'kode', 'indikator', 'isian']);
            $label = $this->pick($attrs, ['isian_label', 'label', 'nama']);
            $satuan = $this->pick($attrs, ['isian_satuan', 'satuan']);
            $ukuran = $this->pick($attrs, ['ukuran', 'jenis', 'agregat']) ?? 'total';
            $nilai = $this->pickNum($attrs, ['total', 'jumlah', 'nilai', 'rata_rata']);
            $periode = $this->pickScalar($attrs, ['periode', 'period']);
            $keterangan = $this->pick($attrs, ['keterangan', 'detail', 'subketerangan', 'keterangan_nilai']);
            if ($kode === null || $kode === '') {
                continue;
            }
            $out[] = [
                'kode' => (string) $kode,
                'label' => $label !== null ? (string) $label : (string) $kode,
                'satuan' => $satuan,
                'ukuran' => (string) $ukuran,
                'nilai' => $nilai,
                'tersedia' => $nilai !== null,
                'periode' => $periode,
                'keterangan' => $keterangan !== null ? (string) $keterangan : null,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function normalizeNilai(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $attrs = $item['attributes'] ?? $item;
            if (! is_array($attrs)) {
                continue;
            }
            $nilai = $this->pickNum($attrs, ['nilai', 'total', 'jumlah']);
            $isianKode = $this->pickScalar($attrs, ['isian_kode', 'kode_isian', 'kode', 'isian']);
            $configId = $this->pickScalar($attrs, ['config_id', 'desa_id', 'kode_desa', 'kode_kemendagri_desa']);
            $kodeKecamatan = $this->pickScalar($attrs, ['kode_kecamatan', 'kode_kemendagri_kecamatan', 'kecamatan_kode']);
            $kodeDesa = $this->pickScalar($attrs, ['kode_desa', 'kode_kemendagri_desa', 'desa_kode', 'config_id']);
            $periode = $this->pickScalar($attrs, ['periode', 'period']);
            $sektor = $this->pickScalar($attrs, ['sektor_kode', 'sektor']);
            if ($nilai === null || $isianKode === null || $isianKode === '') {
                continue;
            }
            $out[] = [
                'isian_kode' => (string) $isianKode,
                'nilai' => $nilai,
                'config_id' => $configId,
                'periode' => $periode,
                'kode_kecamatan' => $kodeKecamatan ? (string) $kodeKecamatan : null,
                'kode_desa' => $kodeDesa ? (string) $kodeDesa : ($configId ? (string) $configId : null),
                'sektor' => $sektor ? (string) $sektor : null,
            ];
        }

        return $out;
    }

    /**
     * Agregat per isian_kode. Tanpa filter periode dipakai periode terbaru
     * pada isian tersebut agar tidak menjumlahkan lintas periode.
     *
     * @param  array<int, array<string, mixed>>  $nilaiItems
     * @return array<string, array{total: float, jumlah: int, periode: ?string}>
     */
    public function aggregatePerIsian(array $nilaiItems, ?string $periode = null): array
    {
        $latest = [];
        foreach ($nilaiItems as $n) {
            $kode = (string) ($n['isian_kode'] ?? '');
            $p = (string) ($n['periode'] ?? '');
            if ($kode === '' || ($periode !== null && $periode !== '' && $p !== $periode)) {
                continue;
            }
            if (! isset($latest[$kode]) || strcmp($p, $latest[$kode]) > 0) {
                $latest[$kode] = $p;
            }
        }

        $out = [];
        foreach ($nilaiItems as $n) {
            $kode = (string) ($n['isian_kode'] ?? '');
            if ($kode === '' || ! isset($latest[$kode]) || (string) ($n['periode'] ?? '') !== $latest[$kode]) {
                continue;
            }
            $out[$kode] ??= ['total' => 0.0, 'jumlah' => 0, 'periode' => $latest[$kode] !== '' ? $latest[$kode] : null];
            $out[$kode]['total'] += (float) $n['nilai'];
            $out[$kode]['jumlah']++;
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, array{total: float, jumlah: int, periode: ?string}>  $agregat
     * @return array<int, array<string, mixed>>
     */
    protected function isiDariAgregat(array $rows, array $agregat): array
    {
        foreach ($rows as $i => $row) {
            if (($row['nilai'] ?? null) !== null) {
                continue;
            }
            $a = $agregat[(string) ($row['kode'] ?? '')] ?? null;
            if ($a === null) {
                continue;
            }
            $rata = in_array(strtolower((string) ($row['ukuran'] ?? '')), ['rata_rata', 'rata-rata', 'avg', 'average'], true);
            $rows[$i]['nilai'] = round($rata && $a['jumlah'] > 0 ? $a['total'] / $a['jumlah'] : $a['total'], 4);
            $rows[$i]['tersedia'] = true;
            $rows[$i]['periode'] ??= $a['periode'];
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nilaiItems
     * @return array<int, array<string, mixed>>
     */
    public function aggregatePerKecamatan(array $nilaiItems, string $indikatorTerpilih): array
    {
        $map = [];
        foreach ($nilaiItems as $n) {
            if ((string) ($n['isian_kode'] ?? '') !== $indikatorTerpilih) {
                continue;
            }
            $kec = (string) ($n['kode_kecamatan'] ?? ($n['kec'] ?? 'lainnya'));
            if ($kec === '') {
                $kec = 'lainnya';
            }
            $map[$kec]['label'] = $map[$kec]['label'] ?? $kec;
            $map[$kec]['nilai'] = ($map[$kec]['nilai'] ?? 0) + (float) $n['nilai'];
        }

        $out = array_map(fn ($k, $v) => ['kode' => $k, 'label' => $v['label'], 'nilai' => round((float) $v['nilai'], 4)], array_keys($map), array_values($map));

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nilaiItems
     * @return array<int, array<string, mixed>>
     */
    public function aggregatePerDesa(array $nilaiItems, string $indikatorTerpilih): array
    {
        $map = [];
        foreach ($nilaiItems as $n) {
            if ((string) ($n['isian_kode'] ?? '') !== $indikatorTerpilih) {
                continue;
            }
            $desa = (string) ($n['kode_desa'] ?? ($n['config_id'] ?? 'lainnya'));
            if ($desa === '') {
                $desa = 'lainnya';
            }
            $map[$desa]['label'] = $map[$desa]['label'] ?? $desa;
            $map[$desa]['nilai'] = ($map[$desa]['nilai'] ?? 0) + (float) $n['nilai'];
        }

        return array_map(fn ($k, $v) => ['kode' => $k, 'label' => $v['label'], 'nilai' => round((float) $v['nilai'], 4)], array_keys($map), array_values($map));
    }

    /**
     * @param  array<int, array<string, mixed>>  $nilaiItems
     * @return array<int, array<string, mixed>>
     */
    public function aggregatePerKategori(array $nilaiItems, string $indikatorTerpilih): array
    {
        $map = [];
        foreach ($nilaiItems as $n) {
            if ((string) ($n['isian_kode'] ?? '') !== $indikatorTerpilih) {
                continue;
            }
            $kat = (string) ($n['kategori'] ?? ($n['sektor'] ?? 'lainnya'));
            if ($kat === '') {
                $kat = 'lainnya';
            }
            $map[$kat]['nilai'] = ($map[$kat]['nilai'] ?? 0) + (float) $n['nilai'];
        }

        return array_map(fn ($k, $v) => ['kode' => $k, 'label' => $k, 'nilai' => round((float) $v['nilai'], 4)], array_keys($map), array_values($map));
    }

    /**
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<int, array<string, mixed>>
     */
    public function filterIndikator(array $isian): array
    {
        $out = [];
        foreach ($isian as $item) {
            $tampil = $item['tampil_indikator'] ?? $item['sebagai_indikator'] ?? $item['indikator'] ?? $item['tampil_filter'] ?? null;
            if ($tampil === false || $tampil === 0 || $tampil === '0') {
                // jika eksplisit false, tetap boleh ditampilkan sebagai indikator default jika tidak ada yang true
                // tapi ikuti skema: kalau ada isian, izinkan semua untuk dropdown indikator agar bisa dipilih
                $tampil = true;
            } elseif ($tampil === null) {
                $tampil = true;
            }
            $out[] = [
                'kode' => $item['kode'] ?? null,
                'label' => $item['label'] ?? ($item['kode'] ?? null),
                'satuan' => $item['satuan'] ?? null,
                'ukuran' => null,
                'tipe' => $item['tipe'] ?? 'angka',
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $a
     * @param  array<int, array<string, mixed>>  $b
     * @return array<int, string>
     */
    public function periods(array $a, array $b): array
    {
        $p = [];
        foreach (array_merge($a, $b) as $x) {
            $per = $x['periode'] ?? null;
            if (is_string($per) && $per !== '' && ! in_array($per, $p, true)) {
                $p[] = $per;
            }
        }
        rsort($p);

        return $p;
    }

    /**
     * @param  array<string, mixed>  $primary
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function mergeWithMeta(array $primary, array $meta): array
    {
        $merged = $primary;
        if (! isset($merged['satuan']) || $merged['satuan'] === null) {
            $merged['satuan'] = $meta['satuan'] ?? null;
        }
        if (! isset($merged['label']) || $merged['label'] === null) {
            $merged['label'] = $meta['label'] ?? ($merged['kode'] ?? null);
        }
        if (! isset($merged['ukuran']) || empty($merged['ukuran'])) {
            $merged['ukuran'] = $meta['ukuran'] ?? 'total';
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @param  array<int, string>  $keys
     */
    protected function pick(array $attrs, array $keys): mixed
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $attrs) && $attrs[$k] !== null && $attrs[$k] !== '') {
                return $attrs[$k];
            }
        }

        return null;
    }

    /**
     * Seperti pick(), tetapi selalu menghasilkan skalar: nilai berbentuk
     * array/objek (mis. `sektor: {kode, nama}`) dipetakan ke kode/id/nama-nya.
     *
     * @param  array<string, mixed>  $attrs
     * @param  array<int, string>  $keys
     */
    protected function pickScalar(array $attrs, array $keys): string|int|float|null
    {
        $value = $this->pick($attrs, $keys);

        if (is_array($value)) {
            foreach (['kode', 'id', 'nama'] as $inner) {
                if (isset($value[$inner]) && is_scalar($value[$inner]) && $value[$inner] !== '') {
                    return $value[$inner];
                }
            }

            return null;
        }

        return is_scalar($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @param  array<int, string>  $keys
     */
    /**
     * @param  array<string, mixed>  $attrs
     * @param  array<int, string>  $keys
     */
    protected function pickNum(array $attrs, array $keys): ?float
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $attrs) && $attrs[$k] !== null && $attrs[$k] !== '') {
                if (is_numeric($attrs[$k])) {
                    return (float) $attrs[$k];
                }
            }
        }

        return null;
    }
}
