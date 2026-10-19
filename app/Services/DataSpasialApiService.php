<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DataSpasialReason;
use App\Exceptions\DataSpasialUnavailableException;
use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pintu tunggal seluruh pemanggilan API Satu Data `/api/v1/dataspasial/*`
 * (FR-002).
 *
 *Aturan yang WAJIB dipatuhi modul lain:
 * - tidak boleh memanggil `Http::` langsung ke endpoint DataSpasial (SC-009);
 * - setiap method publik selalu mengembalikan `DataSpasialResult` dan TIDAK
 *   pernah melempar exception HTTP ke pemanggil;
 * - pesan teknis dari API hanya masuk log, tidak pernah sampai ke view (FR-016).
 *
 * Sengaja TIDAK extends `BaseApiService`: signature `array`-returning dan
 * flash `error_api` milik class itu tidak dapat memenuhi FR-005 tanpa
 * mengubah service lain yang sudah berjalan (lihat research.md R14).
 */
class DataSpasialApiService
{
    /**
     * Kunci cache yang menyimpan nomor generasi cache baca.
     */
    private const GENERATION_KEY = 'dataspasial:cache-generation';

    public function isConfigured(): bool
    {
        return $this->baseUrl() !== '' && $this->resolveToken() !== '';
    }

    /**
     * URL dasar API Satu Data — mengikuti pengaturan database gabungan (R1).
     */
    public function baseUrl(): string
    {
        return rtrim((string) config('app.databaseGabunganUrl', ''), '/');
    }

    /**
     * Token dari Setting pengaturan database gabungan (R1).
     *
     * Tidak ada konfigurasi/env khusus DataSpasial: kredensial yang sama dipakai
     * oleh service database gabungan lainnya.
     */
    public function resolveToken(): string
    {
        return (string) (Setting::where('key', 'database_gabungan_api_key')->first()?->value ?? '');
    }

    public function timeout(): int
    {
        $timeout = (int) config('dataspasial.timeout', 10);

        return $timeout > 0 ? $timeout : 10;
    }

    public function cacheTtl(): int
    {
        return max(0, (int) config('dataspasial.cache_ttl', 300));
    }

    /**
     * Header identitas pemanggil agar tercatat pada audit API (API-07 / #473).
     *
     * Header selalu dikirim; bila tidak ada konteks pengguna keduanya kosong
     * sehingga sisi API dapat membedakan "klien tanpa konteks" dari
     * "header tidak dikirim" (R7).
     *
     * @return array<string, string>
     */
    public function userHeaders(): array
    {
        $user = Auth::user();

        return [
            (string) config('dataspasial.user_id_header', 'X-OpenKab-User-Id') => (string) ($user?->id ?? ''),
            (string) config('dataspasial.user_name_header', 'X-OpenKab-User-Name') => (string) ($user?->name ?? ''),
        ];
    }

    /**
     * Path absolut endpoint DataSpasial relatif terhadap base URL.
     */
    public function endpoint(string $path): string
    {
        $prefix = rtrim((string) config('dataspasial.path_prefix', '/api/v1/dataspasial'), '/');

        return $prefix.'/'.ltrim($path, '/');
    }

    /**
     * Kunci cache untuk operasi baca pada path + query tertentu.
     *
     * Memuat nomor generasi cache agar invalidasi menjadi O(1) tanpa perlu
     * menyisir kunci satu per satu (lihat `invalidateCache()`).
     *
     * @param  array<string, mixed>  $query
     */
    public function cacheKey(string $path, array $query = []): string
    {
        ksort($query);

        return (string) config('dataspasial.cache_prefix', 'dataspasial:get:')
            .$this->cacheGeneration()
            .':'.ltrim($path, '/')
            .':'.md5($this->normalizeQuery($query));
    }

    /**
     * Nomor generasi cache saat ini.
     */
    public function cacheGeneration(): int
    {
        return (int) ($this->cacheStore()->get(self::GENERATION_KEY) ?? 0);
    }

