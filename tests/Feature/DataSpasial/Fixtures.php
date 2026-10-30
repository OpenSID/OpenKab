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
