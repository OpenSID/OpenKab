# 🚀 Upgrade Plan: Laravel 10 → 13

**Current:** Laravel 10.48.29 / PHP 8.4.21
**Target:** Laravel 13.13.0 / PHP ^8.3 — ✅ **SELESAI**

PHP 8.4.21 sudah tersedia — tidak perlu upgrade PHP.

---

## 📦 Ringkasan Perubahan Dependencies

### composer.json — `require`

| Package | L10 | L11 | L12 | L13 |
|---|---|---|---|---|
| `php` | `^8.1` | `^8.2` | `^8.2` | **`^8.3`** |
| `laravel/framework` | `^10.48` | `^11.0` | `^12.0` | **`^13.0`** |
| `laravel/sanctum` | `^3.3` | `^4.0` | `^4.0` | pastikan kompatibel |
| `laravel/tinker` | `^2.8` | `^2.9` | `^2.9` | cek |
| `nunomaduro/collision` | `^7.0` | `^8.1` | `^8.1` | `^8.1` |
| `phpunit/phpunit` | `^10.1` | `^10.5` | **`^11.0`** | `^11.0` |
| `spatie/laravel-ignition` | `^2.0` | `^2.0` | — | — (diganti `laravel/ignition` by default) |

### Package Pihak Ketiga — Perlu Dicek Kompatibilitasnya

| Package | Versi Saat Ini | Perlu Dicek |
|---|---|---|
| `akaunting/laravel-apexcharts` | `^3.0` | ✅ L13 compatible? |
| `alexusmai/laravel-file-manager` | `^3.0` | ⚠️ Perlu verifikasi |
| `bensampo/laravel-enum` | `^6.3` | ⚠️ Cek support L11+ |
| `cviebrock/eloquent-sluggable` | `^10.0` | ✅ Cek versi terbaru |
| `diglactic/laravel-breadcrumbs` | `^8.1` | ✅ Biasanya aman |
| `intervention/image` | `^2.7` | ⚠️ Intervention Image 3.x mungkin diperlukan |
| `jeroennoten/laravel-adminlte` | `^3.9` | ⚠️ Perlu verifikasi L13 support |
| `kalnoy/nestedset` | `^6.0` | ✅ Cek versi terbaru |
| `mews/captcha` | `^3.3` | ⚠️ Perlu verifikasi |
| `mews/purifier` | `^3.4` | ⚠️ Perlu verifikasi |
| `spatie/laravel-activitylog` | `^4.7` | ✅ Biasanya update cepat |
| `spatie/laravel-permission` | `^6.4` | ✅ Cek versi terbaru |
| `spatie/laravel-query-builder` | `^5.2` | ✅ Cek versi terbaru |
| `yajra/laravel-datatables` | `^10.0` | ✅ Cek versi terbaru |
| `barryvdh/laravel-debugbar` | `^3.9` | ✅ Cek versi terbaru |
| `laravel/pint` | `^1.13` | ✅ udah bebas, update aja |
| `laravel/sail` | `^1.26` | ✅ |
| `doctrine/dbal` | `^3.6` | ⚠️ Bisa dihapus (L11 gak butuh) |

---

## 📋 Tahapan Upgrade

### 🟡 Fase 1: Laravel 10 → 11