    public function isAvailable(DataSpasialResult $result): bool
    {
        return ! $result->isUnavailable();
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>|null  $body
     */
    protected function request(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        ?int $schemaVersion = null
    ): DataSpasialResult {
        $path = $this->endpoint($path);
        $isRead = strtoupper($method) === 'GET';

        if (! $this->isConfigured()) {
            Log::warning('DataSpasial: konfigurasi tidak lengkap', [
                'endpoint' => $path,
                'method' => strtoupper($method),
                'has_base_url' => $this->baseUrl() !== '',
            ]);

            return DataSpasialResult::unavailable(DataSpasialReason::fromValue(DataSpasialReason::NotConfigured));
        }

        $cacheKey = $this->cacheKey($path, $query);

        if ($isRead && $this->cacheTtl() > 0) {
            $cached = $this->cacheStore()->get($cacheKey);

            if (is_array($cached) && isset($cached['status'])) {
                return DataSpasialResult::fromCachePayload($cached);
            }
        }

        try {
            $response = $this->httpClient($schemaVersion)
                ->send(strtoupper($method), $this->baseUrl().$path, array_filter([
                    'query' => $query ?: null,
                    'json' => $body,
                ], static fn ($value) => $value !== null));
        } catch (DataSpasialUnavailableException $exception) {
            return DataSpasialResult::unavailable(
                $exception->reason,
                $exception->httpStatus,
                $exception->getMessage()
            );
        } catch (ConnectionException $exception) {
            return $this->logAndReturnUnavailable(
                DataSpasialReason::fromValue(
                    $this->isTimeout($exception) ? DataSpasialReason::Timeout : DataSpasialReason::ConnectionFailed
                ),
                $path,
                $method,
                0,
                $exception->getMessage()
            );
        } catch (\Throwable $exception) {
            return $this->logAndReturnUnavailable(
                DataSpasialReason::fromValue(DataSpasialReason::ConnectionFailed),
                $path,
                $method,
                0,
                $exception->getMessage()
            );
        }

        $result = $this->toResult($response, $path);

        if ($isRead) {
            // Hanya hasil sukses yang di-cache; `unavailable` tidak pernah
            // dibekukan agar outage tidak bertahan selama TTL (R8).
            if (! $result->isUnavailable()) {
                $this->remember($cacheKey, $result);
            }
        } elseif (! $result->isUnavailable()) {
            $this->invalidateCache();
        }

        return $result;
    }

    /**
     * Bangun `PendingRequest` lengkap dengan token, timeout, dan header audit.
     */
    protected function httpClient(?int $schemaVersion = null): PendingRequest
    {
        $client = Http::withHeaders(array_merge([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->resolveToken(),
        ], $this->userHeaders()))
            ->timeout($this->timeout());

        $client = $client->withHeader('Content-Type', 'application/json');

        if ($schemaVersion !== null) {
            $client = $client->withHeader('X-Schema-Version', (string) $schemaVersion);
        }

        return $client;
    }

    /**
     * Map respons HTTP menjadi `DataSpasialResult` sesuai kontrak §4.1.
     */
    protected function toResult(Response $response, string $path): DataSpasialResult
    {
        $status = $response->status();
        $payload = $response->json();

        if ($response->successful()) {
            return $this->fromSuccessfulPayload($payload, $status);
        }

        $message = is_array($payload) ? ($payload['message'] ?? $response->body()) : $response->body();
        $errorCode = is_array($payload) ? ($payload['error_code'] ?? null) : null;

        $reason = DataSpasialReason::fromValue(match (true) {
            $status === 503 => DataSpasialReason::ModuleNotInstalled,
            $status >= 500 => DataSpasialReason::ServiceUnavailable,
            $status === 400 && $errorCode === 'SchemaVersionUnsupported' => DataSpasialReason::SchemaVersionUnsupported,
            in_array($status, [401, 403], true) => DataSpasialReason::Unauthorized,
            default => DataSpasialReason::ClientError,
        });

        Log::warning('DataSpasial: permintaan gagal', array_filter([
            'endpoint' => $path,
            'http_status' => $status,
            'reason' => $reason->value,
            'error_code' => $errorCode,
            'message' => $message,
        ], static fn ($value) => $value !== null));

        return DataSpasialResult::unavailable(
            $reason,
            $status,
            is_string($message) ? $message : null
        );
    }

    protected function fromSuccessfulPayload(mixed $payload, int $status): DataSpasialResult
    {
        $items = $this->extractItems($payload);
        $meta = $this->extractMeta($payload);

        if ($items === []) {
            return DataSpasialResult::empty($meta, null, $status);
        }

        return DataSpasialResult::data($items, $meta, null, $status);
    }

    /**
     * Ambil daftar item dari envelope Fractal/JSON:API maupun payload polos.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function extractItems(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        // GeoJSON dan agregat (ringkasan) tidak memakai envelope data[].
        if (array_key_exists('type', $payload) && $payload['type'] === 'FeatureCollection') {
            return [$payload];
        }

        $items = $payload['data'] ?? null;

        if ($items === null) {
            return isset($payload['data']) && is_array($payload['data']) ? [] : [$payload];
        }

        // `data` bisa berupa objek tunggal (mis. hasil impor) atau daftar item.
        if (! array_is_list($items)) {
            return [$items];
        }

        return array_values(array_filter($items, 'is_array'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractMeta(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        return array_filter([
            'pagination' => is_array($payload['meta']['pagination'] ?? null) ? $payload['meta']['pagination'] : null,
            'links' => is_array($payload['links'] ?? null) ? $payload['links'] : null,
        ], static fn ($value) => $value !== null);
    }

    /**
     * Simpan hasil baca yang sukses. Respons `unavailable` tidak pernah
     * di-cache agar outage tidak "membeku" selama TTL (R8).
     */
    protected function remember(string $cacheKey, DataSpasialResult $result): void
    {
        if ($this->cacheTtl() <= 0) {
            return;
        }

        $this->cacheStore()->put($cacheKey, $result->toCachePayload(), $this->cacheTtl());
    }

    /**
     * Buang seluruh cache baca DataSpasial.
     *
     * Dilakukan dengan menaikkan nomor generasi cache sehingga seluruh kunci
     * baca lama langsung tidak terjangkau, alih-alih menyisir kunci satu
     * per satu. Invalidasi sengaja kasar per-namespace: menulis skema
     * memengaruhi semua baca (nilai, adopsi, ringkasan), sehingga invalidasi
     * per-endpoint rawan menampilkan data basi (FR-006/SC-008).
     *
     * Kunci lama tetap tersimpan sampai TTL-nya habis, namun tidak akan
     * pernah dibaca lagi.
     */
    public function invalidateCache(): void
    {
        $store = $this->cacheStore();
        $next = $this->cacheGeneration() + 1;

        $store->forever(self::GENERATION_KEY, $next);

        Log::debug('DataSpasial: cache baca diinvalidasi', [
            'prefix' => (string) config('dataspasial.cache_prefix', 'dataspasial:get:'),
            'generation' => $next,
        ]);
    }

    protected function cacheStore(): Repository
    {
        $store = config('dataspasial.cache_store');

        return Cache::store($store ?: null);
    }

    protected function logAndReturnUnavailable(
        DataSpasialReason $reason,
        string $path,
        string $method,
        int $status,
        ?string $message
    ): DataSpasialResult {
        Log::warning('DataSpasial: permintaan tidak dapat dikirim', array_filter([
            'endpoint' => $path,
            'method' => strtoupper($method),
            'http_status' => $status,
            'reason' => $reason->value,
            'message' => $message,
        ], static fn ($value) => $value !== null));

        return DataSpasialResult::unavailable($reason, $status, $message);
    }

    protected function isTimeout(ConnectionException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'timed out')
            || str_contains(strtolower($exception->getMessage()), 'timeout');
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function normalizeQuery(array $query): string
    {
        return (string) json_encode($query);
    }

    // -----------------------------------------------------------------------
    // Operasi baca (FR-002)
    // -----------------------------------------------------------------------

    /**
     * Daftar sektor DataSpasial.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listSektor(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'sektor', $filters);
    }

    /**
     * Daftar versi skema untuk SATU sektor.
     *
     * ⚠️ VERIFIKASI LANGSUNG KE API NYATA (spec Q8 / kontrak §6a.4 K1):
     * parameter bernama **`sektor`** dan **WAJIB** — `GET /dataspasial/skema`
     * tanpa parameter itu membalas `422 {"errors":{"sektor":[...]}}`.
     * Nilai yang diterima berupa id numerik (`4234`) maupun kode (`SK0`).
     * Parameter `sektor_id` yang tertera pada draft `openapi-fondasi.yaml`
     * **tidak dikenali** oleh API dan menghasilkan 422.
     *
     * ⚠️ (K2) Respons sudah memuat `attributes.isian[]` LENGKAP dengan
     * `pemetaan[]`, jadi TIDAK ada endpoint detail terpisah.
     *
     * @param  array<string, mixed>  $filters  Wajib menyertakan `sektor`.
     */
    public function listSkema(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'skema', $filters);
    }

