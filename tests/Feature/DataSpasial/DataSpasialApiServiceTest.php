<?php

declare(strict_types=1);

use App\Enums\DataSpasialReason;
use App\Models\Setting;
use App\Models\User;
use App\Services\DataSpasialApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * FR-017: service harus dapat diuji tanpa memanggil API nyata.
 * Cakupan wajib: sukses, kosong, 503, timeout — ditambah header audit,
 * pemetaan error, konfigurasi tidak lengkap, dan cache.
 */
beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.timeout' => 10,
        'dataspasial.cache_ttl' => 300,
    ]);

    Cache::flush();

    // Token mengikuti pengaturan database gabungan (R1).
    Setting::updateOrCreate(
        ['key' => 'database_gabungan_api_key'],
        ['name' => 'API Key', 'value' => 'token-uji']
    );
});

function service(): DataSpasialApiService
{
    return app(DataSpasialApiService::class);
}

/**
 * @return array<string, mixed>
 */
function successPayload(array $items, ?int $total = null): array
{
    return [
        'data' => $items,
        'meta' => ['pagination' => [
            'total' => $total ?? count($items),
            'count' => count($items),
            'per_page' => 10,
            'current_page' => 1,
            'total_pages' => 1,
        ]],
        'links' => ['self' => 'https://api.example.test/api/v1/dataspasial/sektor'],
    ];
}

/**
 * @return array<string, mixed>
 */
function sektorItem(string $id = '1', string $kode = 'pendidikan'): array
{
    return [
        'type' => 'ds_sektor',
        'id' => $id,
        'attributes' => ['kode' => $kode, 'nama' => ucfirst($kode), 'status' => 'aktif'],
    ];
}

// ---------------------------------------------------------------------------
// T010 — Respons sukses
// ---------------------------------------------------------------------------

it('mengembalikan data beserta meta dari respons sukses', function () {
    Http::fake([
        '*/api/v1/dataspasial/sektor*' => Http::response(successPayload([sektorItem()]), 200),
    ]);

    $result = service()->listSektor();

    expect($result->isData())->toBeTrue()
        ->and($result->status->value)->toBe('data')
        ->and($result->reason)->toBeNull()
        ->and($result->httpStatus)->toBe(200)
        ->and($result->count())->toBe(1)
        ->and($result->data[0]['type'])->toBe('ds_sektor')
        ->and($result->data[0]['id'])->toBe('1')
        ->and($result->data[0]['attributes']['kode'])->toBe('pendidikan')
        ->and($result->pagination())->toBe([
            'total' => 1,
            'count' => 1,
            'per_page' => 10,
            'current_page' => 1,
            'total_pages' => 1,
        ]);
});

it('mengirim token dan batas waktu yang dikonfigurasi', function () {
    config(['dataspasial.timeout' => 3]);
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSektor();

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Authorization', 'Bearer token-uji')
            && $request->hasHeader('Accept', 'application/json');
    });
});

