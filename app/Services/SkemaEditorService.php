<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatusSkemaEditor;

/**
 * Orkestrasi editor skema kabupaten (KAB-02).
 *
 * Tanggung jawab:
 * 1. Memetakan item API menjadi bentuk yang nyaman dipakai view.
 * 2. Menghasilkan state editor dari skema yang dipilih (`StatusSkemaEditor`).
 * 3. Menyusun badan draf untuk dikirim ke API.
 *
 * ⚠️ **ATURAN PEMBATASAN (research.md R12)** — berkas ini adalah **satu-satunya**
 * tempat yang boleh menyebut NAMA FIELD ASUMSI kontrak (`popup_peta`,
 * `sebagai_filter`, `sebagai_indikator`, `pemetaan`). Blade, controller, dan JS
 * WAJIB memakai nama internal (`popupPeta`, `sebagaiFilter`, `sebagaiIndikator`).
 *
 * ⚠️ **UTANG KONTRAK U2** — tiga penanda tampilan TIDAK ada pada
 * `openapi-fondasi.yaml` v0.1.0. Selama belum ada, `petakanIsian()` membacanya
 * dengan default `false` agar halaman tetap berfungsi.
 *
 * ⚠️ **UTANG KONTRAK U3** — `pemetaan` inline di badan draf adalah asumsi.
 */
class SkemaEditorService
{
    /**
     * Nama field penanda tampilan di API nyata → nama internal Blade/JS.
     *
     * ⚠️ VERIFIKASI LANGSUNG (spec Q8): ketiga penanda ini SUDAH ADA di API
     * dengan nama `tampil_popup` / `tampil_filter` / `tampil_indikator`.
     * Asumsi lama (`popup_peta`/`filter`/`indikator`) tidak pernah dipakai —
     * lihat `contracts/skema-editor-api.md` §6a.4 K4.
     *
     * @var array<string, string>
     */
    private const PENANDA_TAMPILAN = [
        'tampil_popup' => 'popupPeta',
        'tampil_filter' => 'sebagaiFilter',
        'tampil_indikator' => 'sebagaiIndikator',
    ];

    // -----------------------------------------------------------------------
    // Read model
    // -----------------------------------------------------------------------

