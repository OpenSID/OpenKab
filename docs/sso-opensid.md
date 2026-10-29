# SSO OpenSID: Panduan Deployment & Operasional

**Feature**: SSO Akses Panel Admin OpenSID dari OpenKab
**Branch**: `2502-opensid-sso-access` | **Tanggal**: 2026-08-08

## Ringkasan

Administrator OpenKab (sesi aktif + 2FA selesai) dapat masuk ke panel admin OpenSID desa tanpa login ulang melalui tombol "Masuk ke OpenSID" pada halaman Data Desa. OpenKab menerbitkan token JWT **RS256** (RSA-2048) berumur pendek (maksimal 10 menit, default 5) yang hanya dapat digunakan sekali; OpenSID memverifikasi token ke OpenKab sebelum membuat sesi. Private key hanya disimpan di OpenKab; public key didistribusikan ke instalasi OpenSID.

## Prasyarat

- PHP 8.4, Laravel 13 (repositori OpenKab ini).
- MySQL dengan 2 tabel baru hasil migrasi:
  - `openkab_sso_logs` (audit append-only)
  - `openkab_sso_tokens` (token sekali pakai / anti-replay)
- **API database gabungan** harus mengembalikan field `website` per desa pada `/api/v1/wilayah/penduduk` — ini satu-satunya sumber base URL instalasi OpenSID. Desa tanpa `website` tidak menampilkan tombol SSO (diganti keterangan "URL website belum diisi").
- Dependency baru: `firebase/php-jwt` (^7.0). v6.x tidak digunakan karena terdampak security advisory.
- Sisi OpenSID (repo terpisah) harus menerapkan `specs/001-opensid-sso-access/contracts/opensid-sso-contract.md`.

## Langkah Deployment

1. **Pasang dependency**

   ```bash
   composer install
   ```

2. **Generasi & konfigurasi kunci RS256** (sekali saat pemasangan)

   ```bash
   php artisan sso:generate-keys
   # Opsional: --bits=4096 --path=storage/sso --env-file=.env --force
   ```

   Command ini membuat keypair (private chmod 0600 + public), menulisnya ke
   `storage/sso/`, dan mengisi `SSO_SIGNING_PRIVATE_KEY_FILE`/`SSO_SIGNING_PUBLIC_KEY_FILE`
   di `.env`. Alternatif manual:

   ```bash
   openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:2048 -out sso-private.pem
   openssl pkey -in sso-private.pem -pubout -out sso-public.pem
   chmod 600 sso-private.pem
   ```

   Sebarkan **`sso-public.pem`** ke setiap instalasi OpenSID (out-of-band); private key
   **tidak pernah** dibagikan. Tambahkan ke `.env` produksi:

   ```env
   # Wajib: private key hanya di OpenKab; public key versi yang sama untuk OpenSID.
   SSO_SIGNING_PRIVATE_KEY_FILE=/path/sso-private.pem     # atau SSO_SIGNING_PRIVATE_KEY=<PEM base64>
   SSO_SIGNING_PUBLIC_KEY_FILE=/path/sso-public.pem       # atau SSO_SIGNING_PUBLIC_KEY=<PEM base64>
   SSO_SIGNING_PUBLIC_KEYS_FILE=                          # public key lama (koma) untuk rotasi transisi

   # Wajib, minimal 32 byte. Nilai dibagikan ke instalasi OpenSID.
   SSO_CALLBACK_SECRET=<random-32-bytes-min>

   # Opsional
   SSO_TOKEN_TTL=300              # detik; maksimum 600
   SSO_IP_WHITELIST=              # IP callback yang diizinkan (koma), kosong = semua
   SSO_RATE_LIMIT_MAX=5           # generate-session per menit per user+IP
   SSO_CLOCK_SKEW_TOLERANCE=30    # toleransi selisih jam callback
   ```

   Generate secret contoh: `php -r "echo bin2hex(random_bytes(32));"`.

   **Peringatan**: `SSO_SIGNING_PRIVATE_KEY`/`SSO_SIGNING_PUBLIC_KEY` (atau file) dan
   `SSO_CALLBACK_SECRET` HARUS di-set. Aplikasi menolak operasi SSO bila private key
   tidak valid RSA ≥2048-bit atau callback secret < 32 byte.

3. **Migrasi**

   ```bash
   php artisan migrate
   ```

   Migration `2026_08_11_000001_add_sso_audit_permission` otomatis membuat permission `sso-audit-read` + melampirkannya ke role `administrator` dan menjalankan `admin:menu-update` (menu "Audit Akses SSO").

