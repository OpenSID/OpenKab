<?php

declare(strict_types=1);
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Fixture bersama untuk uji editor skema kabupaten (KAB-02).
 *
 * ⚠️ SEMUA bentuk di sini adalah **SALINAN PERSIS** dari respons instance API
 * Satu Data yang hidup (repo `API-Database-Gabungan`), diverifikasi 2026-10-15.
 * `docs/spasial/openapi-fondasi.yaml` v0.1.0 adalah draft dan **tidak cocok**
 * dengan implementasi — lihat `specs/004-pengelola-skema-editor/contracts/skema-editor-api.md` §6a.
 *
 *Empat Koreksi yang terkunci di sini:
 *   K1 param `sektor` (WAJIB, bukan `sektor_id`)
 *   K2 tidak ada endpoint detail; isian ikut pada respons versi aktif
 *   K3 versi/status di `attributes.versi_skema`
 *   K4 nama resmi penanda: `tampil_popup` / `tampil_filter` / `tampil_indikator`
 *   Q9 daftar versi dari `/skema/riwayat` (endpoint versi aktif hanya 1 versi)
 *
 * Berkas ini bukan test (tidak berakhiran `Test.php`) sehingga tidak diambil
 * oleh runner.
 */

/**
 * Envelope koleksi untuk daftar sektor.
 *
 * @param  array<int, array<string, mixed>>  $items
 * @return array<string, mixed>
 */
