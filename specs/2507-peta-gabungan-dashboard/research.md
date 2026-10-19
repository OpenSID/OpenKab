# Research: Peta Gabungan Geospasial & DataSpasial

**Feature**: 2507-peta-gabungan-dashboard
**Date**: 2026-10-19
**Status**: Complete

## 1. Ringkasan
Halaman dua blok: blok filter DataSpasial (kiri) + blok lapisan/peta (kanan). Blok filter berisi filter kecamatan dan isian bertanda `tampil_filter` pada lapisan DataSpasial aktif. Panel lapisan berisi lapisan kategori geospasial lama (sekolah, fasilitas kesehatan, tempat ibadah, dll.) dan lapisan per sektor DataSpasial. Titik menampilkan popup detail saat **disorot atau diklik**. Tidak ada pemilih sektor.

## 2. Fakta dari basis kode
- `PetaGabunganController` (extends `DataSpasialController`): `index()` mengembalikan view + `DataSpasialResult`; `data()` baru mengembalikan JSON FeatureCollection + meta.
- `DataSpasialApiService::geojsonGabungan()` → `GET /api/v1/dataspasial/geojson`; filter `kode_kabupaten`, `kode_kecamatan`, `kode_desa`, `sektor`, `periode`. FeatureCollection dikembalikan apa adanya; `DataSpasialResult::data[0]` = FeatureCollection.
- `listSektor()` → `GET sektor` (memberi `id`, `kode`, `nama`, `status`).
- `listSkema(['sektor' => kode])` → `GET skema` (wajib param `sektor`, bukan `sektor_id`) dan menyertakan isian: `label`, `tipe`, `satuan`, `tampil_popup`, `tampil_filter` (lihat `tests/Feature/DataSpasial/Fixtures.php`, koreksi K1/K2).
- API-04 `GeoJsonController`/`GeoSpasialService`: fitur titik berasal dari `ds_fasilitas` (punya `dataspasial`), titik bawaan OpenSID `lokasi` (`source='openkab'`, punya `kategori`, `simbol`), dan Data Presisi (`source='presisi'`, punya `kategori`). Titik tanpa koordinat valid dikecualikan.
- Legacy: halaman `/plan` (branch `002-tipe-lokasi-import`) memakai pola blok filter (tombol toggle collapse) + wilayah cascade + tombol Tampilkan; JS dijaga dengan `if (!$) return;` dan dijalankan pada `DOMContentLoaded` (jQuery dari Vite `app.js` bersifat deferred).
- CSP `CustomCspPreset` sudah mengizinkan Leaflet + OSM + `databaseGabunganUrl`; markercluster sudah ditambahkan.

## 3. Keputusan

### D-001: Tata letak dua blok
- **Decision**: Kolom kiri (filter) + kolom kanan (peta). Blok filter berisi kecamatan + isian `tampil_filter`; panel lapisan berada pada blok kanan (di sekitar peta).
- **Rationale**: Sesuai klarifikasi pengguna dan acuan tampilan.
- **Alternatives**: filter horizontal di atas peta (ditolak sesuai klarifikasi).

### D-002: Daftar lapisan dari payload GeoJSON
- **Decision**: Turunkan lapisan dari `properties` pada satu payload `geojson`:
  - **Geospasial lama**: kelompokkan berdasarkan `properties.kategori` (fallback `simbol`), untuk fitur `source` `openkab` (titik lokasi OpenSID) dan `presisi`.
  - **DataSpasial**: kelompokkan berdasarkan `properties.sektor_kode`/`sektor_nama`, untuk fitur dengan `properties.dataspasial` tidak null.
- **Rationale**: Satu pemanggilan baca; sesuai epic "satu peta"; tanpa endpoint baru; nama lapisan (sekolah, fasilitas kesehatan, tempat ibadah) berasal dari kategori titik.
- **Alternatives**: panggil `/api/v1/point` terpisah per kategori (ditolak: menambah endpoint + tidak mencakup DataSpasial).

### D-003: Filter dinamis dari isian `tampil_filter`
- **Decision**: Untuk lapisan DataSpasial aktif (sektor `kode`), ambil isian via `listSkema(['sektor' => kode])`; endpoint `peta/data` mengembalikan `meta.filters` berisi isian dengan `tampil_filter=true` (`kode`, `label`, `tipe`, `opsi`).
- **Rationale**: Definisi "filter" ada di metadata isian API; tidak bisa diturunkan dari GeoJSON.
- **Alternatives**: hardcode filter di UI (ditolak: tidak dinamis).

### D-004: Hanya kecamatan
- **Decision**: Satu dropdown kecamatan; daftar dari endpoint wilayah yang sudah dipakai modul lain (`/api/v1/statistik-web/get-list-kecamatan/{kode_kabupaten}` atau `get-list-kecamatan` + identitas). Tidak ada kabupaten/desa.
- **Rationale**: Klarifikasi pengguna.
- **Alternatives**: cascade kab/kec/desa (ditolak).

### D-005: Popup pada sorot dan klik
- **Decision**: `mouseover` membuka popup singkat; `click` menahan popup; isi = identitas + isian `tampil_popup` + satuan + sumber + tanggal pembaruan.
- **Rationale**: Pengguna menyebut "disorot atau diklik".
- **Alternatives**: klik saja (ditolak).

### D-006: Proxy server-side + pesan generik
- **Decision**: Endpoint `GET dataspasial/peta/data` (permission sama) mengembalikan `{type, features, meta}` dengan `meta.unavailable`/`meta.empty`, selalu HTTP 200. Browser tidak memanggil API Satu Data langsung.
- **Rationale**: Menjaga token di server, konsisten dengan modul DataSpasial & uji `DataSpasialUnavailableTest`.
- **Alternatives**: panggil API langsung dari browser (ditolak: ekspos token, melanggar CSP/convention).

### D-007: Tanpa pemilih sektor
- **Decision**: Hapus pemilih sektor; konteks ditentukan lapisan sektor DataSpasial.
- **Rationale**: Klarifikasi pengguna (Opsi B).

### D-008: CSP
- **Decision**: Pertahankan whitelist Leaflet + markercluster di `CustomCspPreset`.
- **Rationale**: Sudah diterapkan; cukup verifikasi.

## 4. Unknowns Resolved
Semua klarifikasi spec terpenuhi: sumber lapisan (keduanya), tanpa sektor, kecamatan saja, popup sorot/klik, tata letak dua blok.

## 5. Alternatives Considered
Lihat D-001..D-008.

## 6. Referensi
`spec.md`, `plan.md`, `contracts/view-contract.md`, `app/Services/DataSpasialApiService.php`, `app/Http/Controllers/DataSpasial/PetaGabunganController.php`, `app/Policies/CustomCspPreset.php`, `tests/Feature/DataSpasial/Fixtures.php`, branch `002-tipe-lokasi-import` (`resources/views/peta/lokasi/`).
