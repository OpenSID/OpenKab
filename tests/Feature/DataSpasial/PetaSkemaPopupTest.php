<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

beforeEach(function () {
    config(['app.databaseGabunganUrl' => 'https://api.example.test', 'dataspasial.cache_ttl' => 300]);
    Cache::flush();
    Setting::updateOrCreate(['key' => 'database_gabungan_api_key'], ['value' => 'token-uji']);
    Setting::updateOrCreate(['key' => 'website_enable'], ['value' => '1']);
    Setting::updateOrCreate(['key' => 'home_page'], ['value' => 'presisi']);
});

function fakePopupSkemaApi(): void
{
    $features = [];
    foreach ([
        ['SK0', 'Infrastruktur', 100, 'Jalan', [
            ['skema_versi_id' => 102, 'isian_kode' => 'jumlah', 'nilai' => 0, 'periode' => '2025-01', 'sumber' => 'desa'],
            ['skema_versi_id' => 102, 'isian_kode' => 'jumlah', 'nilai' => 12, 'periode' => '2024-01', 'sumber' => 'impor'],
            ['skema_versi_id' => 102, 'isian_kode' => 'rahasia', 'nilai' => 'RAHASIA', 'periode' => '2025-01'],
            ['skema_versi_id' => 102, 'isian_kode' => 'rahasia', 'nilai' => 'RAHASIA_LAMA', 'periode' => '2023-01'],
            ['skema_versi_id' => 101, 'isian_kode' => 'jumlah', 'nilai' => 7, 'periode' => '2024-01'],
            ['skema_versi_id' => 103, 'isian_kode' => 'jumlah', 'nilai' => 'DRAF', 'periode' => '2026-01'],
        ]],
        ['SK1', 'Pendidikan', 100, 'Sekolah', [
            ['skema_versi_id' => 201, 'isian_kode' => 'jumlah', 'nilai' => 20, 'periode' => '2025-01'],
        ]],
        ['SK1', 'Pendidikan', 101, 'Sekolah lain', [
            ['skema_versi_id' => 201, 'isian_kode' => 'jumlah', 'nilai' => 50, 'periode' => '2025-01'],
        ]],
    ] as [$kode, $nama, $lokasi, $fasilitas, $nilai]) {
        $features[] = [
            'type' => 'Feature',
            'geometry' => ['type' => 'Point', 'coordinates' => [115.2, -8.65]],
            'properties' => [
                'id' => $fasilitas,
                'lokasi_id' => $lokasi,
                'nama' => $fasilitas,
                'sektor_kode' => $kode,
                'sektor_nama' => $nama,
                'dataspasial' => $nilai,
            ],
        ];
    }

    Http::fake([
        '*/api/v1/plan/get-list-coordinate*' => Http::response([['id' => 100]]),
        '*/api/v1/dataspasial/geojson*' => function ($request) use ($features) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $filtered = array_values(array_filter($features, static fn ($feature): bool => ! isset($query['sektor'])
                || $feature['properties']['sektor_kode'] === $query['sektor']));
            if (isset($query['periode'])) {
                foreach ($filtered as &$feature) {
                    $feature['properties']['dataspasial'] = array_values(array_filter(
                        $feature['properties']['dataspasial'],
                        static fn ($entry): bool => ($entry['periode'] ?? null) === $query['periode']
                    ));
                }
                unset($feature);
            }

            return Http::response(['type' => 'FeatureCollection', 'features' => $filtered]);
        },
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi([
            fxRiwayatVersi('103', 1, 3, 'draf'),
            fxRiwayatVersi('102', 1, 2, 'terbit'),
            fxRiwayatVersi('101', 1, 1, 'terbit'),
        ])),
        '*/api/v1/dataspasial/skema*' => function ($request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $pendidikan = ($query['sektor'] ?? '') === 'SK1';
            $lama = (int) ($query['versi'] ?? 0) === 1;
            $id = $pendidikan ? '201' : ($lama ? '101' : '102');
            $versi = $pendidikan || $lama ? 1 : 2;
            $isian = [
                fxIsian('jumlah', ['label' => 'Jumlah', 'tipe' => 'angka', 'satuan' => 'unit', 'tampil_popup' => true]),
                fxIsian('rahasia', ['label' => 'Tidak publik', 'tampil_popup' => false]),
            ];

            return Http::response(fakeKoleksi([
                fxSkemaAktif($id, $pendidikan ? 2 : 1, $pendidikan ? 'SK1' : 'SK0', $pendidikan ? 'Pendidikan' : 'Infrastruktur', $versi, 'terbit', $isian),
            ]));
        },
        '*' => Http::response(['data' => []]),
    ]);
}