if (! function_exists('fakeKoleksi')) {
    function fakeKoleksi(array $items): array
    {
        return [
            'data' => $items,
            'meta' => ['pagination' => [
                'total' => count($items),
                'count' => count($items),
                'per_page' => 200,
                'current_page' => 1,
                'total_pages' => 1,
            ]],
            'links' => [],
        ];
    }

    /**
     * Item sektor — bentuk nyata `GET /dataspasial/sektor`.
     *
     * @return array<string, mixed>
     */
    function fxSektor(string $id, string $kode, string $nama, int $jumlahVersi = 2, int $jumlahTerbit = 1): array
    {
        return [
            'type' => 'daftar sektor',
            'id' => $id,
            'attributes' => [
                'kode' => $kode,
                'nama' => $nama,
                'ikon' => null,
                'deskripsi' => null,
                'urutan' => 0,
                'status' => 'aktif',
                'jumlah_versi_skema' => $jumlahVersi,
                'jumlah_versi_terbit' => $jumlahTerbit,
            ],
        ];
    }

    /**
     * Entri riwayat versi — bentuk nyata `GET /dataspasial/skema/riwayat` (Q9).
     *
     * @return array<string, mixed>
     */
    function fxRiwayatVersi(string $id, int $sektorId, int $nomorVersi, string $status): array
    {
        return [
            'type' => 'riwayat versi skema',
            'id' => $id,
            'attributes' => [
                'sektor_id' => $sektorId,
                'nomor_versi' => $nomorVersi,
                'status' => $status,
                'catatan_perubahan' => 'Draf uji.',
                'diterbitkan_pada' => $status === 'terbit' ? '2025-10-01T09:00:00+07:00' : null,
                'diterbitkan_oleh' => $status === 'terbit' ? 'admin@opengkab.go.id' : null,
            ],
        ];
    }

    /**
     * Versi aktif beserta isian — bentuk nyata `GET /dataspasial/skema?sektor=`
     * (K2: isian ikut di sini; K3: versi bersarang di `versi_skema`).
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<string, mixed>
     */
    function fxSkemaAktif(
        string $id,
        int $sektorId,
        string $sektorKode,
        string $sektorNama,
        int $nomorVersi,
        string $status,
        array $isian = []
    ): array {
        return [
            'type' => 'skema data spasial',
            'id' => $id,
            'attributes' => [
                'sektor' => ['id' => $sektorId, 'kode' => $sektorKode, 'nama' => $sektorNama],
                'versi_skema' => [
                    'id' => (int) $id,
                    'sektor_id' => $sektorId,
                    'nomor_versi' => $nomorVersi,
                    'status' => $status,
                    'catatan_perubahan' => 'Draf uji.',
                    'diterbitkan_pada' => $status === 'terbit' ? '2025-10-01T09:00:00+07:00' : null,
                    'diterbitkan_oleh' => null,
                ],
                'isian' => $isian,
            ],
        ];
    }

    /**
     * Item isian — bentuk nyata di dalam `attributes.isian[]`.
     *
     * Penanda memakai NAMA RESMI API (K4). `pemetaan` adalah ARRAY (Q9/§6a.3).
     *
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    function fxIsian(string $kode, array $ubah = []): array
    {
        return array_merge([
            'id' => null,
            'kode' => $kode,
            'label' => ucfirst(str_replace('_', ' ', $kode)),
            'tipe' => 'teks',
            'wajib' => false,
            'satuan' => null,
            'opsi' => null,
            'aturan_validasi' => null,
            'deskripsi' => null,
            'urutan' => 1,
            'diarsipkan' => false,
            'tampil_popup' => false,
            'tampil_filter' => false,
            'tampil_indikator' => false,
            'pemetaan' => [],
        ], $ubah);
    }

    /**
     * Suffix HTTP muat halaman dari layout adminlte (MenuListener).
     *
     * @return array<string, mixed>
     */
    function fakeConfigEndpoint(): array
    {
        return ['data' => []];
    }

    /**
     * Palsukan seluruh endpoint yang dipakai halaman editor skema.
     *
     * @param  array<int, array<string, mixed>>  $sektor
     * @param  array<int, array<string, mixed>>  $riwayat
     * @param  array<string, mixed>|null  $skemaAktif
     */
    function fakeEndpointEditor(array $sektor, array $riwayat = [], ?array $skemaAktif = null): void
    {
        Http::fake([
            '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi($sektor), 200),
            // Q9: daftar seluruh versi.
            '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi($riwayat), 200),
            // K1/K2: versi aktif + isian.
            '*/api/v1/dataspasial/skema?*' => Http::response($skemaAktif ?? [], 200),
            '*' => Http::response(['data' => []], 200),
        ]);
    }

    /**
     * Buat peran + pengguna uji pada sebuah team.
     *
     * ⚠️ Didefinisikan **satu kali** di sini (bukan di tiap berkas test) karena
     * PHP men deadly-declare fungsi global — menyalinnya ke beberapa test akan
     * fatal "Cannot redeclare makeRole()" saat seluruh suite dijalankan.
     *
     * ⚠️ `setPermissionsTeamId()` bersifat global per request, jadi peran yang
     * dibuat TERAKHIR harusLAH pengguna yang sedang login. Pemanggil yang membuat
     * peran lain untuk kasus negatif harusUCAH pemanggil ini setelahnya.
     *
     * @param  array<int, string>  $permissions
     */
    function makeRole(string $teamName, array $permissions): User
    {
        $team = Team::firstOrCreate(['name' => $teamName], ['menu' => []]);

        setPermissionsTeamId($team->id);

        $role = Role::firstOrCreate([
            'name' => $teamName,
            'team_id' => $team->id,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(array_map(
            fn (string $nama) => Permission::findOrCreate($nama, 'web'),
            $permissions
        ));

        $user = User::factory()->create();
        $user->forceFill(['name' => 'Uji '.$teamName])->save();
        $user->teams()->attach($team->id, ['id_user' => $user->id, 'id_team' => $team->id]);
        $user->unsetRelation('team')->load('team');
        $user->assignRole($role);

        return $user;
    }

    // ---------------------------------------------------------------------------
    // Fixture KAB-03 — Penerbitan, riwayat versi & adopsi
    //
    // ⚠️ SEMUA bentuk di bawah adalah SALINAN PERSIS dari instance API Satu Data
    // yang hidup (repo `API-Database-Gabungan`, commit ca48b96), diverifikasi
    // 2026-10-16 — lihat `specs/005-penerbitan-adopsi-skema/contracts/penerbitan-adopsi-api.md`.
    //
    // Koreksi yang terkunci di sini (jangan diubah tanpa verifikasi ulang):
    //   K1 adopsi memakai `nomor_versi_skema` (BUKAN `nomor_versi`)
    //   K2 adopsi memakai `status` (BUKAN `status_adopsi`) dengan hanya dua nilai:
    //      `memakai_skema` dan `belum_ada_data` — "tertinggal" DITURUNKAN OpenKab
    //   K3 adopsi memakai `terakhir_ada_data` (BUKAN `diadopsi_pada`)
    //   K4 `GET /dataspasial/skema/{id}` tidak ada; isian per versi diambil lewat
    //      `?sektor={kode}&versi={nomor}&termasuk_arsip=1`
    //   K5 status versi punya tiga nilai: `draf`, `terbit`, `usang`
    // ---------------------------------------------------------------------------

    /**
     * Riwayat dua versi sektor SK0: v3 `terbit` + v1 `draf` (verifikasi nyata).
     *
     * @return array<int, array<string, mixed>>
     */
    function fxRiwayatDuaVersi(): array
    {
        return [
            fxRiwayatVersi('8143', 4234, 3, 'terbit'),
            fxRiwayatVersi('8142', 4234, 1, 'draf'),
        ];
    }

    /**
     * Riwayat dengan versi terbit lama yang sudah di-downgrade menjadi `usang`.
     *
     * @return array<int, array<string, mixed>>
     */
    function fxRiwayatDenganUsang(): array
    {
        return [
            [
                'type' => 'riwayat versi skema',
                'id' => '8143',
                'attributes' => [
                    'sektor_id' => 4234,
                    'nomor_versi' => 4,
                    'status' => 'terbit',
                    'catatan_perubahan' => 'Tambah isian guru.',
                    'diterbitkan_pada' => '2025-11-05T09:00:00.000000Z',
                    'diterbitkan_oleh' => 'Budi Santoso',
                ],
            ],
            [
                'type' => 'riwayat versi skema',
                'id' => '8143',
                'attributes' => [
                    'sektor_id' => 4234,
                    'nomor_versi' => 3,
                    'status' => 'usang',
                    'catatan_perubahan' => null,
                    'diterbitkan_pada' => '2025-10-05T14:00:00.000000Z',
                    'diterbitkan_oleh' => 'Budi Santoso',
                ],
            ],
        ];
    }

    /**
     * Satu versi skema (v3 sektor SK0) BESERTA isian terarsip — hasil
     * `GET /dataspasial/skema?sektor=SK0&versi=3&termasuk_arsip=1`.
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<string, mixed>
     */
    function fxSkemaVersiDenganArsip(array $isian = []): array
    {
        return [
            'data' => [
                'type' => 'skema data spasial',
                'id' => '8143',
                'attributes' => [
                    'sektor' => ['id' => 4234, 'kode' => 'SK0', 'nama' => 'Sektor 0'],
                    'versi_skema' => [
                        'id' => 8143,
                        'sektor_id' => 4234,
                        'nomor_versi' => 3,
                        'status' => 'terbit',
                        'catatan_perubahan' => 'Versi terbit',
                        'diterbitkan_pada' => '2025-10-05T14:00:00.000000Z',
                        'diterbitkan_oleh' => 'Budi Santoso',
                    ],
                    'isian' => $isian !== [] ? $isian : [
                        [
                            'id' => 8484,
                            'kode' => 'lebar_jalan',
                            'label' => 'Lebar Jalan',
                            'tipe' => 'angka',
                            'wajib' => true,
                            'satuan' => 'meter',
                            'opsi' => null,
                            'aturan_validasi' => null,
                            'deskripsi' => null,
                            'urutan' => 1,
                            'diarsipkan' => false,
                            'tampil_popup' => true,
                            'tampil_filter' => false,
                            'tampil_indikator' => false,
                            'pemetaan' => [
                                ['id' => 3951, 'sumber' => 'openSID', 'rujukan_sumber' => 'tbl_jalan.lebar', 'aturan_hitung' => null],
                            ],
                        ],
                        [
                            'id' => 8485,
                            'kode' => 'arsip_lama',
                            'label' => 'Arsip Lama',
                            'tipe' => 'teks',
                            'wajib' => false,
                            'satuan' => 'meter',
                            'opsi' => null,
                            'aturan_validasi' => null,
                            'deskripsi' => null,
                            'urutan' => 1,
                            'diarsipkan' => true,
                            'tampil_popup' => true,
                            'tampil_filter' => false,
                            'tampil_indikator' => false,
                            'pemetaan' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Satu baris adopsi desa (nama field nyata, K1–K3).
     *
     * @return array<string, mixed>
     */
    function fxAdopsiBaris(
        int $configId = 1,
        string $kodeDesa = '5102012001',
        string $namaDesa = 'Bajera',
        ?int $nomorVersi = 3,
        ?int $sektorId = 4234,
        string $status = 'memakai_skema',
        ?string $terakhir = '2025-06-05 10:00:00'
    ): array {
        return [
            'type' => 'adopsi skema desa',
            'id' => (string) $configId,
            'attributes' => [
                'config_id' => $configId,
                'kode_desa' => $kodeDesa,
                'nama_desa' => $namaDesa,
                'skema_versi_id' => $nomorVersi === null ? null : 9000 + $nomorVersi,
                'nomor_versi_skema' => $nomorVersi,
                'sektor_id' => $sektorId,
                'status' => $status,
                'terakhir_ada_data' => $terakhir,
            ],
        ];
    }

    /**
     * Koleksi adopsi dengan `meta.pagination` (contoh nyata: 133 desa).
     *
     * @param  array<int, array<string, mixed>>|null  $baris
     * @return array<string, mixed>
     */
    function fxAdopsiKoleksi(?array $baris = null): array
    {
        if ($baris === null) {
            $baris = [];

            // 3 desa memakai skema (sebenarnya: Bajera v3 sektor 4234, Bantas v3 sektor 4235).
            $baris[] = fxAdopsiBaris(1, '5102012001', 'Bajera', 3, 4234);
            $baris[] = fxAdopsiBaris(2, '5102022003', 'Bantas', 3, 4235);

            // Sisanya belum pernah menarik skema.
            for ($i = 3; $i <= 133; $i++) {
                $baris[] = fxAdopsiBaris(
                    $i,
                    '35250'.$i,
                    'Desa '.$i,
                    null,
                    null,
                    'belum_ada_data',
                    null
                );
            }
        }

        return [
            'data' => $baris,
            'meta' => [
                'pagination' => [
                    'total' => count($baris),
                    'count' => count($baris),
                    'per_page' => count($baris),
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
        ];
    }

    /**
     * Body penolakan penerbitan saat tidak ada draf aktif (terverifikasi nyata).
     *
     * @return array<string, mixed>
     */
    function fxTerbitGagal409(): array
    {
        return [
            'success' => false,
            'message' => 'Tidak ada draf aktif untuk sektor kesehatan.',
        ];
    }

    /**
     * Pengguna uji dengan izin tulis skema (boleh membuka layar terbitan).
     */
    function makeAdminSkema(): User
    {
        return makeRole('admin skema uji k03', [
            'dataspasial-skema-read',
            'dataspasial-skema-write',
        ]);
    }

    /**
     * Pengguna uji HANYA baca skema (tidak boleh membuka layar terbitan).
     */
    function makePenggunaBacaSaja(): User
    {
        return makeRole('pembaca skema uji k03', [
            'dataspasial-skema-read',
        ]);
    }
}

// ---------------------------------------------------------------------------
// Fixture KAB-04 — Peta Gabungan (GeoJSON API-04)
//
// Bentuk salinan respons `GET /api/v1/dataspasial/geojson`: FeatureCollection
// yang menggabungkan titik geospasial lama (OpenSID `lokasi` & Data Presisi)
// dengan titik DataSpasial (`dataspasial` tidak null), serta polygon batas desa.
// ---------------------------------------------------------------------------

/**
 * @return array<string, mixed>
 */
function fxGeoJsonGabungan(): array
{
    return [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [115.2, -8.65]],
                'properties' => [
                    'id' => 1,
                    'nama' => 'SD Negeri 1',
                    'source' => 'openkab',
                    'kategori' => 'sekolah',
                    'kode_kecamatan' => '510201',
                ],
            ],
            [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [115.3, -8.66]],
                'properties' => [
                    'id' => 2,
                    'nama' => 'Pura Dalem',
                    'source' => 'presisi',
                    'kategori' => 'tempat_ibadah',
                ],
            ],
            [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [115.4, -8.67]],
                'properties' => [
                    'id' => 3,
                    'nama' => 'Fasilitas Infrastruktur',
                    'source' => 'openkab',
                    'sektor_kode' => 'SK0',
                    'sektor_nama' => 'Infrastruktur',
                    'kode_desa' => '5102012001',
                    'kode_kecamatan' => '510201',
                    'dataspasial' => [
                        'nilai' => 12,
                        'sumber' => 'desa',
                        'periode' => '2025-06',
                        'isian_kode' => 'jumlah_unit',
                        'isian_label' => 'Jumlah Unit',
                        'isian_satuan' => 'unit',
                        'isian_tipe' => 'angka',
                        'waktu_input' => '2025-06-15T10:30:00Z',
                    ],
                ],
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[[115.1, -8.6], [115.2, -8.6], [115.2, -8.7], [115.1, -8.7], [115.1, -8.6]]],
                ],
                'properties' => ['kode_desa' => '5102012001', 'nama_desa' => 'Desa Bernilai', 'nilai' => 5],
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[[115.2, -8.6], [115.3, -8.6], [115.3, -8.7], [115.2, -8.7], [115.2, -8.6]]],
                ],
                'properties' => ['kode_desa' => '5102012002', 'nama_desa' => 'Desa Nol', 'nilai' => 0],
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[[115.3, -8.6], [115.4, -8.6], [115.4, -8.7], [115.3, -8.7], [115.3, -8.6]]],
                ],
                'properties' => ['kode_desa' => '5102012003', 'nama_desa' => 'Desa Tanpa Data'],
            ],
        ],
    ];
}

