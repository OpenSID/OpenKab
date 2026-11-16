# OpenKab Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-10-19

## Active Technologies
- PHP 8.4 + laravel/framework (Laravel 13), spatie/laravel-permission, spatie/laravel-activitylog, yajra/laravel-datatables, laravel/sanctum, guzzlehttp/guzzle, mews/captcha, firebase/php-jwt ^7.0 (tanda tangan/verifikasi JWT RS256) (2502-opensid-sso-access)
- MySQL (database utama OpenKab) — 2 tabel baru tersisa: `openkab_sso_logs`, `openkab_sso_tokens`. Tabel `desa_sso_configs` dihapus (migrasi rollback + file migrasi dihapus). (2502-opensid-sso-access)
- PHP 8.4 + Laravel 13, firebase/php-jwt ^7.0 (JWT RS256), spatie/laravel-permission, spatie/laravel-activitylog, guzzlehttp/guzzle, mews/captcha, laravel/sanctum (2502-opensid-sso-access)
- MySQL (OpenKab) — 2 tabel baru `openkab_sso_logs` (append-only) & `openkab_sso_tokens` (anti-replay); kunci RS256 di env/file PEM server (bukan DB). Sisi OpenSID: `opensid_sso_logs` (repo terpisah, CodeIgniter) (2502-opensid-sso-access)
- PHP 8.4 + laravel/framework (Laravel 13), yajra/laravel-datatables, (002-tipe-lokasi-import)
- MySQL (data lokal: user/session/pengaturan); data lokasi (plan) & master (002-tipe-lokasi-import)
- PHP 8.4 + laravel/framework (Laravel 13), guzzlehttp/guzzle (via Illuminate HTTP client), spatie/laravel-permission, ben-sampo/laravel-enum (2503-dataspasial-integration)
- MySQL (hanya data lokal: user/session/pengaturan + peran/izin; data `ds_*` seluruhnya di API Satu Data) (2503-dataspasial-integration)
- MySQL (hanya data lokal: user/session/pengaturan + peran/izin; data `ds_*` seluruhnya di API Satu Data) (2503-dataspasial-integration)
- PHP 8.4 + laravel/framework (Laravel 13), yajra/laravel-datatables, ben-sampo/laravel-enum, spatie/laravel-permission (sudah ada dari KAB-01); frontend: Bootstrap 4.6 + jQuery 3.6 + AdminLTE 4 (Vite 8) (2504-pengelola-skema-editor)
- MySQL (hanya data lokal: user/session/pengaturan/peran-izin; seluruh data `ds_*` — sektor, skema, isian, pemetaan — tetap di API Satu Data, **tidak** ada tabel baru) (2504-pengelola-skema-editor)
- PHP 8.4 + Laravel Framework 13 (OpenKab monolith) + Laravel (routing, FormRequest, view/Blade, HTTP client guzzle via `DataSpasialApiService`), spatie/laravel-permission (izin `dataspasial-skema-read`/`dataspasial-skema-write`), spatie/laravel-activitylog (opsional, audit), ben-sampo/laravel-enum (`TipeIsian`, `StatusSkemaEditor`), yajra/laravel-datatables (tidak digunakan di halaman ini), Vite 8 (assets), Bootstrap 4.6 + jQuery 3.6 + AdminLTE 4 (frontend) (2504-pengelola-skema-editor)
- MySQL (hanya sesi/user/peran/lokal); data sektor/skema/isian/pemetaan **tidak disimpan di OpenKab** (FR-004/SC-010) — semua lewat `DataSpasialApiService` ke API Satu Data (2504-pengelola-skema-editor)
- PHP 8.4 + laravel/framework 13 (OpenKab monolith), spatie/laravel-permission, (2505-penerbitan-adopsi-skema)
- MySQL (lokal: hanya user/session/peran/pengaturan — **tanpa tabel baru**); seluruh data versi skema, riwayat, adopsi desa, dan dampak pra-terbit tetap di API Satu Data (2505-penerbitan-adopsi-skema)
- MySQL (lokal: hanya user/session/peran/pengaturan — **tanpa tabel baru**); seluruh (2505-penerbitan-adopsi-skema)
- MySQL (lokal: hanya user/session/peran/pengaturan — **tanpa tabel baru**); seluruh (2505-penerbitan-adopsi-skema)
- PHP 8.4, Laravel 13 (OpenKab monolith), Blade + jQuery/Bootstrap 4.6/AdminLTE 4, Vite 8 + `spatie/laravel-permission`, `spatie/csp`, `yajra/laravel-datatables-oracle`, `jeroennoten/laravel-adminlte`, `guzzlehttp/guzzle`. Client-side: Leaflet 1.9.4 + Leaflet.markercluster (CDN). (2507-peta-gabungan-dashboard)
- MySQL (local: users/roles/settings/sessions only). DataSpasial consumed via API Satu Data (no new local tables). (2507-peta-gabungan-dashboard)
- PHP 8.4, Laravel 13 (OpenKab monolith) + `spatie/laravel-permission`, `spatie/csp`, `guzzlehttp/guzzle` (via Laravel HTTP client), `jeroennoten/laravel-adminlte`. Client-side: Leaflet 1.9.4 + Leaflet.markercluster 1.5.3 (CDN), Bootstrap 4.6 + jQuery/AdminLTE 4. (2507-peta-gabungan-dashboard)
- MySQL lokal hanya untuk user/session/peran/pengaturan. Seluruh data sektor/geojson/kategori dibaca dari API Satu Data; **tanpa tabel baru**. (2507-peta-gabungan-dashboard)

