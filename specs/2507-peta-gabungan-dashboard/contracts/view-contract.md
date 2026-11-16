# View/Interface Contract: Peta Gabungan (Internal)

**Feature**: 2507-peta-gabungan-dashboard
**Date**: 2026-10-19
**Type**: Internal Blade + AJAX contract (OpenKab monolith)

## 1. Halaman awal
- Route: `GET dataspasial/peta` (permission `dataspasial-peta-read`), name `dataspasial.peta`.
- Controller: `PetaGabunganController::index()`.
- View: `resources/views/dataspasial/peta/index.blade.php` (dua blok: filter kiri, peta kanan).
- Partial filter: `resources/views/dataspasial/peta/filter.blade.php`.
- Tidak ada pemilih sektor. Tidak ada kabupaten/desa.

## 2. Endpoint data (AJAX)
`GET dataspasial/peta/data` (permission `dataspasial-peta-read`), name `dataspasial.peta.data`.

**Query**:
- `sektor` (kode sektor, opsional; ditetapkan oleh lapisan DataSpasial aktif)
- `kode_kecamatan` (opsional)
- `periode` (opsional)
- `filter[<kode_isian>]` (opsional, hanya isian `tampil_filter`)

**Respons 200 (selalu JSON; `application/geo+json` bila ada data)**:
```json
{
  "type": "FeatureCollection",
  "features": [ /* GeoJSON features API-04 */ ],
  "meta": {
    "unavailable": false,
    "empty": false,
    "message": null,
    "periode": "2025-06",
    "periods": ["2025-06", "2025-05"],
    "filters": [
      {"kode": "jenis_sekolah", "label": "Jenis Sekolah", "tipe": "pilihan", "opsi": ["Negeri","Swasta"]}
    ],
    "isian_tematik_options": [{"value": "jumlah_siswa", "label": "Jumlah Siswa"}],
    "layers": [
      {"key": "sekolah", "label": "Sekolah", "jenis": "geospasial_lama", "jumlah": 120},
      {"key": "SK0", "label": "Infrastruktur", "jenis": "dataspasial", "jumlah": 45}
    ]
  }
}
```

**Respons saat sumber tidak tersedia**: HTTP 200, `meta.unavailable = true`, `meta.message = "Data sementara tidak tersedia. Silakan coba lagi nanti."`, `features = []`. Tidak pernah menampilkan stack trace.

**Perilaku**: memanggil `DataSpasialApiService::geojsonGabungan()`; bila `sektor` diisi juga memanggil `listSkema(['sektor' => kode])` untuk metadata isian (`tampil_filter`, `tampil_popup`, `tipe`, `satuan`) → `meta.filters`.

## 3. Panel lapisan (klien)
- Turunan dari `features` pada respons:
  - `jenis = geospasial_lama`: kunci `properties.kategori` (fallback `properties.simbol`), fitur `source` `openkab`/`presisi`.
  - `jenis = dataspasial`: kunci `properties.sektor_kode`, fitur dengan `properties.dataspasial != null`.
- Setiap entri: checkbox toggle (tampil/sembunyi) + jumlah titik.
- Memilih lapisan DataSpasial menetapkan `sektor` untuk pemanggilan berikutnya.

## 4. Blok filter (kiri)
- Dropdown **Kecamatan** (hanya satu tingkat).
- Kontrol dinamis untuk setiap `meta.filters` (isian `tampil_filter`): `pilihan` → select (opsi dari `opsi`), `angka` → number, `ya_tidak` → select ya/tidak, `teks`/`tanggal` → input.
- Tombol **Tampilkan** (terapkan) dan **Reset**.

## 5. Popup
- Dipicu oleh `mouseover` (sorot) dan `click` pada titik/klaster.
- Isi: `nama`, sektor/kategori, desa/kecamatan, lalu isian `tampil_popup` + satuan, sumber (`source`/`sumber`, mis. "Data Presisi"), dan tanggal pembaruan (`waktu_input`).

## 6. Legenda
- Skala warna isian tematik (angka) + satuan + entri "Tanpa Data".

## 7. Aset & CSP
- Leaflet 1.9.4 + Leaflet.markercluster 1.5.3 (CDN unpkg, SRI).
- JS: `public/js/dataspasial/peta-gabungan.js` (vanilla; tanpa dependensi jQuery).
- `CustomCspPreset`: STYLE/SCRIPT unpkg leaflet+markercluster; IMG `tile.openstreetmap.org`.

## 8. Kontrak uji
- Feature: halaman (200), endpoint `data` (FeatureCollection + meta), `unavailable` (503/timeout/not_configured) → `meta.unavailable=true` tanpa error, `empty`, tamu diarahkan ke login.
- Browser smoke: menu `/dataspasial/peta` ada dan halaman tampil; keadaan "Data sementara tidak tersedia" tampil saat API tak tersedia.