/**
 * Skema sektor SK0 dengan isian bertanda filter & popup.
 *
 * @return array<string, mixed>
 */
function fxSkemaIsianFilter(): array
{
    $isian = [
        fxIsian('jumlah_unit', ['label' => 'Jumlah Unit', 'tipe' => 'angka', 'satuan' => 'unit', 'tampil_popup' => true]),
        fxIsian('jenis_unit', ['label' => 'Jenis Unit', 'tipe' => 'pilihan', 'opsi' => ['A', 'B'], 'tampil_filter' => true]),
        fxIsian('catatan', ['label' => 'Catatan', 'tipe' => 'teks']),
    ];

    return fakeKoleksi([
        fxSkemaAktif('1', 1, 'SK0', 'Infrastruktur', 1, 'terbit', $isian),
    ]);
}

function fxRingkasanIndikator(string $fieldKeterangan = 'keterangan'): array
{
    return [
        'data' => [
            [
                'type' => 'ds_ringkasan',
                'id' => '1',
                'attributes' => [
                    'sektor' => 'SK0',
                    'periode' => '2025-06',
                    'isian_kode' => 'jumlah_unit',
                    'isian_label' => 'Jumlah Unit',
                    'isian_satuan' => 'unit',
                    'ukuran' => 'total',
                    'total' => 120,
                ],
            ],
            [
                'type' => 'ds_ringkasan',
                'id' => '2',
                'attributes' => [
                    'sektor' => 'SK0',
                    'periode' => '2025-06',
                    'isian_kode' => 'jumlah_penduduk',
                    'isian_label' => 'Jumlah Penduduk',
                    'isian_satuan' => 'jiwa',
                    'ukuran' => 'total',
                    'total' => 5000,
                    // Q3: baris keterangan opsional dari ringkasan (item pertama sengaja tanpa)
                    $fieldKeterangan => 'L 30.112 · P 28.828',
                ],
            ],
        ],
        'meta' => [
            'pagination' => [
                'total' => 2,
                'count' => 2,
                'per_page' => 10,
                'current_page' => 1,
                'total_pages' => 1,
            ],
        ],
    ];
}

