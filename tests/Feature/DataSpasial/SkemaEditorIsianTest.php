<?php

declare(strict_types=1);

use App\Enums\TipeIsian;
use App\Models\Setting;
use App\Services\SkemaEditorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US2 — Menyusun isian pada sektor.
 *
 * Skenario yang diuji (spec.md US2):
 *  #1 isian baru tampil lengkap dengan atributnya (FR-009)
 *  #2 kolom opsi aktif hanya untuk tipe `pilihan` (FR-010)
 *  #3 urutan berubah setelah diseret (FR-012)
 *  #4/#6 hapus isian dengan konfirmasi yang menyebut kode + pemetaan (FR-014)
 *  #5 dirty-guard memberi tahu ada perubahan belum disimpan (FR-016)
 *
 * ⚠️ Aturan kolom opsi/satuan diuji pada ATRIBUT yang dirender server
 * (`form-isian.blade.php`) — bukan perilaku JS, karena perilaku kolom aktif
 * saat tipe diganti dibrowser berada di `schema-editor.js` dan diuji manual
 * lewat `quickstart.md` §2.
 */
beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.cache_ttl' => 300,
    ]);

    Cache::flush();

    Setting::updateOrCreate(
        ['key' => 'database_gabungan_api_key'],
        ['name' => 'API Key', 'value' => 'token-uji']
    );

    $this->user = makeRole('admin skema us2', [
        'dataspasial-skema-read',
        'dataspasial-skema-write',
        'dataspasial-skema-edit',
        'dataspasial-skema-delete',
    ]);

    test()->actingAs($this->user, 'web');

    $this->urlDraf = route('dataspasial.skema.draf.store');
});

/**
 * @param  array<string, mixed>  $ubah
 * @return array<string, mixed>
 */
function isian(string $kode, array $ubah = []): array
{
    return array_merge([
        'id' => null,
        'kode' => $kode,
        'label' => ucfirst(str_replace('_', ' ', $kode)),
        'tipe' => 'teks',
        'wajib' => false,
        'satuan' => null,
        'opsi' => null,
        'urutan' => 1,
        'diarsipkan' => false,
        'popupPeta' => false,
        'sebagaiFilter' => false,
        'sebagaiIndikator' => false,
    ], $ubah);
}

/**
 * @param  array<int, array<string, mixed>>  $isian
 * @return array<string, mixed>
 */
function isiPayload(array $isian): array
{
    return ['sektor' => 'pendidikan', 'isian' => $isian];
}

// ---------------------------------------------------------------------------
// FR-017/Q14 — `kode` diturunkan dari `label`, bukan diketik admin
// ---------------------------------------------------------------------------

it('tidak merender kolom kode sama sekali di tabel isian', function () {
    // ⚠️ FR-017/Q14 — `kode` diturunkan otomatis dari `label`, jadi kolomnya
    // DIHAPUS: tidak ada header "Kode" dan tidak ada input `input-kode`.
    $tabel = file_get_contents(resource_path('views/dataspasial/skema/partials/tabel-isian.blade.php'));
    $baris = file_get_contents(resource_path('views/dataspasial/skema/partials/form-isian.blade.php'));

    expect($tabel)->not->toContain('<th>Kode</th>')
        ->and($tabel)->not->toContain('input-kode')
        ->and($baris)->not->toContain('input-kode');

    // Kolom `label` tetap ada dan tetap dapat disunting.
    expect($baris)->toContain('input-label');
});

it('tidak memaccess properti dataset bergaris dalam serialisasi isian', function () {
    $js = file_get_contents(public_path('js/dataspasial/schema-editor.js'));

    // ⚠️ REGRESSION: `dataset?.aturan-hitung` diparse sebagai pengurangan
    // (`aturan - hitung`) → ReferenceError setiap kali `bacaDariDom()` jalan,
    // yaitu setiap kali admin menekan "Simpan draf". Atribut dataset memakai
    // camelCase, jadi aksesnya harus `dataset.aturanHitung`.
    // Komentar dibuang dulu supaya penjelasan di dalam kode tidak ikut
    // dianggap sebagai akses properti yang salah.
    $kode = preg_replace(['#//[^\n]*#', '#/\*.*?\*/#s'], '', $js);

    expect($kode)->not->toMatch('/dataset\??\.[a-zA-Z_]*-/')
        ->and($js)->toContain('dataset?.aturanHitung ?? null');
});

