# Tasks: Peta Gabungan Geospasial & DataSpasial

**Input**: Design documents from `/specs/2507-peta-gabungan-dashboard/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Tests are OPTIONAL per spec; however this feature affects UI map behavior and follows existing DataSpasial patterns (Feature/Browser tests). We include targeted test tasks aligned with existing conventions.

**Organization**: Tasks grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1–US5)
- Include exact file paths in descriptions

## Path Conventions

- Single Laravel monolith (OpenKab): `app/`, `resources/views/`, `public/js/`, `tests/Feature/DataSpasial/`, `tests/Browser/` at repository root
- Paths shown assume current OpenKab structure.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare shared assets and CSP config needed by the map.

- [ ] T001 [P] Update CSP whitelist to allow Leaflet.markercluster assets in `app/Policies/CustomCspPreset.php` (add unpkg markercluster CSS/JS)
- [ ] T002 [P] Add Leaflet.markercluster 1.5.3 assets include strategy in peta view (via CDN with SRI or ensure CSP allows) — document in view
- [ ] T003 [P] Create JS asset for Peta Gabungan map in `public/js/dataspasial/peta-gabungan.js` (initial scaffold with clustering, layers, popup, thematic helpers)
- [ ] T004 [P] Verify existing permissions/menu for `dataspasial-peta-read` (migration already exists) — no change needed (reference `database/migrations/2026_10_14_000001_create_dataspasial_roles.php`)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core server-side pieces required before user story implementation.

**⚠️ CRITICAL**: No user story work can begin until this phase complete.

- [ ] T005 Add AJAX endpoint route `GET dataspasial/peta/data` in `routes/web.php` (same middleware group as `dataspasial/peta`, permission `dataspasial-peta-read`), named `dataspasial.peta.data`
- [ ] T006 Add controller method `data(Request $request)` to `app/Http/Controllers/DataSpasial/PetaGabunganController.php` returning JSON (FeatureCollection + meta) following view contract; handle unavailable/empty per DataSpasialResult contract
- [ ] T007 [P] Add Feature test helper fixtures for `geojson-gabungan` success/empty/unavailable in `tests/Feature/DataSpasial/Fixtures.php` (mirror existing fixture style) 
- [ ] T008 [P] Create base Feature test `tests/Feature/DataSpasial/PetaGabunganControllerTest.php` skeleton with `uses(DatabaseTransactions::class)`, Http::fake, config/Setting setup

**Checkpoint**: Foundation ready — user story implementation can begin.

---

## Phase 3: User Story 1 - Satu Peta Gabungan: Sebaran Titik dan Batas Wilayah (Priority: P1) 🎯 MVP

**Goal**: Render single Leaflet map with basemap (OSM), batas wilayah polygons, and facility points (DataSpasial + legacy geospatial) with clustering; layer panel to toggle layers; graceful unavailable state.

**Independent Test**: Open `/dataspasial/peta` with permission → map shows basemap/boundaries/points; clusters form at low zoom; layer toggles work without reload; when API unavailable → "Data sementara tidak tersedia" shown in map area with layout intact.

### Tests for User Story 1

- [ ] T009 [P] [US1] Feature test: unavailable (503 from API) renders unavailable partial/behavior for peta route in `tests/Feature/DataSpasial/PetaGabunganControllerTest.php`
- [ ] T010 [P] [US1] Feature test: timeout/connection failure → unavailable (Http::fake throwing ConnectionException)
- [ ] T011 [P] [US1] Feature test: missing Setting token → unavailable (not_configured)
- [ ] T012 [P] [US1] Feature test: success returns FeatureCollection and view renders map container
- [ ] T013 [P] [US1] Feature test: empty result → shows friendly empty state in map area
- [ ] T014 [P] [US1] Browser smoke: verify `/dataspasial/peta` link exists and page loads; unavailable state displays "Data sementara tidak tersedia" (extend `tests/Browser/SmokeDataSpasialMenuTest.php` or add `tests/Browser/SmokePetaGabunganTest.php`)

### Implementation for User Story 1

- [ ] T015 [P] [US1] Replace/implement full map UI in `resources/views/dataspasial/peta/index.blade.php` (map container, layer panel, legend placeholder, filter controls structure) using AdminLTE + `<x-dataspasial-status>`
- [ ] T016 [US1] Implement controller `data()` to return JSON FeatureCollection + meta (sectors/periods/filters derived) with unavailable/empty envelope (HTTP 200)
- [ ] T017 [US1] Implement `public/js/dataspasial/peta-gabungan.js`: initialize Leaflet map (OSM), L.markerclusterGroup, L.control.layers (basemap + boundary overlay + point groups), render features from server/JSON, graceful error rendering for unavailable/empty
- [ ] T018 [US1] Extract boundaries (Polygon/MultiPolygon) and points (Point) from FeatureCollection; group by source/has-dataspasial for layer toggles per FR-003
- [ ] T019 [US1] Ensure no full page reload on layer toggles (client-side only); preserve existing DataSpasial UX and layout intact

**Checkpoint**: US1 functional and independently testable.

---

## Phase 4: User Story 2 - Popup Titik: Identitas dan Isian yang Ditandai Tampil (Priority: P1)

**Goal**: Popup shows facility identity + only isian with `tampil_popup===true`, with satuan, sumber data, tanggal pembaruan; identity-only if none.

**Independent Test**: Click point with popup-flagged isian → shows all flagged with satuan + sumber + tanggal; sector with no popup isian → identity only; no values for periode → identity + explanation.

### Tests for User Story 2

- [ ] T020 [P] [US2] Feature test: popup content includes only `tampil_popup` flagged isian (fixture with mixed flags)
- [ ] T021 [P] [US2] Feature test: identity-only when no popup isian present
- [ ] T022 [P] [US2] Feature test: includes satuan, sumber (`sumber`/`sumber_data`), tanggal pembaruan (`waktu_input`/`tanggal_pembaruan`) when present

### Implementation for User Story 2

- [ ] T023 [P] [US2] Build popup HTML generator in JS using feature properties + isian metadata (respect `tampil_popup`, default true)
- [ ] T024 [US2] Handle case "nilai belum tersedia untuk periode tersebut" in popup when dataspasial missing for selected periode
- [ ] T025 [US2] Sanitize/format values (numbers, dates) and render satuan after value

**Checkpoint**: US1+US2 both independently functional.

---

## Phase 5: User Story 3 - Filter Sektor, Periode, dan Isian Filter dengan Legenda (Priority: P2)

**Goal**: Sector/period + isian with `tampil_filter===true` controls; updates via AJAX without full page reload; legend updates; last-wins on rapid changes.

**Independent Test**: Change sektor/periode → map+legend update no reload; filter controls appear when `tampil_filter` exists; no-data selection shows friendly message; rapid changes reflect last selection only.

### Tests for User Story 3

- [ ] T026 [P] [US3] Feature test: AJAX endpoint `data()` returns sectors/periods/filters in meta for selected context
- [ ] T027 [P] [US3] Feature test: changing filters returns updated FeatureCollection without full page context
- [ ] T028 [P] [US3] Feature test: rapid successive requests — last response wins (request id/token to ignore stale)
- [ ] T029 [P] [US3] Feature test: no data combination → friendly empty message in map area (not error)

### Implementation for User Story 3

- [ ] T030 [P] [US3] Add sector/periode dropdowns and dynamic filter controls (for `tampil_filter===true`) to view
- [ ] T031 [US3] Implement AJAX fetch in JS: `fetch(petaDataUrl + params, {signal})` with debounce (e.g., 250–300ms), request id to discard stale responses (FR-008)
- [ ] T032 [US3] Update map layers/legend on AJAX response; handle unavailable/empty envelopes in JS
- [ ] T033 [US3] Derive available periods from features (or fallback via service calls) and default to latest with data (FR-006)

**Checkpoint**: US1–US3 functional independently.

---

## Phase 6: User Story 4 - Pewarnaan Desa Tematik Menurut Isian Angka (Priority: P2)

**Goal**: Thematic coloring by selected numeric isian for desa boundaries; "tanpa data" styled distinctly (not zero); zero treated as data; legend includes ranges + satuan + "Tanpa Data".

**Independent Test**: Select numeric isian → desa colored by scale; desa without data has distinct "no data" style different from zero; legend updated; non-numeric sector → thematic picker disabled with explanation.

### Tests for User Story 4

- [ ] T034 [P] [US4] Feature test: thematic uses numeric isian only (UI constraint reflected in meta)
- [ ] T035 [P] [US4] Feature test: desa tanpa data gets distinct style (fixture asserts classification mapping)
- [ ] T036 [P] [US4] Feature test: desa nilai nol included in scale (not treated as no-data)
- [ ] T037 [P] [US4] Feature test: legend includes "Tanpa Data" and satuan (meta/classification)

### Implementation for User Story 4

- [ ] T038 [P] [US4] Add thematic isian dropdown (numeric `tipe='angka'` only) to view; disable/show explanation if none
- [ ] T039 [US4] Implement classification (quantile default; fallback equal interval) on polygon values per desa (`kode_desa` mapping)
- [ ] T040 [US4] Apply polygon styles: no-data → distinct style (e.g., dashed/gray stroke, fill pattern or specific color class) different from all scale colors; zero → lowest scale class
- [ ] T041 [US4] Render/update legend with color ramps, ranges, satuan, and "Tanpa Data" entry (FR-009)
- [ ] T042 [US4] Update polygon popups to show desa identity + selected value or "Tanpa data"

**Checkpoint**: US1–US4 all functional independently.

---

## Phase 7: User Story 5 - Lancar pada 1.000+ Titik dan Nyaman di Ponsel (Priority: P3)

**Goal**: Smooth pan/zoom with 1000+ points (clustering), responsive mobile layout, touch-friendly popups/controls.

**Independent Test**: 1000+ points → pan/zoom responsive; mobile viewport controls/legend/popup accessible, no horizontal scroll; touch interactions work.

### Tests for User Story 5

- [ ] T043 [P] [US5] Browser smoke: mobile viewport rendering (<=768px) — no horizontal scroll, controls accessible (`SmokePetaGabunganTest` or extend)
- [ ] T044 [P] [US5] Performance sanity: load synthetic 1000+ points fixture (Feature test asserts structure; manual verification per SC-001)

### Implementation for User Story 5

- [ ] T045 [P] [US5] Ensure markercluster config tuned (spiderfyOnMaxZoom, showCoverageOnHover false, maxClusterRadius, chunked loading not needed) for smoothness
- [ ] T046 [US5] Responsive CSS for map controls/legend/panel (Bootstrap 4/AdminLTE) on mobile; prevent control overflow
- [ ] T047 [US5] Touch-friendly popup sizing/close; ensure layer control usable on small screens

**Checkpoint**: All US1–US5 independently functional and performant.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final integration, documentation alignment, regression checks.

- [ ] T048 [P] Update `CustomCspPreset.php` with exact markercluster CDN URLs (v1.5.3 CSS/JS) and verify no CSP violations
- [ ] T049 [P] Add Browser test class `tests/Browser/SmokePetaGabunganTest.php` (or extend existing) covering menu link and unavailable state
- [ ] T050 Run existing DataSpasial Feature tests to ensure no regression (`tests/Feature/DataSpasial/DataSpasialUnavailableTest.php`, related)
- [ ] T051 [P] Code cleanup: remove unused code, add inline comments for classification logic
- [ ] T052 Verify quickstart.md scenarios pass (manual smoke checklist)
- [ ] T053 [P] Ensure messages match exactly: "Data sementara tidak tersedia" and "Tanpa Data"/empty states per spec

---

## Dependencies & Execution Order

### Phase Dependencies
- **Phase 1 (Setup)**: No deps — can start immediately
- **Phase 2 (Foundational)**: Depends on Setup — BLOCKS all user stories
- **US1 (P1)**: After Foundational
- **US2 (P1)**: After Foundational (independent from US1 implementation details but tests may share fixtures)
- **US3 (P2)**: After Foundational
- **US4 (P2)**: After Foundational
- **US5 (P3)**: After Foundational (performance tuning, can run parallel with others)
- **Polish**: After desired stories complete (US1–US4 minimum per SC-007)

### Parallel Opportunities

- Phase 1: T001–T004 [P] all parallel
- Phase 2: T005 sequential (route/controller), T006 sequential after or with T005, T007/T008 [P] parallel
- US1: T009–T014 [P] tests parallel; T015 [P] can start with tests; T016–T019 implementation (T016 after T005/T006, T017 depends on T003, T015 independent view)
- US2: T020–T022 [P] tests parallel; T023 [P], T024–T025 implementation
- US3: T026–T029 [P] tests; T030 [P], T031–T033
- US4: T034–T037 [P] tests; T038 [P], T039–T042
- US5: T043–T044 [P] tests; T045 [P], T046–T047
- Polish: T048–T053 mostly [P]

### MVP Scope
Complete Phase 1+2 + US1 (Phase 3) → validates core map + clustering + unavailable state. Add US2 for full popup value, US3/US4 for analysis features.

## Parallel Example: US1
```bash
# Tests in parallel
T009, T010, T011, T012, T013, T014

# Implementation prep
T015 (view) || T016 (controller data) can run in parallel with tests
T017 (JS) depends on T003
```

## Implementation Notes
- Follow DataSpasial conventions: never throw from controller; use DataSpasialResult; generic unavailable message only; DatabaseTransactions + Http::fake in Feature tests.
- No direct browser calls to Satu Data API (use AJAX to OpenKab endpoints only).
- "Tanpa data" ≠ zero: classification maps missing values to distinct style (separate from zero class).
- Debounce AJAX on rapid filter changes; track request id to ignore stale responses.
- Keep layout/nav intact on unavailable/empty states (use `<x-dataspasial-status>`).
