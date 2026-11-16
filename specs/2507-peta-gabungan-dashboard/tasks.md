# Tasks: Peta Gabungan Geospasial & DataSpasial

**Input**: Design documents from `specs/2507-peta-gabungan-dashboard/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md
**Design**: Tata letak dua blok — (kiri) blok filter DataSpasial: **kecamatan** + isian bertanda `tampil_filter`; (kanan) peta + **panel lapisan** (kategori geospasial lama + sektor DataSpasial) + popup **sorot/klik** + legenda. **Tanpa pemilih sektor.**

**Tests**: Disertakan (mengikuti konvensi repo: Pest Feature + Browser smoke, `DatabaseTransactions` + `Http::fake`).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: paralel (berkas berbeda, tanpa dependensi)
- **[Story]**: label user story (US1–US5)
- Sertakan path berkas eksak

## Path Conventions (OpenKab monolith)

- `app/Http/Controllers/DataSpasial/`, `app/Policies/`, `app/Services/`
- `resources/views/dataspasial/peta/`, `public/js/dataspasial/`, `routes/web.php`
- `tests/Feature/DataSpasial/`, `tests/Browser/`

---

## Phase 1: Setup (Shared Infrastructure)

- [x] T001 [P] Verifikasi whitelist Leaflet + Leaflet.markercluster 1.5.3 (CSS/JS unpkg) di `app/Policies/CustomCspPreset.php`; tambahkan bila belum ada
- [x] T002 [P] Tulis ulang `public/js/dataspasial/peta-gabungan.js` sebagai modul vanilla (tanpa jQuery) dengan API `init(options)`, `render(geojson, meta)`, `unavailable(message)`, `empty()`, plus helper layer/popup/legenda
- [x] T003 [P] Siapkan partial blok filter `resources/views/dataspasial/peta/filter.blade.php` (placeholder kecamatan + kontainer filter dinamis)

---

## Phase 2: Foundational (Blocking Prerequisites)

**⚠️ CRITICAL**: Tidak ada user story boleh dimulai sebelum fase ini selesai.

- [x] T004 Tambah route `GET dataspasial/peta/data` (`permission:dataspasial-peta-read`, name `dataspasial.peta.data`) di `routes/web.php`
- [x] T005 Implement `PetaGabunganController::data(Request)` di `app/Http/Controllers/DataSpasial/PetaGabunganController.php`: panggil `geojsonGabungan()` (param `sektor` kode, `kode_kecamatan`, `periode`, `filter[...]`), kembalikan `{type, features, meta}` HTTP 200 dengan flag `unavailable`/`empty`
- [x] T006 Turunkan `meta.layers` dari `features` (kategori geospasial lama via `properties.kategori`/`simbol`; sektor DataSpasial via `properties.sektor_kode`), `meta.periods` dari `properties.dataspasial.periode`, dan `meta.isian_tematik_options` (isian `tipe=angka`)
- [x] T007 Bila `sektor` diisi, panggil `listSkema(['sektor' => kode])` dan isi `meta.filters` (isian `tampil_filter=true` beserta `tipe`/`opsi`/`satuan`) dan `meta.popup_isian` (`tampil_popup=true`)
- [x] T008 [P] Tambah helper fixture payload `geojson` (berisi feature `source=openkab`/`presisi` + feature `dataspasial`) di `tests/Feature/DataSpasial/Fixtures.php`
- [x] T009 [P] Perluas kerangka `tests/Feature/DataSpasial/PetaGabunganControllerTest.php` (setup config/Setting, `makeRole('analis opd', ['dataspasial-peta-read'])`, `Http::fake`)

**Checkpoint**: Fondasi siap — implementasi user story dapat dimulai.

---

## Phase 3: User Story 1 - Satu Peta Gabungan & Panel Lapisan (Priority: P1) 🎯 MVP

**Goal**: Halaman dua blok: blok filter kiri + blok lapisan/peta; basemap, batas wilayah, titik terklaster, panel lapisan (kategori geospasial lama + sektor DataSpasial), degradasi ramah.

**Independent Test**: Buka `/dataspasial/peta` → tampil dua blok; buka panel lapisan → ada lapisan kategori (Sekolah/Fasilitas Kesehatan/Tempat Ibadah) dan lapisan sektor DataSpasial; zoom rendah → klaster berjumlah; toggle lapisan tanpa muat ulang; API mati → "Data sementara tidak tersedia".

### Tests for User Story 1

- [x] T010 [P] [US1] Test halaman `GET /dataspasial/peta` → 200 dan memuat judul + kontainer peta (lihat `tests/Feature/DataSpasial/PetaGabunganControllerTest.php`)
- [x] T011 [P] [US1] Test endpoint `data` mengembalikan `type=FeatureCollection` + `meta.layers` berisi entri `geospasial_lama` dan `dataspasial`
- [x] T012 [P] [US1] Test `unavailable` (503) → `meta.unavailable=true`, `features=[]`, tetap HTTP 200, tanpa stack trace
- [x] T013 [P] [US1] Test `empty` → `meta.empty=true`
- [x] T014 [P] [US1] Test tamu diarahkan ke login

### Implementation for User Story 1

- [x] T015 [US1] Susun `resources/views/dataspasial/peta/index.blade.php` menjadi dua kolom: kiri `@include('dataspasial.peta.filter')`, kanan `#map` + `#map-legend` + `#map-layers` + area pesan
- [x] T016 [US1] Implement panel lapisan di `public/js/dataspasial/peta-gabungan.js`: bangun grup Leaflet per `meta.layers`, checkbox toggle tampil/sembunyi, tampilkan jumlah titik
- [x] T017 [US1] Implement render titik terklaster (`L.markerClusterGroup`) dan polygon batas wilayah dari payload
- [x] T018 [US1] Muat data awal di `index.blade.php` (fetch `dataspasial.peta.data`) dan panggil `PetaGabungan.render()`; pastikan init pada `DOMContentLoaded` tanpa dependensi jQuery
- [x] T019 [US1] Tangani `unavailable`/`empty` di JS → tampilkan pesan pada area peta, kerangka halaman utuh

