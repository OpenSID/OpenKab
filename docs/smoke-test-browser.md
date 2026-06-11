# Smoke Test dengan Pest Browser (Playwright)

Panduan lengkap pembuatan smoke test menggunakan **Pest v4** + **pest-plugin-browser** (Playwright backend) untuk testing browser di Laravel.

## Daftar Isi

- [Arsitektur](#arsitektur)
- [Prasyarat](#prasyarat)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [MSW Setup](#msw-setup)
- [Penulisan Test](#penulisan-test)
- [Session State Management](#session-state-management)
- [Custom Login Route](#custom-login-route)
- [Troubleshooting](#troubleshooting)
- [Referensi](#referensi)

---

## Arsitektur

```
Pest PHP (tests/Browser/*.php)
    │
    ▼
pest-plugin-browser
    │
    ▼
Playwright Server (WebSocket)
    │
    ▼
Chromium Browser + MSW Service Worker
    │  (intercepts fetch/XHR requests)
    ▼
Laravel HTTP Server (amphp)
    │
    ▼
Aplikasi Laravel
```

Pest browser plugin menjalankan **Laravel HTTP server lokal** (amphp) dan mengontrol **Chromium browser** via Playwright WebSocket. **MSW (Mock Service Worker)** berjalan di browser untuk mengintersep request API dan mengembalikan data fixture.

### Request Flow

```
Browser Page (JS)
    │
    ├─ fetch('/api/v1/data-website')
    │       │
    │       ▼
    │   MSW Interceptor (init script)
    │       │
    │       ├─ Route match → return fixture JSON
    │       └─ No match → forward to real server
    │
    └─ Page renders with mock data
```

---

## Prasyarat

| Komponen | Versi | Keterangan |
|----------|-------|------------|
| PHP | 8.4+ | |
| Laravel | 13+ | |
| Node.js | 18+ | Untuk MSW |
| Composer | 2+ | |

---

## Instalasi

### 1. Install Pest v4

```bash
composer require pestphp/pest:^4.0 --dev -W
composer require pestphp/pest-plugin-browser:^4.0 --dev -W
```

### 2. Install MSW (npm)

```bash
npm install msw --save-dev
npx msw init public/ --save
```

### 3. Publish Pest Configuration

Buat `tests/Pest.php`:

```php
<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Browser');

// Timeout untuk browser tests (dalam milidetik)
pest()->browser()->timeout(30000);

// Disable Google Fonts di test environment
beforeEach(function () {
    config(['adminlte.google_fonts.allowed' => false]);
});
```

### 4. Update phpunit.xml

Pastikan `APP_ENV` di-set ke `testing`:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
</php>
```

### 5. Update .gitignore

```
# Pest Browser Testing
tests/Browser/Screenshots/
tests/Browser/.session_state.json
public/mockServiceWorker.js
```

### 6. Vendor Patching

Jalankan `apply-patch.sh` untuk menginjek MSW init script ke vendor:

```bash
# Otomatis dijalankan oleh composer hook
composer install
composer update

# Atau manual
bash tests/Browser/apply-patch.sh
```

Script ini menambahkan MSW init script ke `vendor/pestphp/pest-plugin-browser/src/Playwright/InitScript.php`.

---

## Konfigurasi

### Environment

Gunakan `.env` atau `.env.testing` untuk konfigurasi testing:

```env
APP_ENV=testing
APP_URL=http://127.0.0.1:8000
DB_DATABASE=openkab
DB_USERNAME=root
DB_PASSWORD=secret
```

### Playwright Options

Konfigurasi Playwright di `tests/Pest.php`:

```php
// Timeout default
pest()->browser()->timeout(30000);

// Headed mode (untuk debugging)
pest()->browser()->headless(false);
```

---

## MSW Setup

### Cara Kerja

MSW (Mock Service Worker) berjalan di browser sebagai service worker yang mengintersep request fetch/XHR. Setiap kali halaman dibuka:

1. Playwright inject init script ke browser
2. Init script mendaftarkan MSW service worker
3. Service worker mengintersep request API
4. Request yang cocok dengan route dikembalikan dari fixture JSON
5. Request yang tidak cocok diteruskan ke server asli

### Menambahkan Route Baru

#### Langkah 1: Buat Fixture JSON

Simpan file JSON di `tests/Browser/fixtures/`:

```
tests/Browser/fixtures/
├── kabupaten.json
├── kecamatan-50.01.json
├── kecamatan-50.02.json
├── desa-50.01.01.json
├── data-website.json
├── statistik-penduduk-rentang-umur.json
└── ...
```

#### Langkah 2: Tambah Route di MswSetup.php

Edit `tests/Browser/MswSetup.php` — tambahkan route ke constant `ROUTES` atau `REGEX_ROUTES`:

```php
// Exact match (URL statis)
private const ROUTES = [
    '/api/v1/statistik-web/get-list-kabupaten' => 'kabupaten.json',
    '/api/v1/data-website' => 'data-website.json',
    '/api/v1/endpoint-baru' => 'endpoint-baru.json',  // ← tambah di sini
];

// Regex match (URL dengan parameter dinamik)
private const REGEX_ROUTES = [
    '#/api/v1/statistik-web/get-list-kecamatan/([\d.]+)$#' => 'kecamatan-*.json',
    '#/api/v1/statistik/penduduk\?.*filter\[id\]=([^&]+)#' => 'statistik-penduduk-*.json',
    '#/api/v1/baru/([\w-]+)$#' => 'baru-*.json',  // ← tambah di sini
];
```

#### Langkah 3: Jalankan Test

```bash
php vendor/bin/pest --filter="TestBaru"
```

**Tidak perlu edit vendor** — `InitScript.php` sudah ter-patch dan otomatis memanggil `MswSetup::getInitScriptJs()`.

#### Workflow Lengkap

```
1. Buat fixture JSON
   tests/Browser/fixtures/baru.json

2. Tambah route di MswSetup.php
   - Exact: tambah ke ROUTES
   - Regex: tambah ke REGEX_ROUTES

3. Jalankan test
   php vendor/bin/pest --filter="TestBaru"
```

### Route Matching

MSW menggunakan dua jenis matching:

| Type | Kapan Digunakan | Contoh |
|------|-----------------|--------|
| Exact | URL statis, tidak ada parameter | `/api/v1/data-website` |
| Regex | URL dengan parameter dinamik | `/api/v1/statistik/penduduk?filter[id]=rentang-umur` |

#### Exact Route

```php
private const ROUTES = [
    '/api/v1/data-website' => 'data-website.json',
    // Request ke /api/v1/data-website → return data-website.json
];
```

#### Regex Route

```php
private const REGEX_ROUTES = [
    '#/api/v1/statistik/penduduk\?.*filter\[id\]=([^&]+)#' => 'statistik-penduduk-*.json',
    // Request ke /api/v1/statistik/penduduk?filter[id]=rentang-umur
    // → return statistik-penduduk-rentang-umur.json
];
```

Regex route menggunakan glob pattern (`*.json`) untuk resolve file fixture secara otomatis berdasarkan parameter URL.

### Vendor Patching: Mengapa Edit InitScript.php?

#### Masalah

Pest browser plugin meng-inject init script ke browser via `InitScript::get()`:

```php
// vendor/pestphp/pest-plugin-browser/src/Playwright/Page.php
$context->addInitScript(InitScript::get());  // hardcoded
```

`InitScript::get()` hanya mengembalikan script Pest default (axe.js). **Tidak ada hook/callback untuk menambahkan script lain.**

#### Solusi

Edit `InitScript.php` via composer hook (`apply-patch.sh`) untuk memanggil `MswSetup::getInitScriptJs()`:

```php
// vendor/.../InitScript.php (patched)
public static function get(): string
{
    $initScriptJs = <<<JS
        // ... Pest default (axe.js, console capture) ...
    JS;

    // TAMBAHAN: MSW init script
    $mswSetupJs = \Tests\Browser\MswSetup::getInitScriptJs();

    return $initScriptJs . "\n" . $mswSetupJs;
}
```

#### Kenapa Tidak Edit Vendor Langsung?

Karena `composer update` akan menimpa vendor. Solusi:

1. **Composer hook** — `apply-patch.sh` otomatis jalan setelah `composer install/update`
2. **Cek sebelum patch** — script mengecek apakah patch sudah ada sebelum apply
3. **Backup** — file original disimpan sebagai `InitScript.php.bak`

#### Alternatif Jangka Panjang

Contribute PR ke `pest-plugin-browser` untuk menambahkan:

```php
// Fitur yang diharapkan
pest()->browser()->initScript(fn() => MswSetup::getInitScriptJs());
```

### External CDN Blocking

MSW otomatis memblokir request ke external CDN (Google Fonts, dll) untuk mempercepat test:

```javascript
// Di MswSetup.php
if (url.includes('fonts.googleapis.com') ||
    url.includes('fonts.gstatic.com') ||
    url.includes('cdn.jsdelivr.net')) {
    return new Response('', {status: 204});
}
```

Untuk CSS `<link>` tags (Google Fonts), MSW tidak bisa memblokir. Gunakan config Laravel:

```php
// tests/Pest.php
beforeEach(function () {
    config(['adminlte.google_fonts.allowed' => false]);
});
```

---

## Penulisan Test

### Menggunakan data-testid (Recommended)

Playwright merekomendasikan menggunakan `data-testid` untuk selector yang paling stabil dan resilient terhadap perubahan UI.

**Keuntungan:**
- Tidak berubah saat text/label berubah
- Tidak berubah saat DOM structure berubah
- Explicit contract antara developer dan tester
- Tidak bergantung pada styling atau layout

#### 1. Tambahkan data-testid di Blade Template

```html
<form action="{{ $login_url }}" method="post" data-testid="login-form">
    @csrf

    <input type="text" name="login" data-testid="login-email" ...>
    <input type="password" name="password" data-testid="login-password" ...>
    <button type="submit" data-testid="login-submit">Masuk</button>
</form>
```

#### 2. Gunakan `@` Shorthand di Test

Pest browser plugin mendukung shorthand `@` untuk `data-testid`:

```php
// ✅ Recommended - gunakan @ shorthand
visit('/login')
    ->fill('@login-email', $email)
    ->fill('@login-password', 'password123')
    ->press('@login-submit')
    ->assertPathIsNot('/login');

// ✅ Alternatif - CSS selector eksplisit
visit('/login')
    ->fill('[data-testid="login-email"]', $email)
    ->fill('[data-testid="login-password"]', 'password123')
    ->press('[data-testid="login-submit"]')
    ->assertPathIsNot('/login');
```

#### 3. Naming Convention

Gunakan format `feature-element` untuk data-testid:

| Element | data-testid | Keterangan |
|---------|-------------|------------|
| Login form | `login-form` | Form container |
| Email input | `login-email` | Input email/username |
| Password input | `login-password` | Input password |
| Submit button | `login-submit` | Tombol submit |
| Filter Kabupaten | `filter-kabupaten` | Select filter kabupaten |
| Filter Kecamatan | `filter-kecamatan` | Select filter kecamatan |
| Filter Desa | `filter-desa` | Select filter desa |
| Tombol Filter | `bt-filter` | Tombol Tampilkan |
| Summary Block | `summary-block` | Container kartu summary |
| Peta | `peta` | Container peta |
| Tabel Penduduk | `tabel-penduduk-block` | Container tabel |
| Chart Item | `chart-item-{key}` | Container chart demografi |

#### 4. Selector Hierarchy

```
@           → data-testid shorthand (recommended)
[data-testid="..."] → CSS selector eksplisit
#id         → ID selector (hindari jika bisa berubah)
[name="..."] → Name selector (untuk form elements)
"text"      → Text selector (untuk links/buttons)
```

### Struktur File

```
tests/
├── Browser/
│   ├── SmokeLoginTest.php              # Test login
│   ├── SmokeDashboardTest.php          # Test dashboard
│   ├── SmokeDashboardDemografiTest.php # Test demografi
│   ├── SessionState.php                # Helper session management
│   ├── ScreenshotHelper.php            # Screenshot management
│   ├── MswSetup.php                    # MSW route & init script generator
│   ├── apply-patch.sh                  # Vendor patch script (composer hook)
│   └── fixtures/                       # JSON fixture files
│       ├── kabupaten.json
│       ├── kecamatan-*.json
│       ├── desa-*.json
│       ├── data-website.json
│       └── statistik-penduduk-*.json
├── Feature/
├── Unit/
└── Pest.php                            # Pest configuration
```

### Test Dasar

#### Menampilkan Halaman

```php
it('displays login page correctly', function () {
    visit('/login')
        ->assertSee('Masuk')
        ->assertVisible('@login-email')
        ->assertVisible('@login-password')
        ->assertVisible('@login-submit');
});
```

#### Fill Form & Submit

```php
it('can login with valid credentials', function () {
    $email = 'pest-' . time() . '@login.test';
    $user = User::factory()->create([
        'email' => $email,
        'password' => 'password123', // Plain text - User model auto-hash
    ]);

    visit('/login')
        ->fill('@login-email', $email)
        ->fill('@login-password', 'password123')
        ->press('@login-submit')
        ->assertPathIsNot('/login');
});
```

#### Assert Error Message

```php
it('shows error for invalid credentials', function () {
    visit('/login')
        ->fill('@login-email', 'wrong@email.com')
        ->fill('@login-password', 'wrongpassword')
        ->press('@login-submit')
        ->assertSee('Masuk');
});
```

#### Test dengan Filter Interaksi

```php
it('applies filter and elements remain visible', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@bt-filter');

    // Set filter value via JavaScript (karena Select2)
    $page->script("$('#filter_kabupaten').val('50.01').trigger('change')");
    $page->click('@bt-filter');

    // Assert elemen masih terlihat setelah filter
    $page->assertVisible('@peta')
        ->assertVisible('@tabel-penduduk-block');
});
```

### API Reference

| Method | Keterangan |
|--------|------------|
| `visit($url)` | Buka halaman |
| `navigate($url)` | Navigasi ke URL |
| `fill($selector, $value)` | Isi input field |
| `type($selector, $value)` | Ketik di input field |
| `typeSlowly($selector, $value, $delay)` | Ketik perlahan |
| `press($text)` | Klik tombol/link |
| `click($selector)` | Klik element |
| `select($field, $option)` | Pilih option di select |
| `submit()` | Submit form pertama |
| `value($selector)` | Ambil nilai input |
| `script($js)` | Jalankan JavaScript |
| `assertSee($text)` | Assert teks ada di halaman |
| `assertVisible($selector)` | Assert element terlihat |
| `assertPathIs($path)` | Assert URL path |
| `assertPathIsNot($path)` | Assert URL path bukan |
| `assertNoJavaScriptErrors()` | Assert tidak ada JS error |
| `screenshot($name)` | Ambil screenshot |

---

## Session State Management

### Masalah

Setiap Pest browser test membuat **browser context baru**, sehingga session/cookies hilang antar test. Login via form membutuhkan ~1.5 detik.

### Solusi

Gunakan `SessionState` helper untuk bypass form login via route `/_pest/login/{id}`.

### SessionState Helper

```php
<?php

namespace Tests\Browser;

use App\Models\User;

final class SessionState
{
    private const STORAGE_PATH = __DIR__ . '/.session_state.json';

    // Login user dan bypass force password reset
    public static function loginAdminUser(): User
    {
        $user = User::where('email', 'admin@admin.com')->firstOrFail();
        $user->update([
            'force_password_reset' => false,
            'password_expires_at' => null,
        ]);
        return $user;
    }

    // Login via route dan navigate ke URL
    public static function loginAndNavigate(
        User $user,
        string $url,
        array $options = []
    ): \Pest\Browser\Api\Webpage {
        $options['headers'] = [
            'Cookie' => self::getSessionCookie($user),
        ];
        return visit($url, $options);
    }

    // Cleanup
    public static function clear(): void { /* ... */ }
}
```

### Penggunaan

```php
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('displays dashboard elements', function () {
    SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor')
        ->assertVisible('@summary-block')
        ->assertVisible('@peta');
});
```

### Performance

| Operasi | Waktu |
|---------|-------|
| Login via form | ~1.5s |
| Login via `/_pest/login/{id}` | ~1.4s |

---

## Custom Login Route

### Tujuan

Bypass form login untuk testing lebih cepat.

### Route Definition

Tambahkan di `routes/web.php`:

```php
// Pest Browser Testing - Quick login route (hanya untuk testing)
Route::get('/_pest/login/{userId}', function ($userId) {
    $user = App\Models\User::findOrFail($userId);
    Auth::login($user);
    return response(status: 200);
})->middleware('web');
```

### Cara Kerja

1. Test mengirim request ke `/_pest/login/{userId}`
2. Route melakukan `Auth::login($user)` tanpa validasi password
3. Session Laravel dibuat
4. Return response 200
5. Test dapat mengakses halaman yang memerlukan autentikasi

---

## Troubleshooting

### Password Field Tidak Terisi

**Gejala:** Form login gagal meski sudah pakai `fill()`.

**Root Cause:** User model punya `password()` mutator yang otomatis `Hash::make()`:

```php
// User.php
protected function password(): Attribute
{
    return Attribute::make(
        set: fn (string $value) => Hash::make($value),
    );
}
```

**Solusi:** Pass plain text password, JANGAN pakai `Hash::make()`:

```php
// ❌ Salah - double hash
User::factory()->create([
    'password' => Hash::make('password123'),
]);

// ✅ Benar - mutator akan hash otomatis
User::factory()->create([
    'password' => 'password123',
]);
```

### Force Password Reset Redirect

**Gejala:** Test redirect ke `/password-reset/force` setelah login.

**Root Cause:** User memiliki `force_password_reset=true` atau password expired.

**Solusi:** Bersihkan flag sebelum test:

```php
$user->update([
    'force_password_reset' => false,
    'password_expires_at' => null,
]);
```

### Google Fonts Blocking

**Gejala:** Test hang atau timeout karena menunggu Google Fonts load.

**Root Cause:** `<link>` tag untuk Google Fonts memblokir page load.

**Solusi:** Disable Google Fonts di test environment:

```php
// tests/Pest.php
beforeEach(function () {
    config(['adminlte.google_fonts.allowed' => false]);
});
```

### Test Timeout

**Gejala:** Test timeout setelah 30 detik.

**Solusi:** Increase timeout di `tests/Pest.php`:

```php
pest()->browser()->timeout(60000); // 60 detik
```

### Playwright Tidak Ditemukan

**Gejala:** Error "playwright not found".

**Solusi:**

```bash
npm install playwright @playwright/test
npx playwright install chromium
```

### MSW Routes Not Matching

**Gejala:** Request API tidak di-intercept, mengembalikan 404 atau data asli.

**Solusi:**
1. Pastikan route di `MswSetup.php` benar
2. Pastikan file fixture ada di `tests/Browser/fixtures/`
3. Pastikan regex escape benar (`\\/` untuk `/`)

---

## Best Practices

### 1. Gunakan data-testid untuk Selector

```php
// ❌ Hindari - CSS selector yang fragile
->fill('input[name="login"]', $email)
->fill('input[type="password"]', $password)

// ✅ Recommended - testid selector yang stable
->fill('@login-email', $email)
->fill('@login-password', $password)
```

### 2. Gunakan `@` Shorthand

```php
// ✅ Lebih pendek dan readable
->fill('@login-email', $email)

// ✅ Alternatif - CSS selector eksplisit (jika perlu)
->fill('[data-testid="login-email"]', $email)
```

### 3. Naming Convention

```
data-testid="{feature}-{element}"

Contoh:
- login-email
- login-password
- login-submit
- filter-kabupaten
- filter-kecamatan
- filter-desa
- bt-filter
- summary-block
- peta
- tabel-penduduk-block
- chart-item-rentang-umur
```

### 4. Jangan Test Internal Implementation

```php
// ❌ Hindari - test implementation detail
->fill('input[name="_token"]', $token)

// ✅ Test user behavior
->fill('@login-email', $email)
->press('@login-submit')
->assertPathIs('/dasbor')
```

### 5. Gunakan Assertion yang Reliable

```php
// ❌ Hindari - text bisa berubah
->assertSee('Selamat datang di Dashboard')

// ✅ Gunakan path assertion
->assertPathIs('/dasbor')

// ✅ Atau gunakan testid untuk element yang spesifik
->assertVisible('@dashboard-welcome')
```

### 6. Hindari `wait()` Method

```php
// ❌ Hindari - memblokir event loop
$page->wait(3);

// ✅ Gunakan assertVisible yang polling
$page->assertVisible('@element');
```

---

## Screenshot Management

### Konfigurasi

Screenshot diatur via environment variable:

```env
# Aktifkan screenshot saat test pass
SCREENSHOT_ON_SUCCESS=true

# Nonaktifkan (default)
SCREENSHOT_ON_SUCCESS=false
```

### Penggunaan

```php
use Tests\Browser\ScreenshotHelper;

it('can login with valid credentials', function () {
    $page = visit('/login')
        ->fill('@login-email', $email)
        ->fill('@login-password', 'password123')
        ->press('@login-submit')
        ->assertPathIsNot('/login');

    // Screenshot hanya disimpan jika SCREENSHOT_ON_SUCCESS=true
    ScreenshotHelper::saveIfEnabled($page, 'login-success');
});
```

### Output

Screenshot tersimpan di `tests/Browser/Screenshots/` dengan format:
```
{nama-test}_{timestamp}.png
```

Contoh:
```
login-page_2026-06-10_07-50-09.png
login-success_2026-06-10_07-50-11.png
login-error_2026-06-10_07-50-14.png
```

### Performa

| Kondisi | Waktu (4 tests) | Tambahan |
|---------|-----------------|----------|
| Tanpa screenshot | ~7.5s | baseline |
| Dengan screenshot | ~10s | **+30%** |

**Rekomendasi:**
- **CI/CD cepat**: Nonaktifkan screenshot (`SCREENSHOT_ON_SUCCESS=false`)
- **Debugging/Reporting**: Aktifkan screenshot (`SCREENSHOT_ON_SUCCESS=true`)
- **Test gagal**: Pest browser otomatis screenshot saat assertion gagal (tidak perlu konfigurasi)

### Screenshot saat Gagal

Pest browser otomatis menyimpan screenshot saat test gagal:

```
A screenshot of the page has been saved to [Tests/Browser/Screenshots/test_name.png]
```

Fitur ini **selalu aktif** tanpa perlu konfigurasi.

---

## Referensi

- [Pest v4 Documentation](https://pestphp.com/docs/browser)
- [pest-plugin-browser](https://github.com/pestphp/pest-browser)
- [Playwright for PHP](https://playwright.dev/php/)
- [MSW Documentation](https://mswjs.io/docs/)
- [Laravel HTTP Testing](https://laravel.com/docs/http-tests)