    /**
     * Daftar **seluruh versi** skema milik satu sektor (riwayat versi).
     *
     * ⚠️ VERIFIKASI LANGSUNG + pembacaan kode API (spec Q9): endpoint
     * `GET /dataspasial/skema?sektor={id}` **hanya mengembalikan SATU versi**
     * (yang sedang aktif), sehingga tidak dapat dipakai sebagai sumber daftar
     * skema. Endpoint ini — `GET /dataspasial/skema/riwayat?sektor={id}` —
     * mengembalikan seluruh versi sektor tersebut (nomor versi, status, catatan
     * perubahan, waktu terbit).
     *
     * Parameternya berupa **kode sektor** (bukan id), sama seperti
     * `sektor()` pada API.
     *
     * @param  array<string, mixed>  $filters  Wajib menyertakan `sektor`.
     */
    public function riwayatSkema(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'skema/riwayat', $filters);
    }

    /**
     * Detail satu versi skema beserta isiannya.
     *
     * @deprecated Endpoint `GET /dataspasial/skema/{id}` **tidak ada** di API
     *             nyata (mengembalikan 404). Gunakan `listSkema(['sektor' => …])`
     *             yang sudah menyertakan isian. Dipertahankan sementara agar
     *             pemanggilan lama tidak langsung rusak saat transisi.
     */
    public function detailSkema(int $id): DataSpasialResult
    {
        Log::warning('DataSpasial: detailSkema() dipanggil padahal endpoint detail tidak ada', [
            'skema_id' => $id,
        ]);

        return $this->request('GET', 'skema/'.$id);
    }

    /**
     * Daftar isian skema.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listIsian(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'isian', $filters);
    }

    /**
     * Daftar pemetaan skema ke sasaran wilayah.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listPemetaan(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'pemetaan', $filters);
    }

    /**
     * Daftar fasilitas.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listFasilitas(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'fasilitas', $filters);
    }

    /**
     * Daftar nilai isian.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listNilai(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'nilai', $filters);
    }

    /**
     * Ringkasan agregat untuk dasbor.
     *
     * @param  array<string, mixed>  $filters
     */
    public function ringkasan(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'ringkasan', $filters);
    }

    /**
     * FeatureCollection GeoJSON gabungan untuk Peta Gabungan.
     *
     * @param  array<string, mixed>  $filters
     */
    public function geojsonGabungan(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'geojson-gabungan', $filters);
    }

    /**
     * Status adopsi skema per desa.
     *
     * @param  array<string, mixed>  $filters
     */
    public function adopsi(array $filters = []): DataSpasialResult
    {
        return $this->request('GET', 'adopsi', $filters);
    }

    // -----------------------------------------------------------------------
    // Operasi tulis
    // -----------------------------------------------------------------------

    /**
     * Buat atau perbarui draf skema beserta isiannya.
     *
     * @param  array<string, mixed>  $body
     */
    public function buatDrafSkema(array $body, ?int $schemaVersion = null): DataSpasialResult
    {
        return $this->request('POST', 'skema', [], $body, $schemaVersion);
    }

    /**
     * Terbitkan draf skema menjadi versi `terbit` yang immutable.
     *
     * @param  array<string, mixed>  $body
     */
    public function terbitkanSkema(string $sektor, array $body, ?int $schemaVersion = null): DataSpasialResult
    {
        return $this->request(
            'POST',
            'skema/'.rawurlencode($sektor).'/terbit',
            [],
            $body,
            $schemaVersion
        );
    }

    /**
     * Pratinjau dampak pra-terbit (jumlah data terdampak + daftar desa bentrok).
     *
     * ⚠️ **UTANG KONTRAK U10** — endpoint ini **belum ada** di API Satu Data
     * (verifikasi 2026-10-16 terhadap `routes/apiv1.php`). Bentuk yang diminta
     * ada di `specs/005-penerbitan-adopsi-skema/contracts/openapi-fragment-pra-terbit.yaml`.
     *
     * Sampai tersedia, `404`/`405` dikembalikan sebagai `unavailable` sehingga
     * pemanggil menampilkan "belum dapat dihitung" — **tidak pernah** mengarang angka
     * (FR-043/FR-071).
     */
    public function praTerbit(string $sektor, ?int $nomorVersi = null): DataSpasialResult
    {
        return $this->request(
            'GET',
            'skema/'.rawurlencode($sektor).'/pra-terbit',
            array_filter(['nomor_versi' => $nomorVersi], static fn ($v): bool => $v !== null)
        );
    }

    /**
     * Impor nilai isian dalam jumlah besar untuk satu desa dan periode.
     *
     * @param  array<string, mixed>  $body
     */
    public function imporNilai(array $body, ?int $schemaVersion = null): DataSpasialResult
    {
        return $this->request('POST', 'impor', [], $body, $schemaVersion);
    }
}