it('tidak mengirim kolom kode dari DOM saat menyerialisasi isian', function () {
    $js = file_get_contents(public_path('js/dataspasial/schema-editor.js'));

    // ⚠️ `kode` harus SELALU dihitung ulang dari `label` saat serialisasi —
    // tidak boleh dibaca dari input yang bisa diedit.
    expect($js)->toContain("kode: slugKodeDariLabel(")
        ->and($js)->not->toContain(".input-kode')?.value?.trim()");
});

it('membangkitkan kode dari label memakai pola snake_case yang sama seperti kode sektor', function () {
    $editor = app(SkemaEditorService::class);

    expect($editor->petakanIsian(['id' => '31', 'attributes' => isian('jumlah_siswa')])['kode'])
        ->toBe('jumlah_siswa');
});

it('menampilkan isian lengkap dengan seluruh atributnya', function () {
    $isi = isian('jumlah_siswa', [
        'tipe' => 'angka',
        'wajib' => true,
        'satuan' => 'orang',
        'urutan' => 1,
    ]);

    $editor = app(SkemaEditorService::class);

    expect($editor->petakanIsian(['id' => '31', 'attributes' => $isi]))->toBe([
        'id' => 31,
        'kode' => 'jumlah_siswa',
        'label' => 'Jumlah siswa',
        'tipe' => 'angka',
        'wajib' => true,
        'satuan' => 'orang',
        'opsi' => [],
        'urutan' => 1,
        'diarsipkan' => false,
        'sumber' => 'manual',
        'rujukan' => null,
        'aturanHitung' => null,
        'popupPeta' => false,
        'sebagaiFilter' => false,
        'sebagaiIndikator' => false,
    ]);
});

it('mengurutkan isian berdasarkan urutan yang tersimpan', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanDaftarIsian([
        ['id' => '2', 'attributes' => isian('b', ['urutan' => 2])],
        ['id' => '1', 'attributes' => isian('a', ['urutan' => 1])],
        ['id' => '3', 'attributes' => isian('c', ['urutan' => 3])],
    ]);

    expect(array_column($hasil, 'kode'))->toBe(['a', 'b', 'c'])
        ->and(array_column($hasil, 'urutan'))->toBe([1, 2, 3]);
});

// ---------------------------------------------------------------------------
// FR-010 — kolom opsi & satuan hanya berlaku pada tipenya
// ---------------------------------------------------------------------------

it('mengaktifkan kolom opsi hanya untuk tipe pilihan', function () {
    // ⚠️ `TipeIsian::Pilihan` adalah konstanta PHP biasa (string), sehingga
    // instance enum harus dibangun lewat `fromValue()`.
    $memakaiPilihan = fn (string $tipe): bool => TipeIsian::fromValue($tipe)->memakaiPilihan();
    $memakaiSatuan = fn (string $tipe): bool => TipeIsian::fromValue($tipe)->memakaiSatuan();

    expect($memakaiPilihan('pilihan'))->toBeTrue()
        ->and($memakaiPilihan('teks'))->toBeFalse()
        ->and($memakaiPilihan('angka'))->toBeFalse()
        ->and($memakaiPilihan('ya_tidak'))->toBeFalse()
        ->and($memakaiPilihan('tanggal'))->toBeFalse();

    // Satuan hanya relevan pada tipe `angka`.
    expect($memakaiSatuan('angka'))->toBeTrue()
        ->and($memakaiSatuan('teks'))->toBeFalse()
        ->and($memakaiSatuan('tanggal'))->toBeFalse();
});

it('menerima butir pilihan pada tipe pilihan', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '9', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->urlDraf, isiPayload([
        isian('jenis_sekolah', ['tipe' => 'pilihan', 'opsi' => ['Negeri', 'Swasta']]),
    ]))->assertSessionHasNoErrors();

    Http::assertSent(fn ($request) => str_contains($request->body(), 'Negeri')
        && str_contains($request->body(), 'Swasta'));
});