it('memuat aset popup skema pada peta presisi', function () {
    fakePopupSkemaApi();

    $this->get(route('presisi.geo-spasial'))->assertOk()
        ->assertSee('js/dataspasial/popup-skema.js', false)
        ->assertSee('css/dataspasial/popup-skema.css', false)
        ->assertSee(route('presisi.geo-spasial.skema'), false);
});

it('menyediakan hanya isian popup versi terbit per lokasi pada halaman presisi publik', function () {
    fakePopupSkemaApi();

    $response = $this->getJson(route('presisi.geo-spasial.skema'))->assertOk();
    $skema = $response->json('lokasi.100.skema');

    expect($skema)->toHaveCount(3)
        ->and(array_column($skema, 'sektor'))->toBe(['Infrastruktur', 'Infrastruktur', 'Pendidikan'])
        ->and($skema[0]['isian'][0]['nilai'])->toBe(0)
        ->and($skema[1]['isian'][0]['nilai'])->toBe(7)
        ->and($skema[2]['isian'][0]['nilai'])->toBe(20)
        ->and($response->json('lokasi.101'))->toBeNull();
    $response->assertDontSee('RAHASIA')->assertDontSee('DRAF')->assertDontSee('Tidak publik');
    expect($skema[0]['periode'])->toBe(['2025-01', '2024-01'])
        ->and($skema[0]['periode_aktif'])->toBe('2025-01')
        ->and(array_column($skema[0]['isian'][0]['nilai_periode'], 'nilai', 'periode'))
        ->toBe(['2025-01' => 0, '2024-01' => 12]);
});

it('memasang rincian skema yang sama pada data peta gabungan', function () {
    fakePopupSkemaApi();
    $this->actingAs(makeRole('popup peta test', ['dataspasial-peta-read']));

    $response = $this->getJson(route('dataspasial.peta.data'))->assertOk();
    expect($response->json('features.0.properties.popup_skema'))->toHaveCount(3)
        ->and($response->json('features.1.properties.popup_skema'))->toHaveCount(3)
        ->and($response->json('features.2.properties.popup_skema'))->toHaveCount(1)
        ->and($response->json('features.0.properties.popup_skema.0.isian.0.nilai'))->toBe(0);
});

it('memuat semua skema lokasi pada peta dasbor yang difilter sektor', function () {
    fakePopupSkemaApi();
    $this->actingAs(makeRole('popup dasbor test', ['dataspasial-dasbor-read']));

    $this->getJson(route('dataspasial.peta.data'))->assertForbidden();
    $response = $this->getJson(route('dataspasial.dasbor.peta.data', ['sektor' => 'SK0']))->assertOk();
    $response->assertJsonCount(1, 'features');
    expect($response->json('features.0.properties.popup_skema'))->toHaveCount(3);
});

it('menyediakan seluruh periode popup meski peta difilter periode', function (string $route, string $izin, ?string $sektor) {
    fakePopupSkemaApi();
    $this->actingAs(makeRole('popup periode '.$izin, [$izin]));

    $filters = ['periode' => '2024-01'];
    if ($sektor !== null) {
        $filters['sektor'] = $sektor;
    }
    $response = $this->getJson(route($route, $filters))->assertOk();
    $skema = $response->json('features.0.properties.popup_skema.0');
    expect($skema['periode'])->toBe(['2025-01', '2024-01'])
        ->and($skema['periode_aktif'])->toBe('2024-01')
        ->and($skema['isian'][0]['nilai'])->toBe(12);
})->with([
    ['dataspasial.peta.data', 'dataspasial-peta-read', 'SK0'],
    ['dataspasial.dasbor.peta.data', 'dataspasial-dasbor-read', 'SK0'],
    ['dataspasial.peta.data', 'dataspasial-peta-read', null],
]);