4. **Resolusi URL OpenSID dari website desa (API gabungan)**
   - Base URL panel admin di-resolve **server-side** dari field `attributes.website` desa pada `/api/v1/wilayah/penduduk` (`OpenSidUrlResolver`, hasil di-cache singkat). URL akhir = `<website>/admin/sso-login`.
   - Input klien (frontend) **tidak pernah** menjadi sumber URL redirect (FR-022).
   - Desa tanpa `website` → tombol "Masuk ke OpenSID" tidak ditampilkan; diganti keterangan "URL website belum diisi". `generate-session` untuk desa tersebut ditolak (CONFIGURATION_ERROR) dan dicatat.
   - Tidak ada lagi konfigurasi manual per desa (`desa_sso_configs`/`SSO_OPENSID_BASE_URL` dihapus).

5. **Integrasi sisi OpenSID**
   - Implementasi `POST /admin/sso-login` dan tabel `opensid_sso_logs` sesuai kontrak.
   - OpenSID memanggil `POST {openkab}/api/v1/sso/verify-token` dengan header `X-SSO-Callback-Key`, `X-SSO-Callback-Timestamp`, `X-SSO-Callback-Signature` (HMAC-SHA256 atas body memakai `SSO_CALLBACK_SECRET`).
   - **Proteksi lintas-situs (FR-011, wajib)**: `POST /admin/sso-login` harus menolak request yang header `Origin`/`Referer`-nya tidak cocok dengan daftar `openkab_allowed_origins` (stateless, tanpa CSRF token pre-login); halaman login SSO wajib mengirim `X-Frame-Options: DENY` + `Content-Security-Policy: frame-ancestors 'none'` (anti-clickjacking). Detail: `contracts/opensid-sso-contract.md`.

## Operasional

### Rotasi kunci RS256 & secret callback
1. Terbitkan keypair baru (`openssl genpkey ... 2048`), set `SSO_SIGNING_PRIVATE_KEY(FILE)`/`SSO_SIGNING_PUBLIC_KEY(FILE)` baru di OpenKab, dan set `SSO_CALLBACK_SECRET` baru di kedua sistem.
2. Tambahkan public key lama ke `SSO_SIGNING_PUBLIC_KEYS_FILE` OpenKab selama masa transisi, dan sebarkan public key baru ke OpenSID.
3. Token yang beredar berumur pendek (≤10 menit), sehingga rotasi berdampak seketika.
4. Setelah masa transisi, hapus public key lama dari `SSO_SIGNING_PUBLIC_KEYS_FILE`.
5. Verifikasi `php artisan test tests/Feature/Sso` pada lingkungan staging setelah rotasi.

### Pembersihan token kedaluwarsa
Tabel `openkab_sso_tokens` dapat tumbuh. Jadwalkan pembersihan berkala (contoh via cron):

```bash
php -r "App\Models\Sso\OpenKabSsoToken::where('expires_at','<',now())->orWhereNotNull('used_at')->delete();"
```

### Log audit tidak dapat diubah
Model `OpenKabSsoLog` menolak `update`/`delete`. Untuk proteksi lebih kuat di level database, dapat ditambahkan trigger/revoke DELETE pada tabel `openkab_sso_logs` oleh DBA.

### Dashboard audit
Super admin (role `administrator`) dapat meninjau seluruh percobaan akses melalui menu Pengaturan OpenSID → "Audit Akses SSO" (`/sso/audit`), dengan filter status, rentang waktu, administrator, dan kode desa.

## Keamanan

- Token JWT RS256 (RSA-2048); private key hanya di OpenKab, OpenSID memegang public key; verifikasi memaksa `alg=RS256` (anti downgrade).
- `exp` ≤ 600 detik; `nbf` = `iat`; toleransi selisih jam 30 detik; `jti` unik sekali pakai (konsumsi atomik).
- Generate-session: rate limit 5/menit per user+IP; wajib sesi aktif, role administrator, dan 2FA terverifikasi.
- Callback verify: otentikasi sekret bersama + HMAC + timestamp; respons error generik tanpa data pribadi.
- **Lintas-situs (CSRF/clickjacking, FR-011)**:
  - OpenKab `generate-session`: request yang header `Origin`/`Referer`-nya tidak cocok dengan `config('app.url')` ditolak (403 `ORIGIN_INVALID`, tercatat `reason=origin_invalid`). Validasi dilewati di env `local`/`testing` — daftar env skip dapat diubah via `config('sso.origin_check_skip_envs')`.
  - OpenSID `POST /admin/sso-login`: Origin/Referer wajib cocok dengan `openkab_allowed_origins` (tanpa CSRF token pre-login); halaman login SSO wajib mengirim `X-Frame-Options: DENY` + `Content-Security-Policy: frame-ancestors 'none'`.
- Semua komunikasi wajib HTTPS di produksi (URL OpenSID non-HTTPS ditolak di luar lingkungan lokal).
- Seluruh percobaan (berhasil/gagal) tercatat di `openkab_sso_logs`.

## Referensi
- Kontrak API OpenKab: `specs/001-opensid-sso-access/contracts/openkab-sso-api.md`
- Kontrak integrasi OpenSID: `specs/001-opensid-sso-access/contracts/opensid-sso-contract.md`
