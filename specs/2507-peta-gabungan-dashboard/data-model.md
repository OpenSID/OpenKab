# Data Model: Peta Gabungan Geospasial & DataSpasial

**Feature**: 2507-peta-gabungan-dashboard
**Date**: 2026-10-19
**Status**: Draft

## 1. Ringkasan
Fitur hanya **mengonsumsi** data API Satu Data (API-04) melalui `DataSpasialApiService`. Tidak ada tabel/model baru di MySQL OpenKab. State UI bersifat klien (lapisan aktif, filter, kecamatan).

## 2. Entitas

### 2.1 Fasilitas (Titik)
- **Sumber**: fitur `geometry.type = Point` pada FeatureCollection API-04.
- **Properti**: `id`, `nama`, `source` (`openkab` | `presisi`), `kategori`/`simbol` (titik geospasial lama), `sektor_kode`/`sektor_nama` (DataSpasial), `kode_desa`/`nama_desa`, `kode_kecamatan`, `kode_kabupaten`, `dataspasial` (objek atau `null`).
- **Aturan**: titik tanpa koordinat valid diabaikan.

### 2.2 Nilai DataSpasial (`dataspasial`)
- **Field**: `nilai`, `sumber`/`sumber_data`, `periode`, `skema_versi_id`, `isian_kode`, `isian_label`, `isian_satuan`, `isian_tipe`, `waktu_input`/`tanggal_pembaruan`.
- **Aturan**: hanya isian dengan `tampil_popup = true` ditampilkan di popup.

### 2.3 Isian (metadata filter/popup)
- **Sumber**: `listSkema(['sektor' => kode])` (isian ikut pada respons).
- **Field**: `kode`, `label`, `tipe` (`angka|teks|pilihan|ya_tidak|tanggal`), `satuan`, `opsi`, `tampil_popup`, `tampil_filter`, `urutan`.
- **Aturan**: `tampil_filter=true` → kontrol filter; `tipe='angka'` → kandidat pewarnaan tematik; `tampil_popup=true` → baris popup.

### 2.4 Sektor (lapisan DataSpasial)
- **Sumber**: `listSektor()`; nama lapisan = `nama`; parameter API = `kode`.
- **Field**: `id`, `kode`, `nama`, `status`.

### 2.5 Lapisan (Layer)
- **Turunan** dari payload GeoJSON:
  - Lapisan kategori geospasial lama: kunci = `kategori` (fallback `simbol`), untuk fitur `source` `openkab`/`presisi`.
  - Lapisan DataSpasial: kunci = `sektor_kode`, untuk fitur dengan `dataspasial` tidak null.
- **Field (UI)**: `key`, `label`, `jenis` (`geospasial_lama` | `dataspasial`), `jumlah`, `aktif`.
- **Aturan**: lapisan aktif menyaring titik yang dirender; lapisan DataSpasial aktif menetapkan konteks isian/filter.

### 2.6 Kecamatan (filter wilayah)
- **Sumber**: endpoint wilayah Satu Data yang sudah dipakai modul lain.
- **Field**: `kode_kecamatan`, `nama_kecamatan`.
- **Aturan**: hanya satu tingkat; tanpa kabupaten/desa.

### 2.7 Wilayah (Desa) — batas tematik
- **Sumber**: fitur `Polygon`/`MultiPolygon` pada FeatureCollection (bila tersedia).
- **Aturan**: desa tanpa nilai → gaya "tanpa data" (bukan nol); nilai nol → kelas skala terendah.

### 2.8 Legenda
- **Turunan**: skala warna + satuan isian tematik + entri "Tanpa Data".

### 2.9 State UI (klien)
- `layerAktif` (set), `kecamatan`, `filter` (map isian→nilai), `isianTematik`, `requestId` terakhir (anti respons basi).

## 3. Validasi
- `periode` format `YYYY-MM` (divalidasi API).
- Filter hanya untuk isian bertanda `tampil_filter`.
- Popup hanya isian bertanda `tampil_popup`.
- "Tanpa data" tidak pernah dipetakan ke kelas nol.
- Koordinat tidak valid dilewati.

## 4. Relasi
- Fasilitas → Lapisan (kategori atau sektor).
- Nilai → Fasilitas + Isian + Periode.
- Sektor → Isian (`tampil_filter`/`tampil_popup`).
- Desa → Nilai (tematik).

## 5. Catatan
- Tanpa migrasi/tabel baru.
- Bentuk FeatureCollection mengikuti API-04 (RFC 7946).
- Klasifikasi tematik: kuantil (default), fallback rentang sama besar.