it('meneruskan filter sebagai query parameter', function () {
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listNilai(['sektor_id' => 1, 'periode' => '2025-06', 'page[size]' => 25]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.example.test/api/v1/dataspasial/nilai?sektor_id=1&periode=2025-06&page%5Bsize%5D=25');
});

// ---------------------------------------------------------------------------
// T011 — Respons kosong
// ---------------------------------------------------------------------------

it('menandai respons sukses tanpa data sebagai keadaan kosong', function () {
    Http::fake([
        '*/api/v1/dataspasial/sektor*' => Http::response(successPayload([], 0), 200),
    ]);

    $result = service()->listSektor();

    expect($result->isEmpty())->toBeTrue()
        ->and($result->status->value)->toBe('empty')
        ->and($result->isUnavailable())->toBeFalse()
        ->and($result->isData())->toBeFalse()
        ->and($result->reason)->toBeNull()
        ->and($result->data)->toBe([])
        ->and($result->count())->toBe(0);
});

// ---------------------------------------------------------------------------
// T012 — 503 modul belum terpasang
// ---------------------------------------------------------------------------

it('memetakan 503 modul belum terpasang menjadi tidak tersedia', function () {
    Http::fake([
        '*/api/v1/dataspasial/*' => Http::response([
            'success' => false,
            'message' => 'Modul DataSpasial belum terpasang atau versinya tidak cocok',
        ], 503),
    ]);

    $result = service()->listSektor();

    expect($result->isUnavailable())->toBeTrue()
        ->and($result->status->value)->toBe('unavailable')
        ->and($result->reason)->toBeInstanceOf(DataSpasialReason::class)
        ->and($result->reason->value)->toBe('module_not_installed')
        ->and($result->httpStatus)->toBe(503)
        ->and($result->message)->toContain('belum terpasang');
});

it('menyediakan pesan generik yang tidak membocorkan pesan API', function () {
    Http::fake([
        '*/api/v1/dataspasial/*' => Http::response([
            'success' => false,
            'message' => 'Modul DataSpasial belum terpasang atau versinya tidak cocok',
        ], 503),
    ]);

    $result = service()->listSektor();

    expect($result->userMessage())->toBe('Data sementara tidak tersedia. Silakan coba lagi nanti.')
        ->and($result->userMessage())->not->toContain('belum terpasang');
});

// ---------------------------------------------------------------------------
// T013 — Timeout
// ---------------------------------------------------------------------------

it('memetakan timeout menjadi tidak tersedia tanpa melempar exception', function () {
    Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out after 10001 milliseconds'));

    $result = service()->listSektor();

    expect($result->isUnavailable())->toBeTrue()
        ->and($result->reason->value)->toBe('timeout')
        ->and($result->httpStatus)->toBe(0)
        ->and($result->data)->toBe([]);
});

it('memetakan kegagalan koneksi non-timeout secara terpisah', function () {
    Http::fake(fn () => throw new ConnectionException('cURL error 6: Could not resolve host'));

    $result = service()->listSektor();

    expect($result->isUnavailable())->toBeTrue()
        ->and($result->reason->value)->toBe('connection_failed');
});

it('menangani exception tak terduga tanpa melempar ke pemanggil', function () {
    Http::fake(fn () => throw new RuntimeException('keadaan tidak terduga'));

    $result = service()->listSektor();

    expect($result->isUnavailable())->toBeTrue()
        ->and($result->reason->value)->toBe('connection_failed');
});

// ---------------------------------------------------------------------------
// T014 — Konfigurasi tidak lengkap
// ---------------------------------------------------------------------------

it('gagal aman dan tidak mengirim permintaan saat token database gabungan kosong', function () {
    Setting::where('key', 'database_gabungan_api_key')->delete();
    Http::fake();

    $result = service()->listSektor();

    expect(service()->isConfigured())->toBeFalse()
        ->and($result->isUnavailable())->toBeTrue()
        ->and($result->reason->value)->toBe('not_configured')
        ->and($result->httpStatus)->toBe(0);

    Http::assertNothingSent();
});

it('gagal aman saat URL dasar database gabungan kosong', function () {
    config(['app.databaseGabunganUrl' => '']);
    Http::fake();

    $result = service()->listSektor();

    expect($result->isUnavailable())->toBeTrue()
        ->and($result->reason->value)->toBe('not_configured');

    Http::assertNothingSent();
});

it('mengambil token dari Setting database gabungan', function () {
    Setting::updateOrCreate(
        ['key' => 'database_gabungan_api_key'],
        ['name' => 'API Key', 'value' => 'token-dari-setting']
    );
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    $result = service()->listSektor();

    expect($result->isData())->toBeTrue();
    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer token-dari-setting'));
});

// ---------------------------------------------------------------------------
// T015 — Header identitas pemanggil (audit API-07)
// ---------------------------------------------------------------------------

it('menyertakan identitas pengguna pada setiap permintaan baca', function () {
    $user = User::first() ?? User::factory()->create();
    $user->forceFill(['name' => 'Budi Santoso'])->save();
    $this->actingAs($user, 'web');

    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSektor();

    Http::assertSent(fn (Request $request) => $request->hasHeader('X-OpenKab-User-Id', (string) $user->id)
        && $request->hasHeader('X-OpenKab-User-Name', 'Budi Santoso'));
});

it('menyertakan identitas pengguna pada setiap permintaan tulis', function () {
    $user = User::first() ?? User::factory()->create();
    $user->forceFill(['name' => 'Siti Aminah'])->save();
    $this->actingAs($user, 'web');

    Http::fake(['*' => Http::response(['type' => 'ds_skema_versi', 'id' => '3', 'attributes' => []], 201)]);

    service()->buatDrafSkema(['sektor' => 'pendidikan', 'isian' => []]);

    Http::assertSent(fn (Request $request) => $request->hasHeader('X-OpenKab-User-Id', (string) $user->id)
        && $request->hasHeader('X-OpenKab-User-Name', 'Siti Aminah'));
});

it('tetap mengirim header identitas dengan nilai kosong tanpa konteks pengguna', function () {
    auth()->logout();
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSektor();

    Http::assertSent(fn (Request $request) => $request->hasHeader('X-OpenKab-User-Id', '')
        && $request->hasHeader('X-OpenKab-User-Name', ''));
});

// ---------------------------------------------------------------------------
// T016 — Pemetaan status & error
// ---------------------------------------------------------------------------

it('memetakan 401 dan 403 sebagai unauthorized', function (int $status) {
    Http::fake(['*' => Http::response(['message' => 'ditolak'], $status)]);

    $result = service()->listSektor();

    expect($result->reason->value)->toBe('unauthorized')
        ->and($result->httpStatus)->toBe($status);
})->with([401, 403]);

it('memetakan 404, 409, dan 422 sebagai client error', function (int $status) {
    Http::fake(['*' => Http::response(['message' => 'masalah permintaan'], $status)]);

    $result = service()->listSektor();

    expect($result->reason->value)->toBe('client_error')
        ->and($result->httpStatus)->toBe($status);
})->with([404, 409, 422]);

it('memetakan 500 sebagai layanan tidak tersedia', function () {
    Http::fake(['*' => Http::response(['message' => 'server error'], 500)]);

    $result = service()->listSektor();

    expect($result->reason->value)->toBe('http_5xx')
        ->and($result->httpStatus)->toBe(500);
});

it('memetakan 400 dengan error_code versi skema tidak didukung', function () {
    Http::fake(['*' => Http::response([
        'success' => false,
        'message' => 'Versi skema 0 tidak didukung. Versi yang didukung: 1, 2, 3.',
        'error_code' => 'SchemaVersionUnsupported',
        'meta' => ['schema_version' => 0, 'supported_versions' => [1, 2, 3]],
    ], 400)]);

    $result = service()->terbitkanSkema('pendidikan', ['catatan_perubahan' => 'x'], 0);

    expect($result->reason->value)->toBe('schema_version_unsupported')
        ->and($result->httpStatus)->toBe(400);
});

it('tidak mengirim header versi skema bila tidak diminta', function () {
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSkema();

    Http::assertSent(fn (Request $request) => ! $request->hasHeader('X-Schema-Version'));
});

it('meneruskan header versi skema pada operasi tulis bila diberikan', function () {
    Http::fake(['*' => Http::response(['data' => ['type' => 'ds_skema_versi', 'id' => '3', 'attributes' => []]], 200)]);

    service()->terbitkanSkema('pendidikan', ['catatan_perubahan' => 'x'], 3);

    Http::assertSent(fn (Request $request) => $request->hasHeader('X-Schema-Version', '3'));
});

// ---------------------------------------------------------------------------
// T017 — Cache
// ---------------------------------------------------------------------------

it('tidak memanggil API dua kali untuk baca identik dalam masa cache', function () {
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    $first = service()->listSektor();
    $second = service()->listSektor();

    expect($first->isData())->toBeTrue()
        ->and($second->isData())->toBeTrue()
        ->and($second->data)->toBe($first->data);

    Http::assertSentCount(1);
});

it('memakai cache untuk keadaan kosong juga', function () {
    Http::fake(['*' => Http::response(successPayload([], 0), 200)]);

    service()->listSektor();
    $second = service()->listSektor();

    expect($second->isEmpty())->toBeTrue();
    Http::assertSentCount(1);
});

it('tidak memakai cache untuk query berbeda', function () {
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSektor(['sektor_id' => 1]);
    service()->listSektor(['sektor_id' => 2]);

    Http::assertSentCount(2);
});

it('tidak meng-cache hasil tidak tersedia', function () {
    Http::fake(['*' => Http::response(['message' => 'mati'], 503)]);

    service()->listSektor();
    service()->listSektor();

    Http::assertSentCount(2);
});

it('tidak meng-cache hasil timeout', function () {
    // Closure fake yang melempar exception tidak tercatat sebagai "sent",
    // sehingga jumlah pemanggilan dihitung lewat penghitung.
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;

        throw new ConnectionException('cURL error 28: Operation timed out');
    });

    service()->listSektor();
    service()->listSektor();

    expect($attempts)->toBe(2);
});

