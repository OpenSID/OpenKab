# View/Interface Contract: Peta Gabungan (Internal)

**Feature**: 006-peta-gabungan-dashboard  
**Date**: 2026-10-19  
**Type**: Internal Blade + AJAX contract (OpenKab monolith)

## 1. Initial page load
- Route: `GET dataspasial/peta` (permission `dataspasial-peta-read`)
- Controller: `PetaGabunganController@index()` extends `DataSpasialController`
- Data passed to view: `['title'=>$pageTitle(), 'result'=>$this->fetch($request)]`
- View: `resources/views/dataspasial/peta/index.blade.php` extends `layouts.index`, wraps in `<x-dataspasial-status :result="$result">`
- Initial fetch calls `DataSpasialApiService::geojsonGabungan()` with allowed filters from request (`sektor_id,kode_kabupaten,kode_kecamatan,kode_desa`)

## 2. AJAX endpoint (for dynamic updates, no page reload)
- **Route**: `GET dataspasial/peta/data` (same middleware/permission `dataspasial-peta-read`)
- **Purpose**: Return GeoJSON + metadata for current filters to update map client-side (FR-008)
- **Query params**: `sektor_id`, `periode`, `kode_kabupaten`, `kode_kecamatan`, `kode_desa`, plus filter values for isian with `tampil_filter===true` (e.g., `filter[isian_kode]=value`)
- **Response (success, data/empty)**:
  - HTTP 200, `Content-Type: application/geo+json` (or `application/json` with envelope)
  - Body: FeatureCollection from API-04 (or `{'type':'FeatureCollection','features':[]}`) plus optional metadata:
    ```json
    {
      "type": "FeatureCollection",
      "features": [...],
      "meta": {
        "sektor_id": "...",
        "periode": "YYYY-MM",
        "isian_tematik": {"kode":"...","label":"...","tipe":"angka","satuan":"..."},
        "filters": [{"kode":"...","label":"...","values":[...]}],
        "periods": ["2025-06","2025-05"],
        "sectors": [{"id":1,"kode":"SK0","nama":"..."}],
        "unavailable": false
      }
    }
    ```
- **Response (unavailable)**:
  - HTTP 200 (to keep same-origin AJAX simple) with `meta.unavailable=true` and `meta.message = DataSpasialReason::userMessage()`; JS renders unavailable UI in map area (no stack trace). Or HTTP 503 acceptable but prefer 200 to match existing Blade pattern; follow existing tests (they assert Blade partial). Decision: return 200 + envelope with unavailable flag.
- **Behavior**: Uses same service calls (`geojsonGabungan`, plus `listSektor`, `listIsian`, derive periods) server-side; never calls external API from browser.

## 3. UI Components (Blade/JS)
- **Map container**: `#map` (Leaflet). Height responsive (desktop + mobile). 
- **Controls**: sektor dropdown, periode dropdown, thematic isian dropdown (numeric only), isian-filter controls (dynamically for `tampil_filter===true`), layer control (L.control.layers) with basemap + boundary overlay + point overlays.
- **Legend**: `#map-legend` showing classification colors + ranges + satuan + "Tanpa Data".
- **Layer panel**: toggles "Batas Wilayah (Desa)" and facility point groups (by source `openkab`/`presisi` and presence of dataspasial) — matches "panel lapisan: geospasial lama dan DataSpasial dalam satu peta" (spec).
- **Popup**: rendered via `L.popup()` with content built from feature properties + isian metadata (only `tampil_popup===true`).

## 4. JS Assets
- **Leaflet 1.9.4**: `https://unpkg.com/leaflet@1.9.4/dist/leaflet.js`, CSS same.
- **Leaflet.markercluster 1.5.3**: JS + CSS from unpkg (add to CSP).
- **Custom JS**: `public/js/dataspasial/peta-gabungan.js` (new) or inline in `@push('js')` of view. Follow existing static asset convention for non-Vite bundles (project uses `asset()` for DataSpasial static JS). 
- **Markers**: existing red marker icons under `public/assets/img/map/`.

## 5. CSP requirements
- Add to `app/Policies/CustomCspPreset.php`:
  - STYLE: `https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css`, `https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css`
  - SCRIPT: `https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js`
- IMG already allows `data:`, `https://tile.openstreetmap.org/`, `blob:` (sufficient for markercluster images from unpkg CSS).

## 6. Testing contract
- **Feature tests**: `tests/Feature/DataSpasial/PetaGabunganControllerTest.php` (new) modeled on `DataSpasialUnavailableTest.php` — cover unavailable (503/timeout/not_configured), empty, success with FeatureCollection, permission enforcement, AJAX endpoint behavior, "tanpa data" not zero (via fixture shape), rapid filter changes ignore stale (request id).
- **Browser smoke**: extend `SmokeDataSpasialMenuTest.php` or add `SmokePetaGabunganTest.php` — verify menu link works, page renders, unavailable state (server-side) displays correct message. MSW fixtures only if any client-side external calls added (we avoid that).
