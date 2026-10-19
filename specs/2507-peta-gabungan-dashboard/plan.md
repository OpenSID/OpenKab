# Implementation Plan: Peta Gabungan Geospasial & DataSpasial

**Branch**: `2507-peta-gabungan-dashboard` | **Date**: 2026-10-19 | **Spec**: spec.md
**Input**: Feature specification from `/specs/2507-peta-gabungan-dashboard/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: PHP 8.4, Laravel 13 (OpenKab monolith), Blade + jQuery/Bootstrap 4.6/AdminLTE 4, Vite 8  
**Primary Dependencies**: `spatie/laravel-permission`, `spatie/csp`, `yajra/laravel-datatables-oracle`, `jeroennoten/laravel-adminlte`, `guzzlehttp/guzzle`. Client-side: Leaflet 1.9.4 + Leaflet.markercluster (CDN).  
**Storage**: MySQL (local: users/roles/settings/sessions only). DataSpasial consumed via API Satu Data (no new local tables).  
**Testing**: Pest + PHPUnit (`tests/Feature/`, `tests/Browser/`), Playwright, MSW 2.14.6; `DatabaseTransactions` + `Http::fake`.  
**Target Platform**: Web (desktop + mobile responsive), modern browsers.  
**Project Type**: Laravel monolith web application (server-rendered + progressive JS).  
**Performance Goals**: 1.000+ points smooth with clustering; filter/sector/periode updates < 3s; no full page reloads.  
**Constraints**: CSP enforced (must whitelist markercluster), no direct browser calls to Satu Data API, generic "Data sementara tidak tersedia", "tanpa data" != zero.  
**Scale/Scope**: Peta Gabungan page (US1–US5); implements skeleton at `dataspasial.peta.index`; adds AJAX endpoint `dataspasial.peta.data`.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

[Gates determined based on constitution file]

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Http/Controllers/DataSpasial/PetaGabunganController.php
├── Policies/CustomCspPreset.php
├── Services/DataSpasialApiService.php
└── Services/DataSpasialResult.php

resources/views/dataspasial/peta/index.blade.php

public/js/dataspasial/peta-gabungan.js

routes/web.php

tests/
├── Feature/DataSpasial/
│   ├── PetaGabunganControllerTest.php
│   └── Fixtures.php (updated)
└── Browser/
    └── SmokePetaGabunganTest.php (or extended)
```

**Structure Decision**: Single Laravel monolith (OpenKab). Implement controller method + view + JS asset + CSP update + routes + tests per plan.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