it('mematikan cache bila ttl bernilai nol', function () {
    config(['dataspasial.cache_ttl' => 0]);
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSektor();
    service()->listSektor();

    Http::assertSentCount(2);
});

it('membuat cache baca tidak berlaku setelah operasi tulis', function (string $method) {
    Http::fake(['*' => Http::response(successPayload([sektorItem()]), 200)]);

    service()->listSektor();
    Http::assertSentCount(1);

    match ($method) {
        'buatDrafSkema' => service()->buatDrafSkema(['sektor' => 'pendidikan', 'isian' => []]),
        'terbitkanSkema' => service()->terbitkanSkema('pendidikan', ['catatan_perubahan' => 'x']),
        'imporNilai' => service()->imporNilai(['kode_desa' => '5102012001', 'nomor_versi' => 3, 'periode' => '2025-06', 'fasilitas' => []]),
    };

    Http::assertSentCount(2);

    // Cache lama tidak boleh dipakai lagi -> request ketiga benar-benar dikirim.
    service()->listSektor();

    Http::assertSentCount(3);
})->with(['buatDrafSkema', 'terbitkanSkema', 'imporNilai']);

it('tidak meng-invalidasi cache bila operasi tulis gagal', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;

        return $attempts === 1
            ? Http::response(successPayload([sektorItem()]), 200)
            : Http::response(['message' => 'gagal'], 500);
    });

    service()->listSektor();
    $write = service()->terbitkanSkema('pendidikan', ['catatan_perubahan' => 'x']);

    expect($write->isUnavailable())->toBeTrue();

    // Cache baca hasil kondisi sukses sebelumnya masih berlaku.
    $third = service()->listSektor();

    expect($third->isData())->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('memakai kunci cache yang berbeda untuk endpoint berbeda', function () {
    $service = service();

    expect($service->cacheKey('/sektor'))->not->toBe($service->cacheKey('/nilai'))
        ->and($service->cacheKey('/sektor', ['a' => 1]))->not->toBe($service->cacheKey('/sektor', ['a' => 2]))
        ->and($service->cacheKey('/sektor', ['a' => 1, 'b' => 2]))
        ->toBe($service->cacheKey('/sektor', ['b' => 2, 'a' => 1]));
});
