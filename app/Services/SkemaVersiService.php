<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Perhitungan versi skema untuk layar terbitan (KAB-03 / US1, US2, US3).
 *
 * Tanggung jawab:
 * 1. Membaca riwayat versi satu sektor (`GET /dataspasial/skema/riwayat`).
 * 2. Membaca isian **satu versi tertentu** — bukan endpoint `/skema/{id}`
 *    yang tidak ada di API (research.md R3).
 * 3. Menghitung ringkasan perubahan (isian baru / diubah / diarsipkan)
 *    dari **selisih nyata** antara draf dan versi terbit terakhir (FR-010).
 * 4. Membandingkan dua versi (FR-052).
 * 5. Menyusun peringatan dampak pengarsipan & bentrok (FR-030, FR-040).
 *
 * ⚠️ **VERIFIKASI LANGSUNG (2026-10-16)** — tiga koreksi kontrak dikunci di sini:
 *   K4 `GET /dataspasial/skema/{id}` **tidak ada** (404). Isian per versi diambil
 *      lewat `?sektor={kode}&versi={nomor_versi}&termasuk_arsip=1`.
 *   K5 status versi punya nilai ketiga `usang` (versi terbit lama yang digantikan).
 *   `sektor` pada path = **kode** sektor, bukan id numerik.
 *
 * ⚠️ **Batas minimum 10 nama isian** pada ringkasan (FR-011) — dipakai bersama
 * `research.md` agar angka ini hanya ada di satu tempat.
 */
class SkemaVersiService
{
    /**
     * Jumlah nama isian yang ditampilkan sebelum diringkas menjadi
     * "… dan N lainnya" (FR-011).
     */
    public const BATAS_NAMA_ISIAN = 10;

    /**
     * Peta status versi API → label bahasa pengguna (R10).
     *
     * @var array<string, string>
     */
    private const LABEL_STATUS = [
        'draf' => 'Draf',
        'terbit' => 'Terbit',
        'usang' => 'Usang',
    ];

    /**
     * Atribut isian yang dibandingkan dalam ringkasan & diff (FR-013).
     *
     * ⚠️ Nama internal hasil `SkemaEditorService::petakanIsian()` — **bukan**
     * nama field API (lihat `research.md` R12).
     *
     * @var array<int, string>
     */
    private const ATRIBUT_DIBANDINGKAN = [
        'label',
        'tipe',
        'wajib',
        'satuan',
        'opsi',
        'urutan',
        'popupPeta',
        'sebagaiFilter',
        'sebagaiIndikator',
        'sumber',
        'rujukan',
    ];

    public function __construct(
        private readonly DataSpasialApiService $dataspasial,
        private readonly SkemaEditorService $editor,
    ) {}

    // -----------------------------------------------------------------------
    // Pembacaan
    // -----------------------------------------------------------------------

    /**
     * Riwayat versi satu sektor, dari versi terbaru (FR-050).
     *
     * @return array{versi: array<int, array<string, mixed>>, tersedia: bool}
     */
    public function riwayatSektor(string $kodeSektor): array
    {
        $result = $this->dataspasial->riwayatSkema(['sektor' => $kodeSektor]);

        if ($result->isUnavailable()) {
            return ['versi' => [], 'tersedia' => false];
        }

        $versi = array_values(array_filter(array_map(
            fn (array $item): ?array => $this->normalisasiVersi($item),
            $result->data
        )));

        return ['versi' => $versi, 'tersedia' => true];
    }

