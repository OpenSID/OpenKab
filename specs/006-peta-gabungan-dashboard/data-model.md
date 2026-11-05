# Data Model: Peta Gabungan Geospasial & DataSpasial

**Feature**: 006-peta-gabungan-dashboard  
**Date**: 2026-10-19  
**Status**: Draft

## 1. Overview
This feature consumes API-04 GeoJSON (FeatureCollection) and existing DataSpasial metadata (sektor/isian/periode) via `DataSpasialApiService`. No new local database tables/models are introduced. UI state is client-side (selected sektor/periode/isian-tematik, active layers, filters).

## 2. Entities (from spec, consumed/derived)

### 2.1 Fasilitas (Titik)
- **Source**: API-04 FeatureCollection Point features (`geometry.type='Point'`)
- **Properties (relevant)**: `id`, `nama`, `source` (`openkab` or `presisi`), `sektor_kode`, `sektor_nama`, `kode_desa`, `kode_kecamatan`, `kode_kabupaten`, `dataspasial` (object|null) with nilai/sumber/skema_versi_id/periode and isian fields; optional `waktu_input`/`tanggal_pembaruan`.
- **Behavior**: Clustered by Leaflet.markercluster. Popup shows identity + only isian with `tampil_popup===true` (from isian metadata) + satuan + sumber + tanggal pembaruan (FR-004/005).

### 2.2 Isian (Sektor metadata)
- **Source**: `DataSpasialApiService::listIsian(['sektor_id'=>...])` (or embedded in skema)
- **Fields**: `kode`, `label`, `tipe` (`angka|teks|pilihan|ya_tidak|tanggal`), `satuan`, `tampil_popup` (bool), `tampil_filter` (bool), `urutan`.
- **Behavior**: Popup uses `tampil_popup`. Filter UI uses `tampil_filter`. Thematic picker allows only `tipe='angka'` (FR-010).

### 2.3 Sektor
- **Source**: `DataSpasialApiService::listSektor()` 
- **Fields**: `id`, `kode`, `nama`, `status`.
- **Behavior**: Primary selector (FR-006). Only active sectors considered; periods/isian filtered by selected sector.

### 2.4 Periode
- **Source**: Derived from FeatureCollection features (`properties.periode` and `properties.dataspasial.periode`) for selected sektor; fallback `listNilai()`/`ringkasan()` to enumerate.
- **Format**: `YYYY-MM` (FR-006). Default to latest with data.

### 2.5 Nilai (Value)
- **Source**: Embedded in FeatureCollection (`properties.dataspasial.nilai`) or via `/dataspasial/nilai`. Includes `sumber_data`/`sumber`, `periode`, `skema_versi_id`, timestamps.
- **Behavior**: Displayed in popup with satuan. Numeric values used for thematic coloring (desa-level aggregation).

### 2.6 Wilayah (Desa) — boundaries
- **Source**: API-04 FeatureCollection Polygon/MultiPolygon features representing desa boundaries (per OpenAPI/GeoJSON gabungan). Identified by `kode_desa`, `nama_desa`, possibly `kode_kecamatan`, `kode_kabupaten`.
- **Behavior**: Thematic fill by selected numeric isian aggregated per desa. States: has value (including 0) → colored by scale; no data → distinct "tanpa data" style (not zero) (FR-011/012). Popup shows desa identity + selected value or "Tanpa data".

### 2.7 Legenda
- **Derived state**: Current classification range colors + satuan (for thematic) + "Tanpa Data" category. Updated on sector/periode/isian change (FR-009).

### 2.8 UI State (client-side)
- **Selected**: `sektor_id`, `periode` (`YYYY-MM`), `isian_kode` (for thematic, numeric only), active overlays (boundaries, point layers), active filters (by `tampil_filter` isian values).
- **Fetch state**: loading (debounced on rapid changes), last request id to ignore stale responses (FR-008).

## 3. Validation Rules
- Periode format `YYYY-MM` (validated upstream; UI enforces via API).
- Thematic requires `tipe='angka'` (UI disables picker otherwise).
- "Tanpa data" must never map to zero color class (enforced in classification mapping).
- Popup only shows isian with `tampil_popup===true` (default true).

## 4. Relationships
- Fasilitas belongs to Sektor (by `sektor_kode`), Desa (by `kode_desa`).
- Isian belongs to Sektor (by sektor context).
- Nilai linked to Fasilitas + Isian (by kode) + Periode.
- Desa boundary linked to values via `kode_desa`.

## 5. Notes
- No new models/tables in OpenKab DB (consumed via API). 
- FeatureCollection shape defined by API-04 (GeoJSON RFC 7946). 
- Classification method: quantile by default; fallback equal interval (assumption).