it('mengabaikan satuan pada tipe yang tidak memerlukan satuan', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '9', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    // Satuan pada tipe `teks` TIDAK boleh menjadi alasan penyimpanan gagal.
    $this->post($this->urlDraf, isiPayload([
        isian('catatan', ['tipe' => 'teks', 'satuan' => 'orang']),
    ]))->assertSessionHasNoErrors();
});

it('mengabaikan kolom opsi pada tipe selain pilihan saat menyusun draf', function () {
    $editor = app(SkemaEditorService::class);

    $payload = $editor->composeDrafPayload([
        [
            'kode' => 'catatan',
            'label' => 'Catatan',
            'tipe' => 'teks',
            'wajib' => false,
            'satuan' => null,
            'opsi' => ['tidak-boleh-dipakai'],
        ],
    ], ['sektor' => 'pendidikan']);

    expect($payload['isian'][0]['opsi'])->toBeNull();
});

// ---------------------------------------------------------------------------
// FR-012 — urutan
// ---------------------------------------------------------------------------

it('menulis ulang urutan menjadi 1..N tanpa celah saat menyusun draf', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '9', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    // Urutan sengaja dikirim acak (hasil urutan baru setelah seret).
    $this->post($this->urlDraf, isiPayload([
        isian('a', ['urutan' => 1]),
        isian('b', ['urutan' => 2]),
        isian('c', ['urutan' => 3]),
    ]))->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return array_column($body['isian'] ?? [], 'urutan') === [1, 2, 3]
            && array_column($body['isian'] ?? [], 'kode') === ['a', 'b', 'c'];
    });
});

// ---------------------------------------------------------------------------
// FR-015 — isian terpetakan tampil terkunci
// ---------------------------------------------------------------------------

it('menandai isian sebagai terpetakan dari pemetaan yang tertanam', function () {
    $editor = app(SkemaEditorService::class);

    // ⚠️ VERIFIKASI LANGSUNG: pemetaan TERTANAM di dalam tiap isian
    // (`attributes.pemetaan[]`), bukan diambil dari endpoint terpisah.
    $hasil = $editor->petakanDaftarIsian([
        ['id' => '31', 'attributes' => isian('jumlah_siswa', ['id' => 31, 'pemetaan' => [
            ['id' => 3951, 'sumber' => 'dataPresisi', 'rujukan_sumber' => 'data_presisi_pendidikan.*', 'aturan_hitung' => null],
        ]])],
        ['id' => '32', 'attributes' => isian('luas_ha', ['id' => 32, 'urutan' => 2, 'pemetaan' => []])],
    ]);

    // `sumber` berisi ENUM API, bukan kode sektor (kontrak §6a.5).
    expect($hasil[0]['sumber'])->toBe('dataPresisi')
        ->and($hasil[0]['rujukan'])->toBe('data_presisi_pendidikan.*')
        // Isian tanpa pemetaan tetap manual (FR-023).
        ->and($hasil[1]['sumber'])->toBe('manual')
        ->and($hasil[1]['rujukan'])->toBeNull();
});

it('mengabaikan pemetaan yang tidak memiliki sektor sumber', function () {
    $editor = app(SkemaEditorService::class);

    $isian = $editor->petakanDaftarIsian([['id' => '31', 'attributes' => isian('jumlah_siswa')]]);

    $hasil = $editor->terapkanPemetaan($isian, [
        ['id' => 5, 'sumber' => null, 'rujukan' => 'x', 'aturan_hitung' => null],
        ['id' => 6, 'sumber' => '', 'rujukan' => 'y', 'aturan_hitung' => null],
    ]);

    expect($hasil[0]['sumber'])->toBe('manual');
});

it('menyertakan pemetaan pada badan draf hanya bila ada', function () {
    $editor = app(SkemaEditorService::class);

    $payload = $editor->composeDrafPayload([
        ['kode' => 'a', 'label' => 'A', 'tipe' => 'teks', 'urutan' => 1, 'sumber' => 'manual', 'rujukan' => null],
        ['kode' => 'b', 'label' => 'B', 'tipe' => 'teks', 'urutan' => 2,
            'sumber' => 'openSID', 'rujukan' => 'r'],
    ], ['sektor' => 'pendidikan']);

    expect($payload['isian'][0]['pemetaan'])->toBeNull()
        ->and($payload['isian'][1]['pemetaan'])->toBe([[
            'id' => 0,
            'sumber' => 'openSID',
            'rujukan_sumber' => 'r',
            'aturan_hitung' => null,
        ]]);
});

