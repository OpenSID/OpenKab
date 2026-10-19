# Research: Peta Gabungan Geospasial & DataSpasial

**Feature**: 006-peta-gabungan-dashboard  
**Date**: 2026-10-19  
**Status**: Complete

## 1. Overview

This feature implements the combined map dashboard (KAB-04) displaying both legacy geospatial layers and DataSpasial features (facilities + values) in one Leaflet map, with clustering, layer panel, popup filtering by `tampil_popup` flags, sector/period filters (no reload), and thematic desa coloring by a selected numeric isian where "tanpa data" ≠ zero.

## 2. Known Technical Context (from codebase)

### 2.1 Controller & Routes
- `app/Http/Controllers/DataSpasial/PetaGabunganController.php`: extends `DataSpasialController`, `pageTitle()='Peta Gabungan'`, `viewName()='dataspasial.peta.index'`, `fetch()` calls `$this->dataspasial->geojsonGabungan($request->only(['sektor_id','kode_kabupaten','kode_kecamatan','kode_desa']))`.
- `routes/web.php:522-524`: `GET dataspasial/peta` with `permission:dataspasial-peta-read`, name `dataspasial.peta`.
- Parent `DataSpasialController` returns `view($viewName(), ['title'=>$pageTitle(),'result'=>$this->fetch($request)])` and **never throws** (maps failures to `DataSpasialResult` with `status=unavailable`).

### 2.2 DataSpasial API Service (OpenKab → API Satu Data)
- `app/Services/DataSpasialApiService.php`:
  - Base URL: `config('app.databaseGabunganUrl')` (env `API_DATABASE_GABUNGAN_HOST`), default `https://api-database-gabungan.id/`
  - Token: `Setting::get('database_gabungan_api_key')` (resolved via `resolveToken()`)
  - Endpoint prefix: `/api/v1/dataspasial` (`config('dataspasial.path_prefix')`)
  - Timeout 10s, cache TTL 300s (GET only for `data`/`empty`; `unavailable` never cached)
  - Headers audit: `X-OpenKab-User-Id`, `X-OpenKab-User-Name` (via `userHeaders()`)
  - `geojsonGabungan(array $filters)` → `GET geojson-gabungan` (L535-538)
  - `extractItems()` treats payload with `type==='FeatureCollection'` as special (returns `[$payload]`), so `$result->data[0]` = FeatureCollection array.
  - Write ops invalidate cache via generation bump.

### 2.3 API-04: GeoJSON Gabungan (upstream)
- API-Database-Gabungan exposes `GET /api/v1/dataspasial/geojson` (returns `application/geo+json`, RFC 7946 FeatureCollection). Properties include facility identity, `source` (`openkab`/`presisi`), optional `dataspasial` with nilai/sumber/skema/periode/isian fields. Filters: `kode_kabupaten,kode_kecamatan,kode_desa,sektor,periode`.
- `ds_isian` flags: `tampil_popup` (default 1), `tampil_filter` (default 0), `tipe` (angka/teks/pilihan/ya_tidak/tanggal), `satuan`.

### 2.4 Map stack & assets
- Leaflet **1.9.4** via unpkg CDN. Marker icons in `public/assets/img/map/`, `public/web/img/`.
- **Leaflet.markercluster** not present — required for clustering (FR-002).
- CSP (`app/Policies/CustomCspPreset.php`) allows Leaflet + OSM; must whitelist markercluster CSS/JS/images and allow unpkg.

### 2.5 UI conventions & error states
- Pages extend `layouts.index` (AdminLTE), use `<x-dataspasial-status :result="$result">`.
- `dataspasial.unavailable.blade.php` shows "Data sementara tidak tersedia" (testid `dataspasial-unavailable`) — no technical details (FR-016).
- Feature/Browser tests follow DataSpasial patterns (DatabaseTransactions, Http::fake, SessionState).

## 3. Key Decisions (resolved)

### D-001: Leaflet.markercluster
- **Decision**: Include via CDN (unpkg) with SRI; update `CustomCspPreset` to whitelist markercluster assets.
- **Rationale**: Matches existing CDN pattern, minimal change, supports 1000+ points.

### D-002: AJAX for dynamic updates (no page reload)
- **Decision**: Add AJAX JSON endpoint `GET dataspasial/peta/data` (same permission) returning FeatureCollection (or structured result). Initial server render remains; filter/sector/periode updates call AJAX endpoint to update map without full reload (FR-008).
- **Rationale**: Preserves server-side token/audit logic, avoids calling external API from browser, testable with Http::fake.

### D-003: Basemap + layer panel
- **Decision**: OSM raster tiles; `L.control.layers` with basemaps + overlays for boundaries (polygons) and facility point groups (by source/has-dataspasial). Render from single API-04 FeatureCollection.

### D-004: Thematic desa coloring
- **Decision**: Extract Polygon/MultiPolygon per desa; map `kode_desa` → numeric value for selected `tipe='angka'` isian. Missing value → distinct "no data" style (not zero). Zero → included in scale. Legend includes "Tanpa Data". Default classification: quantile (data-driven); fallback equal interval.
- **Rationale**: Enforces FR-011/012, SC-003.

### D-005: Popup content
- **Decision**: Show identity + only isian with `tampil_popup===true` (default true), include satuan, `sumber_data`/`sumber`, `periode`, `waktu_input`/version info when present. If none → identity only.

### D-006: Filters
- **Decision**: Load sectors via `listSektor()`; derive available periods from features or via `listNilai()`/`ringkasan()`; isian filter list from `listIsian()` (or sector metadata) for `tampil_filter===true`. All updates via AJAX (debounced).

### D-007: Error/empty states in JS
- **Decision**: AJAX JSON returns unavailable/empty; render same UX in map area (`dataspasial-unavailable` / friendly empty message). Never show stack traces.

### D-008: CSP additions
- **Decision**: Whitelist Leaflet.markercluster CSS/JS (unpkg v1.5.3) in `CustomCspPreset`.

## 4. Unknowns Resolved
All clarifications satisfied (classification default quantile, boundaries from FeatureCollection, periods derived from data, isian metadata from API/listIsian).

## 5. Alternatives Considered
- Full server re-render on filter change → violates FR-008 (rejected)
- Direct browser calls to Satu Data API → token exposure, breaks convention (rejected)
- Bundle via Vite instead of CDN → inconsistent with legacy maps (rejected)
- Treat "tanpa data" as transparent/zero-style → violates FR-011/SC-003 (rejected)

## 6. References
Spec: `specs/006-peta-gabungan-dashboard/spec.md`; Service: `app/Services/DataSpasialApiService.php`, `DataSpasialResult.php`; Controller: `PetaGabunganController.php`; CSP: `app/Policies/CustomCspPreset.php`; Tests: `DataSpasialUnavailableTest.php`, `SmokeDataSpasialMenuTest.php`, `Fixtures.php`; API: `docs/spasial/openapi-fondasi.yaml`, API-Database-Gabungan GeoJson*.