- PHP 8.4, Laravel 13 + laravel/framework, spatie/laravel-permission, spatie/laravel-activitylog, yajra/laravel-datatables, laravel/sanctum, guzzlehttp/guzzle, mews/captcha. Tambahan: `firebase/php-jwt` (library JWT tervalidasi untuk penandatanganan/verifikasi). (2502-opensid-sso-access)

## Project Structure

```text
app/{Console,Enums,Exceptions,Http,Models,Providers,Services}/  config/  database/  resources/views/  routes/
specs/{###-feature}/{spec,plan,research,data-model,quickstart}.md + contracts/
tests/{Feature,Browser,Unit}/
```

## Commands

- `composer install` / `composer lint` (PHP CS Fixer)
- `php artisan migrate` — **hanya tabel baru yang belum ada**; gunakan `--pretend` untuk preview
- `php artisan db:seed` — hanya untuk seeder yang eksplisit diperlukan
- `vendor/bin/pest tests/Feature` · `vendor/bin/pest tests/Browser` · `vendor/bin/pint` (PHP CS Fixer)
- **Jalankan hanya test yang relevan** — jangan pernah jalankan seluruh suite test secara simultan; filter dengan `--filter=NamaMetode` atau path spesifik (misal `vendor/bin/pest tests/Feature/Sso/`) terutama saat ada perubahan kode.
- Speckit: `/speckit.specify` → `/speckit.plan` → `/speckit.tasks` → `/speckit.implement` → `/speckit.checklist`

## Database Safety — READ FIRST

**DILARANG KARENA MERUSAKKAN DATA AKTUAL:**

- `php artisan migrate:fresh` — **DILARANG** (drop semua tabel, data hilang)
- `php artisan migrate:refresh` — **DILARANG** (rollback + migrate ulang semua, data hilang)
- `php artisan db:wipe` — **DILARANG** (hapus semua tabel)
- `php artisan migrate:fresh --seed` — **DILARANG**
- Perintah artisan lain yang secara implisit memakai opsi di atas juga **DILARANG**

**Aturan emas:** Jika database produksi/eksisting sudah berisi data, jangan pernah gunakan perintah yang menghapus atau me-replace seluruh skema database. Gunakan `php artisan migrate` saja untuk menambahkan tabel/kolom baru secara incremental.

## Code Style

PHP 8.4, Laravel 13: Follow standard conventions

## Testing — Database Isolation

Agar testing tidak pernah mengotori (memutasi atau menghapus) database eksisting:

1. **Selalu pakai `DatabaseTransactions` trait** pada setiap test case yang menulis ke database. Trait ini otomatis rollback semua transaksi setelah test selesai, sehingga data saksi (seed) tetap utuh dan tidak ada record sisa.
   - Contoh di `tests/BaseTestCase.php` sudah memakai `use DatabaseTransactions;`.
2. **Jika membuat test case baru yang extend `Tests\TestCase` langsung** (bukan `BaseTestCase`), tambahkan trait `use Illuminate\Foundation\Testing\DatabaseTransactions;` segera.
3. **Jangan panggil `RefreshDatabase` atau `DatabaseMigrations`** — mereka akan `migrate:fresh` dan menghancurkan data. Pakai `DatabaseTransactions` saja.
4. **Gunakan factory untuk data saksi** — `User::factory()->create()` dll. Record yang diciptakan oleh factory otomatis ter-rollback oleh `DatabaseTransactions`.
5. **Untuk data eksternal** (API Satu Data, OpenSID, dll) gunakan `Http::fake()` / `Cache::put()` sehingga tidak ada request ke sistem nyata.
6. **Jangan hard-code ID atau email tertentu** yang bisa bentrok dengan data eksisting; gunakan nilai acak atau factory.
7. **Jangan gunakan `assertDatabaseHas` untuk verifikasi data sementara tanpa `DatabaseTransactions`** — gunakan saja setelah memastikan trait sudah aktif.
8. **Test environment** di `phpunit.xml` sudah memakai `APP_ENV=testing`, `CACHE_DRIVER=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array` — itu sudah tepat. Jangan ubah `DB_CONNECTION` ke MySQL lain; pakai database yang sama dengan `DatabaseTransactions` sebagai jaring pengaman.
9. **Jalankan hanya test yang relevan** — jangan pernah jalankan seluruh suite test secara simultan. Filter dengan `--filter=NamaMetode` atau path spesifik (misal `vendor/bin/pest tests/Feature/Sso/`). Ini mengurangi beban database dan mencegah side-effect tak terduga.

## Recent Changes
- 2507-peta-gabungan-dashboard: Added PHP 8.4, Laravel 13 (OpenKab monolith) + `spatie/laravel-permission`, `spatie/csp`, `guzzlehttp/guzzle` (via Laravel HTTP client), `jeroennoten/laravel-adminlte`. Client-side: Leaflet 1.9.4 + Leaflet.markercluster 1.5.3 (CDN), Bootstrap 4.6 + jQuery/AdminLTE 4.
- 2507-peta-gabungan-dashboard: Added PHP 8.4, Laravel 13 (OpenKab monolith), Blade + jQuery/Bootstrap 4.6/AdminLTE 4, Vite 8 + `spatie/laravel-permission`, `spatie/csp`, `yajra/laravel-datatables-oracle`, `jeroennoten/laravel-adminlte`, `guzzlehttp/guzzle`. Client-side: Leaflet 1.9.4 + Leaflet.markercluster (CDN).
- 2506-peta-gabungan-dashboard: Added [if applicable, e.g., PostgreSQL, CoreData, files or N/A]


<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