**Checkpoint**: US1 selesai & teruji mandiri.

---

## Phase 4: User Story 2 - Popup Detail Saat Sorot/Klik (Priority: P1)

**Goal**: Popup memuat identitas fasilitas + hanya isian `tampil_popup` + satuan + sumber + tanggal pembaruan; dipicu `mouseover` (sorot) dan `click`.

**Independent Test**: Sorot titik → popup muncul; klik titik → popup menetap; lapisan tanpa isian `tampil_popup` → popup identitas saja.

### Tests for User Story 2

- [x] T020 [P] [US2] Test `meta.popup_isian` hanya memuat isian `tampil_popup=true` (fixture campuran flag)
- [x] T021 [P] [US2] Test `meta.popup_isian` menyertakan `satuan` bila ada

### Implementation for User Story 2

- [x] T022 [US2] Implement `popupContent(feature, meta)` di `public/js/dataspasial/peta-gabungan.js`: identitas + isian `tampil_popup` + satuan + sumber (`source`/`sumber`, mis. "Data Presisi") + tanggal pembaruan
- [x] T023 [US2] Ikat `mouseover` (buka) dan `click` (tahan) pada penanda/klaster; tangani titik tanpa data → identitas + keterangan nilai belum tersedia
- [x] T024 [US2] Escape nilai & format angka/tanggal agar aman di popup

**Checkpoint**: US1 + US2 fungsional mandiri.

---

## Phase 5: User Story 3 - Filter Kecamatan & Isian `tampil_filter` (Priority: P2)

**Goal**: Blok filter kiri berisi dropdown **Kecamatan** (saja) + kontrol dinamis tiap isian `tampil_filter`; pembaruan tanpa muat ulang; hasil pilihan terakhir.

**Independent Test**: Pilih kecamatan → peta/legenda berubah tanpa muat ulang; kontrol filter muncul untuk isian `tampil_filter`; kombinasi kosong → pesan "tidak ada data untuk pilihan ini"; ganti cepat → hasil terakhir.

### Tests for User Story 3

- [x] T025 [P] [US3] Test `meta.filters` hanya isian `tampil_filter=true` untuk sektor terpilih
- [x] T026 [P] [US3] Test `data` meneruskan `kode_kecamatan` dan `filter[...]` ke `geojsonGabungan`
- [x] T027 [P] [US3] Test kombinasi tanpa hasil → `meta.empty=true` (bukan galat)

### Implementation for User Story 3

- [x] T028 [US3] Implement loader dropdown kecamatan di `resources/views/dataspasial/peta/filter.blade.php` (tanpa kabupaten/desa)
- [x] T029 [US3] Render kontrol filter dinamis dari `meta.filters` (select untuk `pilihan`/`ya_tidak`, number untuk `angka`, input untuk `teks`/`tanggal`)
- [x] T030 [US3] Implement fetch/refetch di `index.blade.php` dengan `AbortController`/request-id (buang respons basi) dan tombol Tampilkan/Reset
- [x] T031 [US3] Pastikan tidak ada pemilih sektor di UI; konteks DataSpasial = lapisan sektor aktif

**Checkpoint**: US1–US3 fungsional mandiri.

---

## Phase 6: User Story 4 - Pewarnaan Desa Tematik (Priority: P2)

**Goal**: Desa diwarnai menurut satu isian angka; "tanpa data" ≠ nol; legenda rentang + satuan + "Tanpa Data".

**Independent Test**: Pilih isian angka → desa berwarna + legenda; desa tanpa data gaya khusus; desa nol masuk skala; tanpa isian angka → pilihan nonaktif.

### Tests for User Story 4

- [x] T032 [P] [US4] Test `meta.isian_tematik_options` hanya isian angka
- [x] T033 [P] [US4] Test klasifikasi: nilai kosong → "tanpa data", nilai nol → kelas skala (unit test JS-free via helper atau Feature assertion meta)

