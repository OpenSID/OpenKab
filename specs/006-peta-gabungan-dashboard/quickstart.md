# Quickstart: Peta Gabungan Geospasial & DataSpasial

**Feature**: 006-peta-gabungan-dashboard  
**Date**: 2026-10-19  
**Purpose**: Validation scenarios for acceptance (mirror US1–US5, SC-001–SC-007)

## Prerequisites
- KAB-01 dependencies satisfied (permissions/menu exist; `dataspasial-peta-read` assigned to roles incl. analis opd).
- API-04 reachable (or Http::fake in tests). `API_DATABASE_GABUNGAN_HOST` + `database_gabungan_api_key` configured for manual smoke.
- Leaflet + markercluster assets whitelisted in CSP (updated preset).

## Manual validation scenarios

### US1: Single combined map
1. Login as user with `dataspasial-peta-read`.
2. Open `/dataspasial/peta` (menu "Peta Gabungan").
3. Verify basemap (OSM), batas wilayah (polygons) visible, points from DataSpasial present. 
4. Zoom out → clusters appear with counts; click cluster → zooms to points.
5. Layer control toggles legacy geospasial vs DataSpasial layers and boundaries.
6. With API unavailable → "Data sementara tidak tersedia" shown in map area; layout/nav intact (no stack trace).

### US2: Popup with flagged isian + sumber + tanggal
1. Click a point with `dataspasial` values and `tampil_popup=true` isian.
2. Verify identity (nama, sektor, desa/kec) + each popup isian with nilai + satuan.
3. Verify `sumber`/`sumber_data` and `waktu_input`/`tanggal_pembaruan` shown when present.
4. Sector with no popup isian → popup shows identity only (no errors).
5. Point with no values for selected periode → identity + "nilai belum tersedia untuk periode tersebut" (per acceptance).

### US3: Filters + legend (no reload)
1. Change sektor → map + legend update without full page reload (AJAX). Periods list reflects selected sektor.
2. Change periode → values in popups and thematic update.
3. If `tampil_filter` isian exist → filter controls appear; change filter → map updates (debounced).
4. No data for selection → friendly "tidak ada data untuk pilihan ini" in map area; legend updated.
5. Rapid changes → only last selection reflected (stale responses ignored).

### US4: Thematic desa coloring (tanpa data ≠ nol)
1. Select numeric isian for thematic.
2. Desa with value >0 and ==0 colored by scale; desa with no data → distinct "tanpa data" style (different from all scale colors, including zero).
3. Legend shows scale ranges + satuan + "Tanpa Data".
4. Sector with no numeric isian → thematic picker disabled/unavailable with explanation; points/popup still work.
5. Toggle between periods → colors update accordingly.

### US5: Performance + mobile
1. Load dataset with >=1000 points → pan/zoom smooth (no noticeable jank). Clusters render correctly.
2. Mobile viewport (<=768px): controls, legend, layer panel accessible; popup fits screen; no horizontal scroll.
3. Touch: tap cluster → expands; tap point → popup; close popup works.

## Automated checks (Feature/Browser)
- Feature: `PetaGabunganControllerTest` covers unavailable (503/timeout/not_configured) → unavailable partial, empty state, success returns FeatureCollection, permission denied for unauthorized, AJAX endpoint behavior.
- Browser smoke: `SmokePetaGabunganTest` (or extend menu smoke) verifies `/dataspasial/peta` loads and shows correct title/"Data sementara tidak tersedia" on simulated unavailable.
- Regression: existing `DataSpasialUnavailableTest` and `SmokeDataSpasialMenuTest` still pass.

## Verification of SCs
- SC-001: 1000+ points smooth (manual + synthetic fixtures)
- SC-002: filter changes <3s, 0 full reloads (network tab)
- SC-003: no data style distinct from zero (visual inspection + fixture assertions)
- SC-004: popup shows flagged isian + satuan + sumber + tanggal when present
- SC-005: mobile task flow <1 min
- SC-006: unavailable shows message, no error/stack trace
- SC-007: US1–4 reviewed by System Analyst