    /**
     * Petakan satu item `ds_sektor` menjadi bentuk view.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function petakanSektor(array $item): array
    {
        $attributes = $this->attributes($item);

        return [
            'id' => (int) ($item['id'] ?? $attributes['id'] ?? 0),
            'kode' => (string) ($attributes['kode'] ?? ''),
            'nama' => (string) ($attributes['nama'] ?? ''),
            'ikon' => $attributes['ikon'] ?? null,
            'status' => (string) ($attributes['status'] ?? 'aktif'),
            // Counter dari API nyata (§6a.2) — dipakai untuk label versi/draf
            // TANPA perlu memanggil endpoint skema lebih awal.
            'jumlahSkema' => (int) ($attributes['jumlah_versi_skema'] ?? 0),
            'jumlahTerbit' => (int) ($attributes['jumlah_versi_terbit'] ?? 0),
            'adaDraf' => false,
            'nomorVersiDraf' => null,
        ];
    }

    /**
     * Petakan daftar item sektor dan hitung berapa skema/draf tiap sektor (R1).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function petakanDaftarSektor(array $items, array $semuaSkema = []): array
    {
        return array_map(function (array $item) use ($semuaSkema) {
            $sektor = $this->petakanSektor($item);
            $skemaSektor = $this->groupSkemaPerSektor($semuaSkema)[$sektor['id']] ?? [];

            $draf = $this->cariSkema($skemaSektor, StatusSkemaEditor::Draf);

            // Bila counter dari API tidak tersedia, hitung dari daftar skema.
            $jumlahSkema = $sektor['jumlahSkema'] ?: count($skemaSektor);

            return array_merge($sektor, [
                'jumlahSkema' => $jumlahSkema,
                'adaDraf' => $draf !== null || ($jumlahSkema > 0 && $sektor['jumlahTerbit'] === 0),
                'nomorVersiDraf' => $draf['nomorVersi'] ?? null,
            ]);
        }, $items);
    }

    /**
     * Petakan satu item `ds_skema_versi` menjadi bentuk view.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function petakanSektorSkema(array $item): array
    {
        $attributes = $this->attributes($item);
        $sektor = is_array($attributes['sektor'] ?? null) ? $attributes['sektor'] : [];

        // ⚠️ VERIFIKASI LANGSUNG (K3): versi & status berada di
        // `attributes.versi_skema`, BUKAN di root `attributes`.
        // Fallback ke root dipertahankan agar draft `openapi-fondasi.yaml`
        // masih terbaca bila API belum diperbarui.
        $versi = is_array($attributes['versi_skema'] ?? null) ? $attributes['versi_skema'] : [];

        $id = (int) ($versi['id'] ?? $item['id'] ?? 0);
        $sektorId = (int) ($sektor['id'] ?? $versi['sektor_id'] ?? $attributes['sektor_id'] ?? 0);

        return [
            'id' => $id,
            'sektorId' => $sektorId,
            'sektorKode' => (string) ($sektor['kode'] ?? ''),
            'sektorNama' => (string) ($sektor['nama'] ?? ''),
            'nomorVersi' => (int) ($versi['nomor_versi'] ?? $attributes['nomor_versi'] ?? 0),
            'status' => (string) ($versi['status'] ?? $attributes['status'] ?? ''),
            'diterbitkanPada' => $versi['diterbitkan_pada'] ?? $attributes['diterbitkan_pada'] ?? null,
            'catatanPerubahan' => $versi['catatan_perubahan'] ?? $attributes['catatan_perubahan'] ?? null,
            'jumlahIsian' => count(is_array($attributes['isian'] ?? null) ? $attributes['isian'] : []),
        ];
    }

    /**
     * Petakan satu item `ds_isian` menjadi bentuk view dengan NAMA INTERNAL.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function petakanIsian(array $item): array
    {
        $attributes = $this->attributes($item);
        $pemetaan = $this->pemetaanPertama(is_array($attributes['pemetaan'] ?? null) ? $attributes['pemetaan'] : []) ?? [];

        $isian = [
            'id' => isset($item['id']) ? (int) $item['id'] : (isset($attributes['id']) ? (int) $attributes['id'] : null),
            'kode' => (string) ($attributes['kode'] ?? ''),
            'label' => (string) ($attributes['label'] ?? ''),
            'tipe' => (string) ($attributes['tipe'] ?? 'teks'),
            'wajib' => (bool) ($attributes['wajib'] ?? false),
            'satuan' => $attributes['satuan'] ?? null,
            'opsi' => array_values(array_filter(
                is_array($attributes['opsi'] ?? null) ? $attributes['opsi'] : [],
                static fn ($opsi): bool => is_string($opsi) && $opsi !== '',
            )),
            'urutan' => (int) ($attributes['urutan'] ?? 0),
            'diarsipkan' => (bool) ($attributes['diarsipkan'] ?? false),
            // ⚠️ VERIFIKASI LANGSUNG: `sumber` adalah ENUM API
            // (`openSID`/`dataPresisi`/`impor`), BUKAN kode sektor (§6a.5).
            'sumber' => $pemetaan['sumber'] ?? 'manual',
            'rujukan' => $pemetaan['rujukan'] ?? null,
            'aturanHitung' => $pemetaan['aturan_hitung'] ?? null,
        ];

        // Penanda tampilan: nama RESMI dibaca dengan default `false`.
        foreach (self::PENANDA_TAMPILAN as $api => $internal) {
            $isian[$internal] = (bool) ($attributes[$api] ?? false);
        }

        return $isian;
    }

    /**
     * Petakan daftar isian dan urutkan berdasarkan `urutan` (FR-012).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function petakanDaftarIsian(array $items): array
    {
        $isian = array_map(fn (array $item): array => $this->petakanIsian($item), $items);

        usort($isian, static fn (array $a, array $b): int => $a['urutan'] <=> $b['urutan']);

        return $isian;
    }

    /**
     * Petakan daftar `ds_pemetaan` lalu pasang ke isian terkait (FR-023/FR-024).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function petakanPemetaan(array $items): array
    {
        return array_map(function (array $item): array {
            $attributes = $this->attributes($item);

            return [
                'id' => (int) ($item['id'] ?? 0),
                'sumber' => $attributes['sumber'] ?? null,
                'rujukan' => $attributes['rujukan_sumber'] ?? null,
                'aturan_hitung' => $attributes['aturan_hitung'] ?? null,
            ];
        }, $items);
    }

    /**
     * Pemetaan pertama sebuah isian (API mengembalikan array; isian memakai satu).
     *
     * @param  array<int, mixed>  $items
     * @return array<string, mixed>|null
     */
    private function pemetaanPertama(array $items): ?array
    {
        foreach ($items as $item) {
            if (is_array($item)) {
                return $this->petakanPemetaan([$item])[0] ?? null;
            }
        }

        return null;
    }