    /**
     * Isian satu versi tertentu (FR-052/FR-055).
     *
     * ⚠️ `termasuk_arsip=1` **wajib**: tanpa itu isian terarsip hilang dari
     * perbandingan sehingga admin tidak melihat isian yang hilang dari formulir.
     *
     * @return array{isian: array<int, array<string, mixed>>, versi: array<string, mixed>, tersedia: bool}
     */
    public function isianVersi(string $kodeSektor, int $nomorVersi, bool $termasukArsip = true): array
    {
        $result = $this->dataspasial->listSkema(array_filter([
            'sektor' => $kodeSektor,
            'versi' => $nomorVersi,
            'termasuk_arsip' => $termasukArsip ? 1 : null,
        ], static fn ($value): bool => $value !== null));

        if ($result->isUnavailable()) {
            return ['isian' => [], 'versi' => [], 'tersedia' => false];
        }

        $item = is_array($result->first()) ? $result->first() : null;

        if ($item === null) {
            return ['isian' => [], 'versi' => [], 'tersedia' => false];
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $versi = is_array($atribut['versi_skema'] ?? null) ? $atribut['versi_skema'] : [];
        $isian = is_array($atribut['isian'] ?? null) ? $atribut['isian'] : [];

        return [
            'isian' => $this->editor->petakanDaftarIsian($isian),
            'versi' => [
                'nomorVersi' => (int) ($versi['nomor_versi'] ?? $nomorVersi),
                'status' => (string) ($versi['status'] ?? ''),
            ],
            'tersedia' => true,
        ];
    }

    /**
     * Versi terbit terakhir pada satu sektor, atau `null` bila belum ada (FR-015).
     *
     * @param  array<int, array<string, mixed>>|null  $riwayat
     * @return array<string, mixed>|null
     */
    public function versiTerbitTerakhir(string $kodeSektor, ?array $riwayat = null): ?array
    {
        $riwayat ??= $this->riwayatSektor($kodeSektor)['versi'];

        foreach ($riwayat as $versi) {
            if ($versi['status'] === 'terbit') {
                return $versi;
            }
        }

        return null;
    }

    /**
     * Versi draf yang sedang berjalan pada satu sektor, atau `null` (FR-051).
     *
     * @param  array<int, array<string, mixed>>|null  $riwayat
     * @return array<string, mixed>|null
     */
    public function versiDraf(string $kodeSektor, ?array $riwayat = null): ?array
    {
        $riwayat ??= $this->riwayatSektor($kodeSektor)['versi'];

        foreach ($riwayat as $versi) {
            if ($versi['status'] === 'draf') {
                return $versi;
            }
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Ringkasan perubahan (US1 / FR-010 … FR-016)
    // -----------------------------------------------------------------------

    /**
     * Hitung ringkasan perubahan antara draf dan versi terbit terakhir (FR-010).
     *
     * `isianDraf` = isian draf **yang sedang disunting di layar** (sudah berupa
     * bentuk view `SkemaEditorService`), karena ringkasan harus mencerminkan apa
     * yang akan benar-benar diterbitkan.
     *
     * @param  array<int, array<string, mixed>>  $isianDraf
     * @param  array<int, array<string, mixed>>|null  $riwayat
     * @return array<string, mixed>
     */
    public function ringkasanPerubahan(string $kodeSektor, array $isianDraf, ?array $riwayat = null): array
    {
        $riwayat ??= $this->riwayatSektor($kodeSektor)['versi'];

        $versiTerbit = $this->versiTerbitTerakhir($kodeSektor, $riwayat);
        $versiDraf = $this->versiDraf($kodeSektor, $riwayat);

        // Tanpa versi terbit → seluruh isian adalah "baru" (terbitan pertama).
        if ($versiTerbit === null) {
            $jumlahBaru = count($isianDraf);

            return [
                'sektor' => $kodeSektor,
                'versi_terbit_sebelumnya' => null,
                'versi_akan_terbit' => $versiDraf['nomor_versi'] ?? null,
                'terbitan_pertama' => true,
                'isian_baru' => $this->ringkas(array_map($this->identitasIsian(...), $isianDraf)),
                'isian_diubah' => [],
                'isian_diarsipkan' => [],
                'jumlah_baru' => $jumlahBaru,
                'jumlah_diubah' => 0,
                'jumlah_diarsipkan' => 0,
                'total_perubahan' => $jumlahBaru,
                'ada_perubahan' => $jumlahBaru > 0,
            ];
        }

        $isiVersiTerbit = $this->isianVersi($kodeSektor, (int) $versiTerbit['nomor_versi'])['isian'];

        // Isian terarsip pada versi terbit tidak boleh dihitung "diarsipkan" ulang.
        $lamaAktif = array_values(array_filter(
            $isiVersiTerbit,
            static fn (array $isian): bool => ($isian['diarsipkan'] ?? false) === false
        ));

        $lama = $this->indeksKode($lamaAktif);
        $baruSekarang = $this->indeksKode($isianDraf);

        $kodeBaru = array_values(array_diff(array_keys($baruSekarang), array_keys($lama)));
        $kodeDiarsipkan = array_values(array_diff(array_keys($lama), array_keys($baruSekarang)));

        $diubah = [];

        foreach ($baruSekarang as $kode => $isian) {
            if (! isset($lama[$kode])) {
                continue;
            }

            $perubahan = $this->selisihAtribut($lama[$kode], $isian);

            if ($perubahan !== []) {
                $diubah[] = [
                    'kode' => $kode,
                    'label' => (string) ($isian['label'] ?? ''),
                    'perubahan' => $perubahan,
                ];
            }
        }

        // ⚠️ FR-011 memotong **tampilan**, bukan angkanya: jumlah yang
        // ditampilkan pada ringkasan (FR-062) harus tetap utuh.
        $jumlahBaru = count($kodeBaru);
        $jumlahDiarsipkan = count($kodeDiarsipkan);

        $isianBaru = $this->ringkas(array_map(
            fn (string $kode): array => $this->identitasIsian($baruSekarang[$kode]),
            $kodeBaru
        ));

        $isianDiarsipkan = $this->ringkas(array_map(
            fn (string $kode): array => $this->identitasIsian($lama[$kode]),
            $kodeDiarsipkan
        ));

        $total = $jumlahBaru + count($diubah) + $jumlahDiarsipkan;

        return [
            'sektor' => $kodeSektor,
            'versi_terbit_sebelumnya' => (int) $versiTerbit['nomor_versi'],
            'versi_akan_terbit' => $versiDraf['nomor_versi'] ?? null,
            'terbitan_pertama' => false,
            'isian_baru' => $isianBaru,
            'isian_diubah' => $this->ringkas($diubah),
            'isian_diarsipkan' => $isianDiarsipkan,
            'jumlah_baru' => $jumlahBaru,
            'jumlah_diubah' => count($diubah),
            'jumlah_diarsipkan' => $jumlahDiarsipkan,
            'total_perubahan' => $total,
            'ada_perubahan' => $total > 0,
        ];
    }

    /**
     * Perbandingan dua versi (FR-052).
     *
     * @param  array<int, array<string, mixed>>  $isianLama
     * @param  array<int, array<string, mixed>>  $isianBaru
     * @return array<int, array<string, mixed>>
     */
    public function bandingkanDuaVersi(array $isianLama, array $isianBaru): array
    {
        $lama = $this->indeksKode($isianLama);
        $baru = $this->indeksKode($isianBaru);

        $baris = [];

        foreach ($lama as $kode => $isian) {
            if (! isset($baru[$kode])) {
                // FR-055: isian yang hilang dari formulir tetap ditampilkan.
                $baris[] = [
                    'kode' => $kode,
                    'label' => (string) ($isian['label'] ?? ''),
                    'status' => ($isian['diarsipkan'] ?? false) ? 'diarsipkan' : 'hilang',
                    'perubahan' => [],
                ];

                continue;
            }

            $perubahan = $this->selisihAtribut($isian, $baru[$kode]);

            $baris[] = [
                'kode' => $kode,
                'label' => (string) ($baru[$kode]['label'] ?? ''),
                'status' => $perubahan === [] ? 'tetap' : 'diubah',
                'perubahan' => $perubahan,
            ];
        }

        foreach ($baru as $kode => $isian) {
            if (isset($lama[$kode])) {
                continue;
            }

            $baris[] = [
                'kode' => $kode,
                'label' => (string) ($isian['label'] ?? ''),
                'status' => 'baru',
                'perubahan' => [],
            ];
        }

        usort($baris, static fn (array $a, array $b): int => $a['kode'] <=> $b['kode']);

        return $baris;
    }

    // -----------------------------------------------------------------------
    // Pra-pemeriksaan & peringatan (US3 / FR-045, FR-030, FR-040)
    // -----------------------------------------------------------------------

    /**
     * Periksa dua keadaan yang pasti ditolak server, **sebelum** dikirim
     * (FR-045 / research.md R12):
     * 1. draf tanpa isian aktif;
     * 2. perubahan `tipe` pada isian yang kodenya sudah pernah terbit.
     *
     * @param  array<int, array<string, mixed>>  $isianDraf
     * @param  array<int, array<string, mixed>>|null  $riwayat
     * @return array{lolos: bool, alasan: ?string, isian: array<int, string>}
     */
    public function praPemeriksaanTerbit(string $kodeSektor, array $isianDraf, ?array $riwayat = null): array
    {
        $aktif = array_values(array_filter(
            $isianDraf,
            static fn (array $isian): bool => ($isian['diarsipkan'] ?? false) === false
        ));

        if ($aktif === []) {
            return [
                'polos' => false,
                'alasan' => 'Draf skema tidak memiliki isian aktif. Tambahkan minimal satu isian sebelum menerbitkan.',
                'isian' => [],
            ];
        }

        $riwayat ??= $this->riwayatSektor($kodeSektor)['versi'];
        $versiTerbit = $this->versiTerbitTerakhir($kodeSektor, $riwayat);

        if ($versiTerbit === null) {
            return ['polos' => true, 'alasan' => null, 'isian' => []];
        }

        $lama = $this->indeksKode(array_values(array_filter(
            $this->isianVersi($kodeSektor, (int) $versiTerbit['nomor_versi'])['isian'],
            static fn (array $isian): bool => ($isian['diarsipkan'] ?? false) === false
        )));

        $bertipeBentrok = [];

        foreach ($aktif as $isian) {
            $kode = (string) ($isian['kode'] ?? '');

            if ($kode === '' || ! isset($lama[$kode])) {
                continue;
            }

            if ((string) ($lama[$kode]['tipe'] ?? '') !== (string) ($isian['tipe'] ?? '')) {
                $bertipeBentrok[] = $kode;
            }
        }

        if ($bertipeBentrok !== []) {
            return [
                'polos' => false,
                'alasan' => 'Tipe data isian yang sudah terbit tidak dapat diubah: '
                    .implode(', ', $bertipeBentrok)
                    .'. Arsipkan isian lama dan buat isian baru dengan kode berbeda.',
                'isian' => $bertipeBentrok,
            ];
        }

        return ['polos' => true, 'alasan' => null, 'isian' => []];
    }

    /**
     * Susun peringatan terbitan: dampak pengarsipan + bentrok lokal desa
     * (FR-030 … FR-044).
     *
     * ⚠️ Endpoint pratinjau pra-terbit **belum ada** di API (Utang U10). Sampai
     * tersedia, sistem menampilkan daftar isian terarsip + "belum dapat
     * dihitung" dan **tidak pernah** mengarang angka (FR-043/FR-071).
     *
     * @param  array<int, array<string, mixed>>  $isianDraf
     * @param  array<string, mixed>|null  $pratinjau  hasil `DataSpasialResult` dari `praTerbit()`
     * @return array<string, mixed>
     */
    public function peringatanTerbit(array $isianDraf, ?DataSpasialResult $pratinjau = null): array
    {
        $isianDiarsipkan = array_values(array_filter(
            $isianDraf,
            static fn (array $isian): bool => ($isian['diarsipkan'] ?? false) === true
        ));

        $ringkas = $this->ringkas(array_map($this->identitasIsian(...), $isianDiarsipkan));

        $dampak = $this->bacaBagianDampak($pratinjau);
        $bentrok = $this->bacaBagianBentrok($pratinjau);

        return [
            'dampak_tersedia' => $dampak !== null,
            'isian_diarsipkan' => $ringkas,
            'jumlah_isian_diarsipkan' => count($ringkas),
            'jumlah_nilai_terdampak' => $dampak['jumlah_nilai'] ?? null,
            'jumlah_desa_terdampak' => $dampak['jumlah_desa'] ?? null,
            'jumlah_fasilitas_terdampak' => $dampak['jumlah_fasilitas'] ?? null,
            'pesan_dampak' => $dampak === null
                ? 'Jumlah data terdampak belum dapat dihitung.'
                : null,
            'bentrok_tersedia' => $bentrok !== null,
            'desa_bentrok' => $bentrok ?? [],
            'jumlah_desa_bentrok' => $bentrok === null ? null : count($bentrok),
            'pesan_bentrok' => $bentrok === null
                ? 'Pemeriksaan isian lokal desa belum dapat dilakukan.'
                : null,
            'butuh_pengakuan' => $bentrok === null || $bentrok !== [],
            'penjelasan_arsip' => 'Nilai isian yang diarsipkan tetap tersimpan dan dapat ditelusuri; '
                .'isian hanya disembunyikan dari formulir, datanya tidak dihapus.',
        ];
    }

    /**
     * Isian terarsip dari draf (dipakai dialog agar tidak memanggil API lagi).
     *
     * @param  array<int, array<string, mixed>>  $isianDraf
     * @return array<string, mixed>
     */
    public function isianDiarsipkan(array $isianDraf): array
    {
        return $this->ringkas(array_map($this->identitasIsian(...), array_values(array_filter(
            $isianDraf,
            static fn (array $isian): bool => ($isian['diarsipkan'] ?? false) === true
        ))));
    }

    // -----------------------------------------------------------------------
    // Normalisasi internal
    // -----------------------------------------------------------------------

    /**
     * Normalisasi satu item riwayat versi menjadi bentuk view.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalisasiVersi(array $item): ?array
    {
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];

        if ($atribut === []) {
            return null;
        }

        $status = (string) ($atribut['status'] ?? '');
        $catatan = $atribut['catatan_perubahan'] ?? null;

        return [
            'id' => (int) ($item['id'] ?? 0),
            'sektor_id' => (int) ($atribut['sektor_id'] ?? 0),
            'nomor_versi' => (int) ($atribut['nomor_versi'] ?? 0),
            'status' => $status,
            'status_label' => self::LABEL_STATUS[$status] ?? ucfirst($status),
            'diterbitkan_pada' => $atribut['diterbitkan_pada'] ?? null,
            'diterbitkan_oleh' => $atribut['diterbitkan_oleh'] ?? null,
            'catatan_perubahan' => filled($catatan) ? (string) $catatan : null,
            'catatan_label' => filled($catatan) ? (string) $catatan : '(tanpa catatan)',
        ];
    }

    /**
     * Ambil bagian dampak dari respons endpoint pra-terbit (bila tersedia).
     *
     * @return array<string, mixed>|null
     */
    private function bacaBagianDampak(?DataSpasialResult $pratinjau): ?array
    {
        if ($pratinjau === null || $pratinjau->isUnavailable()) {
            return null;
        }

        $item = is_array($pratinjau->first()) ? $pratinjau->first() : null;

        if ($item === null) {
            return null;
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;

        $isian = is_array($atribut['isian_diarsipkan'] ?? null) ? $atribut['isian_diarsipkan'] : [];

        if ($isian === []) {
            return ['jumlah_nilai' => 0, 'jumlah_desa' => 0, 'jumlah_fasilitas' => 0];
        }

        return [
            'jumlah_nilai' => array_sum(array_map(
                static fn (array $b): int => (int) ($b['jumlah_nilai'] ?? 0),
                $isian
            )),
            'jumlah_desa' => array_sum(array_map(
                static fn (array $b): int => (int) ($b['jumlah_desa'] ?? 0),
                $isian
            )),
            'jumlah_fasilitas' => array_sum(array_map(
                static fn (array $b): int => (int) ($b['jumlah_fasilitas'] ?? 0),
                $isian
            )),
        ];
    }

    /**
     * Ambil daftar desa yang bentrok dari respons endpoint pra-terbit.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function bacaBagianBentrok(?DataSpasialResult $pratinjau): ?array
    {
        if ($pratinjau === null || $pratinjau->isUnavailable()) {
            return null;
        }

        $item = is_array($pratinjau->first()) ? $pratinjau->first() : null;

        if ($item === null) {
            return null;
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
        $desa = is_array($atribut['desa_bentrok'] ?? null) ? $atribut['desa_bentrok'] : [];

        return array_values(array_map(static fn (array $d): array => [
            'config_id' => (int) ($d['config_id'] ?? 0),
            'kode_desa' => (string) ($d['kode_desa'] ?? ''),
            'nama_desa' => (string) ($d['nama_desa'] ?? ''),
            'isian' => array_values(array_map(static fn (array $i): array => [
                'kode' => (string) ($i['kode'] ?? ''),
                'alasan' => (string) ($i['alasan'] ?? ''),
            ], is_array($i['isian'] ?? null) ? $i['isian'] : [])),
        ], $desa));
    }

    /**
     * Selisih atribut satu isian: `dari → ke` per atribut yang berbeda (FR-013).
     *
     * @param  array<string, mixed>  $lama
     * @param  array<string, mixed>  $baru
     * @return array<int, array<string, mixed>>
     */
    private function selisihAtribut(array $lama, array $baru): array
    {
        $perubahan = [];

        foreach (self::ATRIBUT_DIBANDINGKAN as $atribut) {
            $dari = $this->nilaiTampil($lama[$atribut] ?? null);
            $ke = $this->nilaiTampil($baru[$atribut] ?? null);

            if ($dari !== $ke) {
                $perubahan[] = ['atribut' => $atribut, 'dari' => $dari, 'ke' => $ke];
            }
        }

        return $perubahan;
    }

    /**
     * Ubah nilai atribut menjadi bentuk yang bisa dibandingkan & ditampilkan.
     */
    private function nilaiTampil(mixed $nilai): string
    {
        if (is_bool($nilai)) {
            return $nilai ? 'ya' : 'tidak';
        }

        if (is_array($nilai)) {
            $isi = array_values(array_filter(
                array_map(static fn ($v): string => is_scalar($v) ? (string) $v : '', $nilai),
                static fn (string $v): bool => $v !== ''
            ));

            return $isi === [] ? '—' : implode(', ', $isi);
        }

        if ($nilai === null || $nilai === '') {
            return '—';
        }

        return (string) $nilai;
    }

    /**
     * Identitas singkat satu isian (kode + label).
     *
     * @param  array<string, mixed>  $isian
     * @return array<string, mixed>
     */
    private function identitasIsian(array $isian): array
    {
        return [
            'kode' => (string) ($isian['kode'] ?? ''),
            'label' => (string) ($isian['label'] ?? ''),
        ];
    }

    /**
     * Potong daftar isian pada batas tampilan dan tambahkan penanda sisanya
     * (FR-011).
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<int, array<string, mixed>>
     */
    private function ringkas(array $isian): array
    {
        if (count($isian) <= self::BATAS_NAMA_ISIAN) {
            return $isian;
        }

        $potong = array_slice($isian, 0, self::BATAS_NAMA_ISIAN);
        $potong[] = [
            'kode' => '',
            'label' => '… dan '.(count($isian) - self::BATAS_NAMA_ISIAN).' isian lainnya',
            'sisa' => count($isian) - self::BATAS_NAMA_ISIAN,
        ];

        return $potong;
    }

    /**
     * Indeks isian berdasarkan `kode` (kunci pencocokan FR-010).
     *
     * @param  array<int, array<string, mixed>>  $isian
     * @return array<string, array<string, mixed>>
     */
    private function indeksKode(array $isian): array
    {
        $indeks = [];

        foreach ($isian as $baris) {
            $kode = (string) ($baris['kode'] ?? '');

            if ($kode !== '') {
                $indeks[$kode] = $baris;
            }
        }

        return $indeks;
    }

    /**
     * Catat kegagalan baca untuk diagnosa (Prinsip III: log backend, pesan
     * generik di layar — FR-005).
     */
    public function logKegagalan(string $stage, ?DataSpasialResult $result, string $kodeSektor): void
    {
        Log::warning('DataSpasial KAB-03: gagal memuat '.$stage, [
            'sektor' => $kodeSektor,
            'reason' => $result?->reason?->value,
            'http_status' => $result?->httpStatus,
        ]);
    }
}