it('mengambil riwayat periode fasilitas tanpa lokasi_id berdasarkan identitas fasilitas', function () {
    $this->actingAs(makeRole('popup fasilitas tanpa lokasi', ['dataspasial-peta-read']));
    $entries = [
        ['skema_versi_id' => 102, 'isian_kode' => 'jumlah', 'nilai' => 1, 'periode' => '2024'],
        ['skema_versi_id' => 102, 'isian_kode' => 'jumlah', 'nilai' => 2, 'periode' => '2025'],
    ];
    Http::fake([
        '*/api/v1/dataspasial/geojson*' => function ($request) use ($entries) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $values = isset($query['periode'])
                ? array_values(array_filter($entries, fn ($entry) => $entry['periode'] === $query['periode']))
                : $entries;

            return Http::response(['type' => 'FeatureCollection', 'features' => [[
                'properties' => ['id' => 77, 'lokasi_id' => null, 'sektor_kode' => 'SK0', 'dataspasial' => $values],
            ]]]);
        },
        '*/api/v1/dataspasial/skema*' => Http::response(fakeKoleksi([
            fxSkemaAktif('102', 1, 'SK0', 'Infrastruktur', 2, 'terbit', [fxIsian('jumlah', ['tampil_popup' => true])]),
        ])),
        '*' => Http::response(['data' => []]),
    ]);

    $response = $this->getJson(route('dataspasial.peta.data', ['periode' => '2024']))->assertOk();
    expect($response->json('features.0.properties.popup_skema.0.periode'))->toBe(['2025', '2024'])
        ->and($response->json('features.0.properties.popup_skema.0.isian.0.nilai'))->toBe(1);
});

it('tidak membocorkan isian draf atau isian tanpa versi terverifikasi', function () {
    Http::fake([
        '*/api/v1/plan/get-list-coordinate*' => Http::response([['id' => 100]]),
        '*/api/v1/dataspasial/geojson*' => Http::response([
            'type' => 'FeatureCollection',
            'features' => [[
                'properties' => [
                    'lokasi_id' => 100, 'sektor_kode' => 'SK0', 'nama' => 'Lokasi',
                    'dataspasial' => [
                        ['skema_versi_id' => 103, 'isian_kode' => 'jumlah', 'nilai' => 'DRAF'],
                        ['isian_kode' => 'jumlah', 'nilai' => 'TANPA_VERSI'],
                    ],
                ],
            ]],
        ]),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi([fxRiwayatVersi('103', 1, 3, 'draf')])),
        '*/api/v1/dataspasial/skema*' => Http::response(fakeKoleksi([
            fxSkemaAktif('103', 1, 'SK0', 'Infrastruktur', 3, 'draf', [
                fxIsian('jumlah', ['tampil_popup' => true]),
            ]),
        ])),
        '*' => Http::response(['data' => []]),
    ]);

    $response = $this->getJson(route('presisi.geo-spasial.skema'))->assertOk();
    expect($response->json('lokasi.100.skema'))->toBe([]);
    $response->assertDontSee('DRAF')->assertDontSee('TANPA_VERSI');
});

it('menampilkan status gagal saat API peta tidak tersedia', function () {
    Http::fake(['*/api/v1/dataspasial/geojson*' => Http::response(['message' => 'unavailable'], 503)]);

    $this->getJson(route('presisi.geo-spasial.skema'))->assertStatus(503)
        ->assertJsonStructure(['message']);
});

it('tidak mengembalikan rincian publik saat daftar lokasi peta tidak tersedia', function () {
    Http::fake([
        '*/api/v1/dataspasial/geojson*' => Http::response(['type' => 'FeatureCollection', 'features' => []]),
        '*/api/v1/plan/get-list-coordinate*' => Http::response(['message' => 'unavailable'], 503),
    ]);

    $this->getJson(route('presisi.geo-spasial.skema'))->assertStatus(503)
        ->assertJsonMissingPath('lokasi');
});
