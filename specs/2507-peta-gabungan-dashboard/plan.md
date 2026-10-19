# Implementation Plan: Peta Gabungan Geospasial & DataSpasial

**Branch**: `2507-peta-gabungan-dashboard` | **Date**: 2026-10-19 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/2507-peta-gabungan-dashboard/spec.md`

## Summary

Bangun halaman **Peta Gabungan** dua blok: (kiri) blok filter DataSpasial berisi filter **kecamatan** dan kontrol dinamis untuk setiap isian bertanda `tampil_filter` pada lapisan DataSpasial aktif; (blok kanan) peta Leaflet berisi basemap, batas wilayah, **panel lapisan** (kategori geospasial lama seperti sekolah/fasilitas kesehatan/tempat ibadah + lapisan per sektor DataSpasial), titik terklaster, popup detail saat titik **disorot atau diklik**, dan legenda. Semua data dibaca lewat `DataSpasialApiService` (API-04 `GET /api/v1/dataspasial/geojson`) melalui endpoint proxy OpenKab — browser tidak pernah memanggil API Satu Data secara langsung. Tidak ada tabel/model lokal baru.

## Technical Context

**Language/Version**: PHP 8.4, Laravel 13 (OpenKab monolith)
**Primary Dependencies**: `spatie/laravel-permission`, `spatie/csp`, `guzzlehttp/guzzle` (via Laravel HTTP client), `jeroennoten/laravel-adminlte`. Client-side: Leaflet 1.9.4 + Leaflet.markercluster 1.5.3 (CDN), Bootstrap 4.6 + jQuery/AdminLTE 4.
**Storage**: MySQL lokal hanya untuk user/session/peran/pengaturan. Seluruh data sektor/geojson/kategori dibaca dari API Satu Data; **tanpa tabel baru**.
**Testing**: Pest + PHPUnit (`tests/Feature/`), Playwright + MSW 2.14.6 (`tests/Browser/`). Feature test memakai `DatabaseTransactions` + `Http::fake`.
**Target Platform**: Web responsif (desktop + ponsel), peramban modern.
**Project Type**: Laravel monolith (server-rendered Blade + progressive JS).
**Performance Goals**: lancar dengan 1.000+ titik (klaster), pembaruan tanpa muat ulang < 3 detik.
**Constraints**: CSP wajib mengizinkan Leaflet + markercluster; tanpa stack trace ke pengguna (pesan generik "Data sementara tidak tersedia"); "tanpa data" ≠ nol.
**Scale/Scope**: halaman `dataspasial.peta` (US1–US5); mengganti kerangka di `PetaGabunganController` + `resources/views/dataspasial/`; endpoint proxy `GET dataspasial/peta/data` + `GET dataspasial/peta/lapisan` (opsional).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Secure-by-Default** — PASS. Akses via middleware `permission:dataspasial-peta-read`; token API tetap di server (endpoint proxy), tidak ada kredensial di browser; tanpa PII di respons error.
- **II. Test-First & CI Enforcement** — PASS. Feature test (Pest, `DatabaseTransactions` + `Http::fake`) + Browser smoke; lint `pint`/phpcs.
- **III. Audit & Observability** — PASS. Header audit `X-OpenKab-User-Id`/`X-OpenKab-User-Name` diteruskan `DataSpasialApiService`; kegagalan dicatat `Log::warning`; pengguna hanya menerima pesan generik.
- **IV. Contract-First Integration** — PASS. Kontrak konsumsi API-04 didokumentasikan di `contracts/` (bentuk FeatureCollection, `source`, `kategori`, `sektor`, `dataspasial`).
- **IV.** tidak ada endpoint lintas sistem baru yang diekspos keluar.
- **V. Simplicity & Incremental Delivery** — PASS. Satu halaman, memakai service/menu/peran yang sudah ada; tanpa model/tabel/proyek baru; dipecah per user story P1→P3.

Tidak ada pelanggaran yang perlu dijustifikasi.

## Project Structure

### Documentation (this feature)

```text
specs/2507-peta-gabungan-dashboard/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   └── view-contract.md
├── checklists/
│   └── requirements.md
└── tasks.md             # /speckit.tasks output
```

### Source Code (repository root)

```text
app/
├── Http/Controllers/DataSpasial/
│   ├── DataSpasialController.php          # (existing) base, index() view+result
│   └── PetaGabunganController.php         # index(), data(), (lapisan)
├── Policies/CustomCspPreset.php           # whitelist Leaflet + markercluster
└── Services/DataSpasialApiService.php     # geojsonGabungan, listSektor, listSkema, listIsian

resources/views/dataspasial/peta/
├── index.blade.php            # tata letak dua blok (filter kiri + peta/lapisan)
└── filter.blade.php           # blok filter: kecamatan + isian tampil_filter

public/js/dataspasial/
└── peta-gabungan.js           # Leaflet + markercluster: layer panel, popup, legenda (vanilla JS)

routes/web.php                 # dataspasial/peta + dataspasial/peta/data

tests/
├── Feature/DataSpasial/PetaGabunganControllerTest.php
└── Browser/SmokePetaGabunganTest.php
```

**Structure Decision**: Monolith Laravel OpenKab. Rombak `resources/views/dataspasial/peta/` menjadi tata letak dua blok + panel lapisan; pertahankan pola `DataSpasialController` + `DataSpasialApiService`; endpoint proxy baru tetap di bawah permission yang sama.

## Complexity Tracking

Tidak ada pelanggaran constitution — tabel ini tidak diisi.

## Design Decisions (Phase 0/1 ringkas)

Ringkasan keputusan; detail di `research.md` dan `contracts/view-contract.md`.

- **D1 — Tata letak dua blok**: kolom kiri (`col-md-3`) blok filter; kolom kanan (`col-md-9`) peta + legend + panel lapisan.
- **D2 — Panel lapisan dari satu payload GeoJSON**: lapisan kategori geospasial lama diturunkan dari `properties.kategori` (untuk feature `source` `openkab`/`presisi`) dan lapisan DataSpasial dari `properties.sektor_kode`/`properties.sektor_nama` (feature dengan `dataspasial` tidak null). Tidak perlu endpoint baru untuk daftar lapisan.
- **D3 — Filter dinamis**: isian bertanda `tampil_filter` diambil dari skema sektor aktif (`listSkema(['sektor' => kode])`), dikembalikan sebagai `meta.filters` oleh endpoint `peta/data`.
- **D4 — Kecamatan saja**: daftar kecamatan dari endpoint wilayah yang sudah dipakai modul lain (`/api/v1/statistik-web/get-list-kecamatan/...`), tanpa kabupaten/desa.
- **D5 — Popup pada sorot & klik**: `mouseover` menampilkan popup (auto), `click` menahan popup; isi popup dari `properties` + metadata isian (`tampil_popup`), termasuk sumber & tanggal pembaruan.
- **D6 — Proxy server-side**: `GET dataspasial/peta/data` mengembalikan FeatureCollection + `meta` (periods, filters, layers); selalu HTTP 200 dengan flag `unavailable`/`empty` agar UI menampilkan pesan generik.
- **D7 — Tidak ada pemilih sektor**: konteks DataSpasial = lapisan sektor yang dipilih.

## Post-Design Constitution Re-check

- I Secure-by-Default — PASS (proxy server-side, permission tetap).
- II Test-First — PASS (rencana Feature + Browser test; kontrak fixture dari payload nyata).
- III Audit — PASS (header audit via service).
- IV Contract-First — PASS (`contracts/view-contract.md` diperbarui).
- V Simplicity — PASS (tanpa tabel baru, satu halaman).
