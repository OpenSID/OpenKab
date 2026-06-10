# Smoke Test dengan Pest Browser (Playwright)

Panduan lengkap pembuatan smoke test menggunakan **Pest v4** + **pest-plugin-browser** (Playwright backend) untuk testing browser di Laravel.

## Daftar Isi

- [Arsitektur](#arsitektur)
- [Prasyarat](#prasyarat)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
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
Chromium Browser
    │
    ▼
Laravel HTTP Server (amphp)
    │
    ▼
Aplikasi Laravel
```

Pest browser plugin menjalankan **Laravel HTTP server lokal** (amphp) dan mengontrol **Chromium browser** via Playwright WebSocket. Test ditulis 100% dalam syntax PHP Pest.

---

## Prasyarat

| Komponen | Versi | Keterangan |
|----------|-------|------------|
| PHP | 8.4+ | |
| Laravel | 13+ | |
| Node.js | 18+ | Untuk Playwright |
| Composer | 2+ | |

---

## Instalasi

### 1. Install Pest v4

```bash
composer require pestphp/pest:^4.0 --dev -W
composer require pestphp/pest-plugin-browser:^4.0 --dev -W
```

### 2. Install Playwright (npm)

```bash
npm install playwright @playwright/test --save-dev
npx playwright install chromium
```

### 3. Publish Pest Configuration

Buat `tests/Pest.php`:

```php
<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Browser');

// Timeout untuk browser tests (dalam milidetik)
pest()->browser()->timeout(30000);
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
```

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

## Penulisan Test

### Struktur File

```
tests/
├── Browser/
│   ├── SmokeLoginTest.php      # Test login
│   ├── SmokeDashboardTest.php  # Test dashboard
│   └── SessionState.php        # Helper session management
├── Feature/
├── Unit/
└── Pest.php                    # Pest configuration
```

### Test Dasar

#### Menampilkan Halaman

```php
it('displays login page correctly', function () {
    visit('/login')
        ->assertSee('Masuk')
        ->assertVisible('input[name="login"]')
        ->assertVisible('input[name="password"]')
        ->assertVisible('button[type="submit"]');
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
        ->fill('input[name="login"]', $email)
        ->fill('input[name="password"]', 'password123')
        ->press('Masuk')
        ->assertPathIsNot('/login');
});
```

#### Assert Error Message

```php
it('shows error for invalid credentials', function () {
    visit('/login')
        ->fill('input[name="login"]', 'wrong@email.com')
        ->fill('input[name="password"]', 'wrongpassword')
        ->submit()
        ->assertSee('Masuk');
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
| `submit()` | Submit form pertama |
| `value($selector)` | Ambil nilai input |
| `script($js)` | Jalankan JavaScript |
| `assertSee($text)` | Assert teks ada di halaman |
| `assertVisible($selector)` | Assert element terlihat |
| `assertPathIs($path)` | Assert URL path |
| `assertPathIsNot($path)` | Assert URL path bukan |
| `wait($seconds)` | Tunggu beberapa detik |
| `screenshot($name)` | Ambil screenshot |
| `dd()` | Dump & die |

---

## Session State Management

### Masalah

Setiap Pest browser test membuat **browser context baru**, sehingga session/cookies hilang antar test. Login via form membutuhkan ~1.5 detik.

### Solusi

Gunakan `SessionState` helper untuk save/restore user ID ke file, lalu bypass form login via route `/_pest/login/{id}`.

### SessionState Helper

```php
<?php

namespace Tests\Browser;

use App\Models\User;

final class SessionState
{
    private const STORAGE_PATH = __DIR__ . '/.session_state.json';

    // Simpan state user ke file
    public static function saveForUser(User $user): void
    {
        $state = [
            'user_id' => $user->id,
            'email' => $user->email,
            'created_at' => now()->toIso8601String(),
        ];
        file_put_contents(self::STORAGE_PATH, json_encode($state, JSON_PRETTY_PRINT));
    }

    // Load state dari file
    public static function load(): ?array { /* ... */ }

    // Hapus state file
    public static function clear(): void { /* ... */ }

    // Buat atau ambil user exist
    public static function getOrCreateUser(string $emailPrefix = 'pest-session'): User { /* ... */ }

    // Login langsung via route
    public static function loginAs(User $user): \Pest\Browser\Api\AwaitableWebpage
    {
        $result = visit("/_pest/login/{$user->id}");
        self::saveForUser($user);
        return $result;
    }

    // Restore session dari file
    public static function restoreSession(): ?\Pest\Browser\Api\AwaitableWebpage { /* ... */ }
}
```

### Penggunaan

```php
use Tests\Browser\SessionState;

it('can restore session from saved state', function () {
    // Buat user
    $user = SessionState::getOrCreateUser('pest-restore');

    // Simpan state
    SessionState::saveForUser($user);

    // Load state
    $state = SessionState::load();
    expect($state)->not->toBeNull();
    expect($state['user_id'])->toBe($user->id);

    // Login via quick route
    visit("/_pest/login/{$user->id}")
        ->navigate('/dasbor')
        ->assertPathIs('/dasbor');

    // Cleanup
    SessionState::clear();
});
```

### Performance

| Operasi | Waktu |
|---------|-------|
| Login via form | ~1.5s |
| Login via `/_pest/login/{id}` | ~1.4s |
| **Session restore** | **~0.55s** (3x lebih cepat) |

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

### Variable Undefined di View

**Gejala:** `Undefined variable $settingAplikasi` di view.

**Kemungkinan:** View partial yang di-include sebelum AppServiceProvider boot selesai.

**Solusi:** Gunakan `assertPathIs()` alih-alih `assertSee()`:

```php
// ❌ Bisa gagal
->assertSee('Dasbor');

// ✅ Lebih reliable
->assertPathIs('/dasbor');
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

---

## Referensi

- [Pest v4 Documentation](https://pestphp.com/docs/browser)
- [pest-plugin-browser](https://github.com/pestphp/pest-browser)
- [Playwright for PHP](https://playwright.dev/php/)
- [Laravel HTTP Testing](https://laravel.com/docs/http-tests)