### Implementation for User Story 4

- [x] T034 [US4] Implement klasifikasi kuantil (fallback rentang sama besar) di `public/js/dataspasial/peta-gabungan.js`
- [x] T035 [US4] Terapkan gaya polygon: tanpa data → `NO_DATA_COLOR` + garis putus-putus; bernilai (termasuk nol) → warna skala
- [x] T036 [US4] Render legenda (skala + satuan + "Tanpa Data") di `#map-legend`; perbarui saat isian/kecamatan berubah
- [x] T037 [US4] Nonaktifkan pilihan pewarnaan bila tidak ada isian angka, dengan keterangan jelas

**Checkpoint**: US1–US4 fungsional mandiri.

---

## Phase 7: User Story 5 - 1.000+ Titik & Ponsel (Priority: P3)

**Goal**: Pan/zoom lancar dengan 1.000+ titik; tata letak dua blok tetap terpakai di ponsel.

**Independent Test**: Dataset 1.000+ titik → lancar & klaster benar; layar ponsel → blok filter, panel lapisan, legenda, popup terjangkau tanpa scroll horizontal.

### Tests for User Story 5

- [x] T038 [P] [US5] Browser smoke responsif: viewport ponsel tanpa scroll horizontal (`tests/Browser/SmokePetaGabunganTest.php`)
- [ ] T039 [P] [US5] Uji struktur dengan fixture 1.000+ titik (Feature assertion struktur meta/features)

### Implementation for User Story 5

- [x] T040 [US5] Setel konfigurasi markercluster (`maxClusterRadius`, `chunkedLoading`, `showCoverageOnHover=false`) di `public/js/dataspasial/peta-gabungan.js`
- [x] T041 [US5] CSS responsif di `resources/views/dataspasial/peta/index.blade.php`: peta (`#map` tinggi adaptif), blok filter menumpuk di ponsel, panel lapisan tidak meluber
- [x] T042 [US5] Pastikan kontrol popup/panel lapisan ramah sentuh (ukuran tombol, area tap)

**Checkpoint**: Semua US fungsional dan teruji.

---

## Phase 8: Polish & Cross-Cutting

- [x] T043 [P] Perbarui `specs/2507-peta-gabungan-dashboard/contracts/view-contract.md` bila kontrak bergeser (meta.layers/filters/popup_isian)
- [x] T044 [P] Tambah/perluas `tests/Browser/SmokePetaGabunganTest.php` (menu `/dataspasial/peta`, degradasi "Data sementara tidak tersedia")
- [x] T045 Jalankan regresi `vendor/bin/pest tests/Feature/DataSpasial/DataSpasialUnavailableTest.php`
- [x] T046 [P] Bersihkan kode mati (sisa pemilih sektor/cascade kab-desa) di `resources/views/dataspasial/peta/` dan `public/js/dataspasial/peta-gabungan.js`
- [x] T047 [P] Format & lint: `vendor/bin/pint` pada berkas PHP yang tersentuh; `php -l` pada JS tidak berlaku (lakukan lint manual/sintaks)
- [ ] T048 Verifikasi skenario `specs/2507-peta-gabungan-dashboard/quickstart.md`

---

## Dependencies & Execution Order

### Dependensi fase
- Setup (P1) tidak bergantung apa pun.
- Foundational (Phase 2) bergantung pada Setup; **memblokir** semua user story.
- US1 (P1) → US2 (P1) dapat berjalan beriringan setelah fondasi; US3 (P2), US4 (P2), US5 (P3) menyusul.
- Polish setelah US1–US4 selesai (US5 direkomendasikan).

### Dependensi user story
- US1: setelah Phase 2; tidak bergantung US lain.
- US2: butuh US1 (render titik) untuk popup, tetapi dapat dikerjakan paralel pada berkas JS yang sama secara berurutan.
- US3: butuh Foundational T005–T007.
- US4: bergantung pada polygon dari US1.
- US5: bergantung pada US1 (peta) dan dapat maju paralel dengan US3/US4.

### Paralel
- T001–T003 paralel.
- T008/T009 paralel setelah T005.
- Test per US dapat paralel.
- T043/T044/T046/T047 paralel.

## Parallel Example: US1
```bash
# Test paralel
T010, T011, T012, T013, T014
# Implementasi
T015 (view) || T016 (layer JS) ; lanjut T017 -> T018 -> T019
```

## Implementation Strategy

### MVP First
Phase 1 + 2 + US1 (T001–T019) → peta dua blok + panel lapisan + degradasi ramah. Deploy/demo.

### Incremental
- +US2 → popup lengkap
- +US3 → filter kecamatan & isian
- +US4 → pewarnaan tematik
- +US5 → performa & ponsel

### Notes
- Tidak ada tabel/migrasi baru; seluruh data lewat `DataSpasialApiService`.
- Jangan panggil API Satu Data dari browser.
- Pesan pengguna selalu generik.
- "Tanpa data" ≠ nol.
- Uji hanya yang relevan (filter path), jangan seluruh suite.