// ---------------------------------------------------------------------------
// FR-020 — validasi tetap satu-satunya penentu
// ---------------------------------------------------------------------------

it('menolak kode bentrok walau validasi klien lolos', function () {
    Http::fake();

    $this->post($this->urlDraf, isiPayload([
        isian('jumlah_siswa', ['urutan' => 1]),
        isian('jumlah_siswa', ['urutan' => 2]),
    ]))->assertSessionHasErrors('isian.1.kode');

    Http::assertNothingSent();
});

it('menolak kode tidak valid walau klien mengirimnya', function () {
    Http::fake();

    $this->post($this->urlDraf, isiPayload([isian('Jumlah Siswa')]))
        ->assertSessionHasErrors('isian.0.kode');

    Http::assertNothingSent();
});

// ---------------------------------------------------------------------------
// Editor benar-benar menampilkan baris isian (FR-009)
// ---------------------------------------------------------------------------

it('merender baris isian pada halaman editor dengan tombol tambah isian', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response([
            'data' => [[
                'type' => 'ds_sektor', 'id' => '1',
                'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'ikon' => null, 'status' => 'aktif'],
            ]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 100, 'current_page' => 1, 'total_pages' => 1]],
        ], 200),
        // ⚠️ Q9: daftar skema dari `/skema/riwayat` (bukan `/skema?`).
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response([
            'data' => [[
                'type' => 'riwayat versi skema', 'id' => '9',
                'attributes' => ['sektor_id' => 1, 'nomor_versi' => 3, 'status' => 'draf',
                    'catatan_perubahan' => null, 'diterbitkan_pada' => null, 'diterbitkan_oleh' => null],
            ]],
        ], 200),
        // ⚠️ K2/K3: versi aktif — `versi_skema` bersarang, isian ikut di dalamnya.
        '*/api/v1/dataspasial/skema?*' => Http::response([
            'type' => 'skema data spasial', 'id' => '9',
            'attributes' => [
                'sektor' => ['id' => 1, 'kode' => 'pendidikan', 'nama' => 'Pendidikan'],
                'versi_skema' => ['id' => 9, 'sektor_id' => 1, 'nomor_versi' => 3, 'status' => 'draf',
                    'catatan_perubahan' => null, 'diterbitkan_pada' => null, 'diterbitkan_oleh' => null],
                'isian' => [
                    isian('jumlah_siswa', ['id' => 31, 'tipe' => 'angka', 'wajib' => true, 'satuan' => 'orang', 'urutan' => 1]),
                    isian('jenis_sekolah', ['id' => 32, 'tipe' => 'pilihan', 'opsi' => ['Negeri', 'Swasta'], 'urutan' => 2]),
                ],
            ],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('jumlah_siswa')
        ->toContain('Jumlah siswa')
        ->toContain('jenis_sekolah')
        ->toContain('Negeri')
        ->toContain('btn-tambah-isian')
        // Kolom opsi NONAKTIF pada tipe non-pilihan (FR-010).
        ->toContain('class="form-control form-control-sm input-opsi" multiple size="2" disabled')
        // Urutan tampil pada kolom # (FR-012).
        ->toContain('class="text-center align-middle urutan text-muted"');
});

it('menampilkan keadaan kosong dengan ajakan menambah isian pertama', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response([
            'data' => [[
                'type' => 'ds_sektor', 'id' => '1',
                'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'ikon' => null, 'status' => 'aktif'],
            ]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 100, 'current_page' => 1, 'total_pages' => 1]],
        ], 200),
        '*/api/v1/dataspasial/skema?*' => Http::response([
            'type' => 'skema data spasial', 'id' => '9',
            'attributes' => [
                'sektor' => ['id' => 1, 'kode' => 'pendidikan', 'nama' => 'Pendidikan'],
                'versi_skema' => ['id' => 9, 'sektor_id' => 1, 'nomor_versi' => 1,
                    'status' => 'draf', 'catatan_perubahan' => null,
                    'diterbitkan_pada' => null, 'diterbitkan_oleh' => null],
                'isian' => [],
            ],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->assertSee('Belum ada isian pada skema ini')
        ->assertSee('Tambah Isian Baru');
});