function fxSkemaIsianIndikator(): array
{
    return [
        'data' => [
            [
                'type' => 'ds_skema_versi',
                'id' => '1',
                'attributes' => [
                    'sektor_id' => 1,
                    'status' => 'terbit',
                    'isian' => [
                        ['kode' => 'jumlah_unit', 'label' => 'Jumlah Unit', 'tipe' => 'angka', 'satuan' => 'unit', 'tampil_indikator' => true, 'tampil_filter' => true],
                        ['kode' => 'jumlah_penduduk', 'label' => 'Jumlah Penduduk', 'tipe' => 'angka', 'satuan' => 'jiwa', 'tampil_indikator' => true],
                    ],
                ],
            ],
        ],
    ];
}

function fxNilaiIsian(): array
{
    return [
        'data' => [
            [
                'type' => 'ds_nilai',
                'id' => '1',
                'attributes' => ['isian_kode' => 'jumlah_unit', 'nilai' => 60, 'config_id' => '5102012001', 'periode' => '2025-06'],
            ],
            [
                'type' => 'ds_nilai',
                'id' => '2',
                'attributes' => ['isian_kode' => 'jumlah_unit', 'nilai' => 60, 'config_id' => '5102012002', 'periode' => '2025-06'],
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------
// Fixture KAB-07 — Monitoring Desa (K-verify LANGSUNG 2026-10-21 terhadap
// API hidup 127.0.0.1:8001 + sumber repo API-Database-Gabungan).
//
// Koreksi yang terkunci di sini (lihat contracts/dataspasial-consumption.md §8):
//   K1 kolom sumber nilai bernama `sumber_data` (BUKAN `sumber`),
//      nilai: manual | desa | impor | pemetaan
//   K2 respons `nilai` TIDAK memuat identitas desa (hanya fasilitas_id) —
//      rincian per desa diambil per desa (`nilai?kode_desa=X`)
//   K3 `ringkasan` per wilayah: {wilayah, wilayah_kode, total_fasilitas,
//      total_isian, nilai:{angka,pilihan,teks}} — tanpa meta, tanpa per-isian
//   K4 isian aktif versi terbit: riwayatSkema → listSkema (isian + tipe)
//   K5 periode default diturunkan dari `terakhir_ada_data` adopsi
// ---------------------------------------------------------------------------

/**
 * Isian versi terbit SK0 (nomor 3) untuk monitoring: 3 angka + 1 teks.
 *
 * Denominator kelengkapan = 4 isian aktif (tanpa arsip).
 *
 * @return array<int, array<string, mixed>>
 */
function fxMonitoringIsianVersi3(): array
{
    return [
        fxIsian('jumlah_lk', ['label' => 'Jumlah Laki-laki', 'tipe' => 'angka', 'satuan' => 'jiwa', 'urutan' => 1]),
        fxIsian('jumlah_pr', ['label' => 'Jumlah Perempuan', 'tipe' => 'angka', 'satuan' => 'jiwa', 'urutan' => 2]),
        fxIsian('jumlah_total', ['label' => 'Jumlah Penduduk', 'tipe' => 'angka', 'satuan' => 'jiwa', 'urutan' => 3]),
        fxIsian('catatan', ['label' => 'Catatan', 'tipe' => 'teks', 'urutan' => 4]),
    ];
}

/**
 * Koleksi adopsi campuran untuk satu kecamatan monitoring (sektor_id 4234).
 *
 * Dunia uji: versi terbit terbaru SK0 = 3.
 *   D1 Bajera   5102012001 v3  mutakhir   (memakai_skema)
 *   D2 Bantas   5102022003 v2  tertinggal (memakai_skema)
 *   D3 Cekatak  5102022004 —   belum_ada_data
 *   D4 Dauhwaru 5102012005 v4  anomali    (lebih besar dari terbit)
 *   D5 Epal     5102012006 v3  mutakhir
 *
 * @return array<string, mixed>
 */
function fxMonitoringAdopsi(): array
{
    return fxAdopsiKoleksi([
        fxAdopsiBaris(1, '5102012001', 'Bajera', 3, 4234, 'memakai_skema', '2025-06-05 10:00:00'),
        fxAdopsiBaris(2, '5102022003', 'Bantas', 2, 4234, 'memakai_skema', '2025-05-02 09:00:00'),
        fxAdopsiBaris(3, '5102022004', 'Cekatak', null, null, 'belum_ada_data', null),
        fxAdopsiBaris(4, '5102012005', 'Dauhwaru', 4, 4234, 'memakai_skema', '2025-06-01 08:00:00'),
        fxAdopsiBaris(5, '5102012006', 'Epal', 3, 4234, 'memakai_skema', '2025-06-03 07:00:00'),
    ]);
}

/**
 * Satu baris `nilai?kode_desa=…` — bentuk nyata transformer API (K1/K2).
 *
 * @return array<string, mixed>
 */
function fxMonitoringNilaiBaris(
    string $id,
    string $isianKode,
    mixed $nilai,
    string $tipe = 'angka',
    string $sumberData = 'manual',
    string $periode = '2025-06',
    int $nomorVersi = 3
): array {
    return [
        'type' => 'daftar nilai DataSpasial',
        'id' => $id,
        'attributes' => [
            'fasilitas_id' => (int) $id,
            'isian_kode' => $isianKode,
            'isian_label' => ucfirst(str_replace('_', ' ', $isianKode)),
            'isian_tipe' => $tipe,
            'isian_satuan' => $tipe === 'angka' ? 'jiwa' : null,
            'isian_opsi' => null,
            'nilai' => $nilai,
            'sumber_data' => $sumberData,
            'periode' => $periode,
            'waktu_input' => '2025-06-10 09:00:00',
            'skema_versi' => ['id' => 9000 + $nomorVersi, 'nomor_versi' => $nomorVersi, 'status' => $nomorVersi === 3 ? 'terbit' : 'usang'],
            'sektor' => ['id' => 4234, 'kode' => 'SK0', 'nama' => 'Sektor 0'],
        ],
    ];
}

/**
 * Respons `nilai?kode_desa=…` lengkap untuk satu desa monitoring (K2: tanpa
 * identitas desa di badan respons).
 *
 * @param  array<int, array<string, mixed>>  $baris
 * @return array<string, mixed>
 */
function fxMonitoringNilaiDesa(array $baris): array
{
    return [
        'data' => $baris,
        'meta' => ['pagination' => [
            'total' => count($baris),
            'count' => count($baris),
            'per_page' => 100,
            'current_page' => 1,
            'total_pages' => 1,
        ]],
        'links' => [],
    ];
}

/**
 * Nilai per desa pada dunia uji monitoring (sektor SK0, periode 2025-06).
 *
 * @return array<string, array<int, array<string, mixed>>>
 */
function fxMonitoringNilaiPerDesa(): array
{
    return [
        // D1 Bajera — valid (100+90=190), sumber manual, 3 dari 4 isian.
        '5102012001' => [
            fxMonitoringNilaiBaris('11', 'jumlah_lk', 100),
            fxMonitoringNilaiBaris('12', 'jumlah_pr', 90),
            fxMonitoringNilaiBaris('13', 'jumlah_total', 190),
        ],
        // D2 Bantas — melanggar (50+40≠100), sumber desa.
        '5102022003' => [
            fxMonitoringNilaiBaris('21', 'jumlah_lk', 50, 'angka', 'desa'),
            fxMonitoringNilaiBaris('22', 'jumlah_pr', 40, 'angka', 'desa'),
            fxMonitoringNilaiBaris('23', 'jumlah_total', 100, 'angka', 'desa'),
        ],
        // D3 Cekatak — tidak ada baris nilai (belum_ada_data).
        '5102022004' => [],
        // D4 Dauhwaru — valid (10+10=20), sumber pemetaan (tanpa tanda impor).
        '5102012005' => [
            fxMonitoringNilaiBaris('41', 'jumlah_lk', 10, 'angka', 'pemetaan'),
            fxMonitoringNilaiBaris('42', 'jumlah_pr', 10, 'angka', 'pemetaan'),
            fxMonitoringNilaiBaris('43', 'jumlah_total', 20, 'angka', 'pemetaan'),
        ],
        // D5 Epal — melanggar (5+5≠99), sumber impor (tanda impor).
        '5102012006' => [
            fxMonitoringNilaiBaris('51', 'jumlah_lk', 5, 'angka', 'impor'),
            fxMonitoringNilaiBaris('52', 'jumlah_pr', 5, 'angka', 'impor'),
            fxMonitoringNilaiBaris('53', 'jumlah_total', 99, 'angka', 'impor'),
        ],
    ];
}

/**
 * Respons `ringkasan?…&group_by=desa` (K3: tanpa meta, tanpa per-isian).
 *
 * @param  array<string, int>  $totalIsianPerDesa  kode_desa => total_isian
 * @return array<string, mixed>
 */
function fxMonitoringRingkasan(array $totalIsianPerDesa = []): array
{
    if ($totalIsianPerDesa === []) {
        $totalIsianPerDesa = [
            '5102012001' => 3,
            '5102022003' => 3,
            '5102012005' => 3,
            '5102012006' => 3,
        ];
    }

    $data = [];

    foreach ($totalIsianPerDesa as $kode => $totalIsian) {
        $data[] = [
            'type' => 'ds_ringkasan',
            'attributes' => [
                'wilayah' => 'Desa '.$kode,
                'wilayah_kode' => $kode,
                'total_fasilitas' => 1,
                'total_isian' => $totalIsian,
                'nilai' => [
                    'angka' => ['jumlah' => $totalIsian, 'total' => 0, 'rata_rata' => 0, 'min' => null, 'max' => null],
                    'pilihan' => ['jumlah' => 0, 'pecahan' => []],
                    'teks' => ['jumlah' => 0, 'catatan' => 'dieksklusi dari agregat'],
                ],
            ],
        ];
    }

    return ['data' => $data];
}

/**
 * Daftar kecamatan untuk komponen filter (statistik-web, bentuk polos).
 *
 * @return array<int, array<string, string>>
 */
function fxMonitoringWilayah(): array
{
    return [
        ['kode_kecamatan' => '510201', 'nama_kecamatan' => 'Kecamatan Kediri'],
        ['kode_kecamatan' => '510202', 'nama_kecamatan' => 'Kecamatan Mengwi'],
    ];
}

/**
 * Respons `config/kecamatan` (JSON:API) untuk kolom Kecamatan tabel
 * monitoring (KAB-07).
 *
 * @return array<string, mixed>
 */
function fxMonitoringKecamatan(): array
{
    return ['data' => [
        ['type' => 'config', 'id' => '1', 'attributes' => ['kode_kecamatan' => '510201', 'nama_kecamatan' => 'Kecamatan Kediri']],
        ['type' => 'config', 'id' => '2', 'attributes' => ['kode_kecamatan' => '510202', 'nama_kecamatan' => 'Kecamatan Mengwi']],
    ]];
}

/**
 * Palsukan seluruh endpoint yang dipakai halaman monitoring (tanpa peta).
 *
 * Secara bawaan memakai dunia uji `fxMonitoringAdopsi` + `fxMonitoringNilaiPerDesa`.
 *
 * @param  array<string, mixed>|null  $adopsi
 * @param  array<string, array<int, array<string, mixed>>>|null  $nilaiPerDesa
 * @param  array<string, mixed>|null  $ringkasan
 * @param  array<int, array<string, mixed>>|null  $riwayat
 * @param  array<string, mixed>|null  $skemaAktif
 */
function fakeEndpointMonitoring(
    ?array $adopsi = null,
    ?array $nilaiPerDesa = null,
    ?array $ringkasan = null,
    ?array $riwayat = null,
    ?array $skemaAktif = null
): void {
    $adopsi ??= fxMonitoringAdopsi();
    $nilaiPerDesa ??= fxMonitoringNilaiPerDesa();
    $ringkasan ??= fxMonitoringRingkasan();
    $riwayat ??= [fxRiwayatVersi('8143', 4234, 3, 'terbit'), fxRiwayatVersi('8142', 4234, 1, 'draf')];
    $skemaAktif ??= fxSkemaAktif('8143', 4234, 'SK0', 'Sektor 0', 3, 'terbit', fxMonitoringIsianVersi3());

    // ⚠️ Http::fake(array): stub PERTAMA yang cocok menang — pola spesifik
    // (rincian per desa) HARUS didaftarkan sebelum wildcard dan catch-all.
    $fakes = [];

    foreach ($nilaiPerDesa as $kodeDesa => $baris) {
        $fakes['*/api/v1/dataspasial/nilai*kode_desa='.$kodeDesa.'*'] = Http::response(
            fxMonitoringNilaiDesa($baris),
            200
        );
    }

    // Kolom Kecamatan (nama kecamatan per kode) — sebelum wildcard config.
    $fakes['*/api/v1/config/kecamatan*'] = Http::response(fxMonitoringKecamatan(), 200);
    $fakes['*/api/v1/config/*'] = Http::response(fakeConfigEndpoint(), 200);
    $fakes['*/api/v1/dataspasial/adopsi*'] = Http::response($adopsi, 200);
    $fakes['*/api/v1/dataspasial/ringkasan*'] = Http::response($ringkasan, 200);
    $fakes['*/api/v1/dataspasial/skema/riwayat*'] = Http::response(fakeKoleksi($riwayat), 200);
    $fakes['*/api/v1/dataspasial/sektor*'] = Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200);
    $fakes['*/api/v1/dataspasial/skema?*'] = Http::response($skemaAktif, 200);
    $fakes['*'] = Http::response(['data' => []], 200);

    Http::fake($fakes);
}