    /**
     * Tempelkan status sumber nilai pada tiap isian (FR-023).
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @param  array<int, array<string, mixed>>  $pemetaan
     * @return array<int, array<string, mixed>>
     */
    public function terapkanPemetaan(array $isian, array $pemetaan): array
    {
        $perIsian = [];

        foreach ($pemetaan as $peta) {
            if (($peta['sumber'] ?? null) === null || ($peta['sumber'] ?? '') === '') {
                continue;
            }

            $perIsian[$peta['isianId'] ?? 0] = $peta;
        }

        return array_map(function (array $baris) use ($perIsian): array {
            $peta = $perIsian[$baris['id']] ?? null;

            if ($peta === null) {
                return $baris;
            }

            return array_merge($baris, [
                'sumber' => $peta['sumber'],
                'rujukan' => $peta['rujukan'],
                'aturanHitung' => $peta['aturan_hitung'] ?? null,
            ]);
        }, $isian);
    }

    /**
     * Kelompokkan daftar skema global per sektor agar panel skema tidak
     * memanggil API sekali per sektor (R1).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function groupSkemaPerSektor(array $items): array
    {
        $perSektor = [];

        foreach ($items as $item) {
            $skema = $this->petakanSektorSkema($item);
            $perSektor[$skema['sektorId']][] = $skema;
        }

        foreach ($perSektor as &$daftar) {
            usort($daftar, static fn (array $a, array $b): int => $b['nomorVersi'] <=> $a['nomorVersi']);
        }
        unset($daftar);

        return $perSektor;
    }

    /**
     * Petakan satu entri riwayat versi menjadi bentuk daftar skema (Q9).
     *
     * ⚠️ Bentuk `riwayat versi skema` **datar** — berbeda dari bentuk
     * `skema data spasial` yang bersarang di `attributes.versi_skema`.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function petakanRiwayatVersi(array $item): array
    {
        $attributes = $this->attributes($item);

        $sektorId = (int) ($attributes['sektor_id'] ?? 0);
        $id = (int) ($item['id'] ?? $attributes['id'] ?? 0);

        return [
            'id' => $id,
            'sektorId' => $sektorId,
            'nomorVersi' => (int) ($attributes['nomor_versi'] ?? 0),
            'status' => (string) ($attributes['status'] ?? ''),
            'catatanPerubahan' => $attributes['catatan_perubahan'] ?? null,
            'diterbitkanPada' => $attributes['diterbitkan_pada'] ?? null,
            'diterbitkanOleh' => $attributes['diterbitkan_oleh'] ?? null,
            'jumlahIsian' => 0,
        ];
    }

    /**
     * Turunkan `StatusSkemaEditor` dari skema yang dipilih (FR-008a/FR-008b).
     *
     * @param  array<string, mixed>|null  $skemaTerpilih
     * @param  array<int, array<string, mixed>>  $skemaSektor
     */
    public function statusEditor(?array $skemaTerpilih, array $skemaSektor): StatusSkemaEditor
    {
        if ($skemaTerpilih !== null) {
            return StatusSkemaEditor::dari([
                'status' => $skemaTerpilih['status'],
                'jumlah_skema' => count($skemaSektor),
            ]);
        }

        $draf = $this->cariSkema($skemaSektor, StatusSkemaEditor::Draf);

        if ($draf !== null) {
            return StatusSkemaEditor::dari(['status' => $draf['status']]);
        }

        return StatusSkemaEditor::dari([
            'status' => '',
            'jumlah_skema' => count($skemaSektor),
        ]);
    }