#### 1.1 Update composer.json
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.9"
  },
  "require-dev": {
    "nunomaduro/collision": "^8.1",
    "phpunit/phpunit": "^10.5"
  }
}
```

#### 1.2 Perubahan di Laravel 11
- **Application structure**: Laravel 11 pakai `bootstrap/app.php` baru, Http/Console/Exception handler dipindah ke sana. Tapi backward compatible — bisa tetap pakai struktur lama.
- **doctrine/dbal**: Bisa dihapus, L11 gak butuh.
- **Carbon 3**: Perubahan minor di API Carbon.
- **Floating-point types**: `Schema::float()` method returns `double` now.
- **Modifying columns**: Harus specify semua properti (type, length, dll).
- **Password rehashing**: Otomatis rehash saat login.
- **Per-second rate limiting**: Ubah dari `throttle:60,1` ke `throttle:60` (per-second).
- **Sanctum 4**: Migrasi Sanctum perlu dipublikasi ulang.
- **Spatie Once**: Bisa hapus `spatie/once`, Laravel punya `once()` helper sendiri.
- **`$routeMiddleware` diganti `$middlewareAliases`**: Di Http Kernel.

#### 1.3 Commands
```bash
composer update --with-all-dependencies
php artisan optimize:clear
```

---

### 🟡 Fase 2: Laravel 11 → 12

#### 2.1 Update composer.json
```json
{
  "require": {
    "laravel/framework": "^12.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0"
  }
}
```

#### 2.2 Perubahan di Laravel 12
- **Very minor breaking changes** — mostly low impact.
- **Eloquent `HasUuids` & UUIDv7**: Perubahan di UUID generation default ke v7.
- **Concurrency result index mapping**: `Concurrency::run()` result index berubah.
- **Image validation SVG exclusion**: Validasi gambar otomatis tolak SVG.
- **Local filesystem default root**: Berubah ke `storage/app/private`.

#### 2.3 Commands
```bash
composer update --with-all-dependencies
php artisan optimize:clear
```

---

### 🟡 Fase 3: Laravel 12 → 13

#### 3.1 Update composer.json
```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^13.0"
  }
}
```

#### 3.2 Perubahan di Laravel 13 — **High Impact**
1. **PHP 8.3 required** — ✅ sudah terpenuhi (PHP 8.4)
2. **Request Forgery Protection (CSRF)** — Origin-aware validation + token-based
3. **Updating dependencies** — `laravel/framework` ke `^13.0`

#### 3.3 Perubahan Medium Impact
1. **Cache `serializable_classes`** — Tambah konfigurasi baru di `config/cache.php`:
   ```php
   'serializable_classes' => [
       Illuminate\Support\Collection::class,
       Illuminate\Database\Eloquent\Collection::class,
       // tambah class kustom yang di-cache
   ],
   ```
2. **Session `serialization`** — Set di `config/session.php`:
   ```php
   'serialization' => 'php',
   ```
3. **Database `upsert` behavior** — Berubah untuk MySQL/MariaDB.

#### 3.4 Perubahan Low Impact
1. **Cache prefix & session cookie name** — Nilai default berubah. Set eksplisit di `.env`:
   ```
   CACHE_PREFIX=nama_aplikasi_cache
   SESSION_COOKIE=nama_aplikasi_session
   ```
2. **Pagination Bootstrap view names** — `pagination::default` → `pagination::bootstrap-3`
3. **Container::call & nullable class defaults**
4. **Domain route registration priority**
5. **MySQL DELETE queries** — JOIN/ORDER BY/LIMIT behavior berubah
6. **Polymorphic pivot table name generation**
7. **`Str` factory reset between tests**

#### 3.5 Commands
```bash
composer update --with-all-dependencies
php artisan optimize:clear
```

---

## ⚠️ Checklist Package Compatibility

Sebelum eksekusi, verifikasi tiap package:

- [ ] `akaunting/laravel-apexcharts` — cek versi L13
- [ ] `alexusmai/laravel-file-manager` — cek latest
- [ ] `bensampo/laravel-enum` — mungkin perlu migrasi ke PHP 8 enums
- [ ] `cviebrock/eloquent-sluggable` — update ke ^11.0?
- [ ] `intervention/image` — migrasi ke 3.x jika perlu
- [ ] `jeroennoten/laravel-adminlte` — cek kompatibilitas
- [ ] `mews/captcha` — cek versi
- [ ] `mews/purifier` — cek versi
- [ ] `spatie/laravel-activitylog` — update ke ^4.8+ atau ^5.0
- [ ] `spatie/laravel-permission` — update ke ^6.5+
- [ ] `yajra/laravel-datatables` — update ke ^10.5+
- [ ] `stevebauman/location` — cek versi
- [ ] `shetabit/visitor` — cek versi

---

## 🔧 Langkah Eksekusi

### Pra-Upgrade
1. Backup DB & file project
2. `git commit` or `git stash` semua perubahan
3. Buat branch baru: `git checkout -b upgrade/laravel-13`
4. Catat nilai current `config('cache.prefix')` via tinker

### Eksekusi
5. Update `composer.json` untuk L11 → `composer update` → test
6. Update `composer.json` untuk L12 → `composer update` → test
7. Update `composer.json` untuk L13 → `composer update` → test
8. Perbaiki konflik dependency sat per sat

### Post-Upgrade
9. Tambah config cache & session baru (L13)
10. Set `CACHE_PREFIX` & `SESSION_COOKIE` di `.env`
11. `php artisan optimize:clear`
12. `php artisan migrate`
13. `php artisan test`
14. `npm install && npm run build`
15. Review perubahan di `bootstrap/app.php` (pindah middleware dll)

---

## 📝 Catatan Post-Upgrade

### Package yang Harus Update Major Version

| Package | L10 → L13 | Perubahan |
|---|---|---|
| `diglactic/laravel-breadcrumbs` | ^8.1 → ^10.1 | BC break minimal |
| `cviebrock/eloquent-sluggable` | ^10.0 → ^13.0 | 3 major version loncatan |
| `yajra/laravel-datatables` | ^10.0 → ^13.0 | 3 major version loncatan |
| `spatie/laravel-query-builder` | ^5.2 → ^6.0 | 1 major version |
| `kalnoy/nestedset` | ^6.0 → ^7.0 | 1 major version |
| `intervention/image` | ^2.7 → ^3.11 | v2→v3: API berubah total |
| `intervention/image-laravel` | (none) → ^1.5 | Adapter baru untuk Laravel |
| `spatie/laravel-csp` | ^2.0 → ^3.25 | **BC break besar** |
| `barryvdh/laravel-debugbar` | ^3.9 → ^4.0 | 1 major version |
| `laravel/tinker` | ^2.8 → ^3.0 | 1 major version |
| `spatie/laravel-package-tools` | (implicit) → ^1.17 | dependency CSP v3 |
| `akaunting/laravel-apexcharts` | ^3.0 → ^4.0 | 1 major version |

### Spatie CSP v2 → v3 — Perubahan Kritis

- **Tidak ada `csp_nonce()` helper lagi** — harus didefinisikan manual (`app('csp-nonce')`)
- **Policy class → Preset interface**: Ganti extends `Spatie\Csp\Policies\Basic` → implements `Spatie\Csp\Preset`
- **`addDirective()` → `add()`**: Method directive berubah nama
- **Config berubah**: `policy` → `presets` array; tambah `directives`, `nonce_enabled`, dll
- **`shouldBeApplied()` dihapus**: Route exclusion harus via middleware terpisah
- **Blade directive**: `@cspNonce` bukan `{{ csp_nonce() }}`

### Intervention Image v2 → v3 — Perubahan Kritis

- **Service provider**: `Intervention\Image\ImageServiceProvider::class` → `Intervention\Image\Laravel\ServiceProvider::class`
- **Facade**: `Intervention\Image\Facades\Image::class` → `Intervention\Image\Laravel\Facades\Image::class`
- **API**: `Image::make($path)` → `Image::read($path)`, `->resize()` → `->scale()`, `->save($path, $quality)` → `->save($path, quality: $quality)`
- **Config**: File config baru (`config/image.php`) dengan key `driver` dan `options`

### Test Environment

- **DB connection untuk test dikomentari** di phpunit.xml (`sqlite :memory:`). Feature tests yang butuh DB akan 500.
- **Unit tests** (19/19 ✅) — semua lulus
- **CSP Policy tests** (5/5 ✅) — diadaptasi untuk v3

## 🔗 Referensi

- https://laravel.com/docs/11.x/upgrade
- https://laravel.com/docs/12.x/upgrade
- https://laravel.com/docs/13.x/upgrade
- https://github.com/laravel/laravel/compare/10.x...13.x