    // -----------------------------------------------------------------------
    // Write model
    // -----------------------------------------------------------------------

    /**
     * Susun badan draf untuk dikirim ke API (R2).
     *
     * ⚠️ **NAMA ASUMSI KONTRAK HANYA BOLEH MUNCUL DI SINI** (R12).
     * Input memakai nama internal hasil validasi FormRequest.
     *
     * @param  array<int, array<string, mixed>>  $isian  Hasil `$request->validated()['isian']`
     * @param  array<string, mixed>  $meta  Berisi `sektor`, `nomor_versi`, `catatan_perubahan`
     * @return array<string, mixed>
     */
    public function composeDrafPayload(array $isian, array $meta = []): array
    {
        $urut = 1;

        $isianApi = array_map(function (array $baris) use (&$urut): array {
            $payload = [
                'kode' => $baris['kode'],
                'label' => $baris['label'],
                'tipe' => $baris['tipe'],
                'wajib' => (bool) ($baris['wajib'] ?? false),
                'satuan' => $baris['satuan'] ?? null,
                'opsi' => ($baris['tipe'] ?? '') === 'pilihan' ? array_values($baris['opsi'] ?? []) : null,
                'urutan' => $urut++,
                // Nama RESMI API (verifikasi langsung — lihat §6a.4 K4).
                'tampil_popup' => (bool) ($baris['popupPeta'] ?? false),
                'tampil_filter' => (bool) ($baris['sebagaiFilter'] ?? false),
                'tampil_indikator' => (bool) ($baris['sebagaiIndikator'] ?? false),
                // ⚠️ ASUMSI U3 — pemetaan inline di badan draf.
                'pemetaan' => $this->composePemetaan($baris),
            ];

            return $payload;
        }, $isian);

        return array_merge([
            'catatan_perubahan' => $meta['catatan_perubahan'] ?? null,
            'nomor_versi' => $meta['nomor_versi'] ?? null,
            'isian' => $isianApi,
        ], $meta['sektor'] !== null ? ['sektor' => $meta['sektor']] : []);
    }

    /**
     * @param  array<string, mixed>  $baris
     * @return array{sektor: string|null, rujukan: string|null}|null
     */
    private function composePemetaan(array $baris): ?array
    {
        $sumber = $baris['sumber'] ?? null;

        if ($sumber === null || $sumber === '' || $sumber === 'manual') {
            return null;
        }

        // ⚠️ API menerima pemetaan sebagai ARRAY di dalam tiap isian (verifikasi
        // langsung §6a.3) — bukan objek tunggal seperti asumsi lama.
        return [[
            'id' => 0,
            'sumber' => (string) $sumber,
            'rujukan_sumber' => $baris['rujukan'] ?? null,
            'aturan_hitung' => $baris['aturanHitung'] ?? null,
        ]];
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function attributes(array $item): array
    {
        return is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
    }

    /**
     * @param  array<int, array<string, mixed>>  $daftar
     * @return array<string, mixed>|null
     */
    private function cariSkema(array $daftar, string $status): ?array
    {
        foreach ($daftar as $skema) {
            if (($skema['status'] ?? '') === $status) {
                return $skema;
            }
        }

        return null;
    }
}
