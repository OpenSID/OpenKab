<?php

namespace Database\Seeders;

use App\Models\Bantuan;
use App\Models\BantuanPeserta;
use App\Models\Enums\SHDKEnum;
use App\Models\Kelompok;
use App\Models\Penduduk;
use Illuminate\Database\Seeder;

class BantuanDemoSeeder extends Seeder
{
    private $totalKelompok;

    private $sasaran = [
        // penduduk
        1 => [
            'Bantuan Pangan',
            'Bantuan Tunai',
            'Jaminan Kesehatan Nasional (JKN)',
            'Program Vaksinasi Massal',
            'Bantuan Obat-obatan',
            'Bantuan Kesejahteraan Lansia',
            'Bantuan Perlindungan Sosial Anak',
            'Bantuan Pendidikan',
            'Bantuan Perumahan',
            'Bantuan Pengangguran',
            'Bantuan Keterampilan Kerja',
            'Bantuan Kesehatan Jiwa',
            'Bantuan Modal Usaha',
            'Bantuan Perawatan Lanjut Usia',
            'Bantuan Pelatihan Profesi',
            'Bantuan Kesehatan Ibu dan Anak',
            'Bantuan Keuangan untuk Pelajar',
            'Bantuan Rehabilitasi Fisik',
            'Bantuan Konseling Psikologis',
            'Bantuan Pelatihan Kewirausahaan',
            'Bantuan Pekerja Migran',
            'Bantuan Keamanan Pangan',
            'Bantuan Kebutuhan Disabilitas',
            'Bantuan Kesehatan Gigi dan Mulut',
            'Bantuan Program Hamil Sehat',
        ],

        // keluarga
        2 => [
            'Bantuan Pangan',
            'Bantuan Tunai',
            'Bantuan Pendidikan',
            'Program Pendidikan Gratis',
            'Bantuan Kesehatan Anak',
            'Bantuan Perlindungan Sosial Keluarga',
            'Bantuan Kesehatan Ibu Hamil',
            'Bantuan Kesehatan Balita',
            'Bantuan Kesehatan Lansia',
            'Bantuan Perumahan Keluarga',
            'Bantuan Perlindungan Sosial Disabilitas',
            'Bantuan Kebutuhan Bayi',
            'Bantuan Kebutuhan Anak',
            'Bantuan Pelatihan Parenting',
            'Bantuan Penyuluhan Keluarga',
            'Bantuan Konseling Keluarga',
            'Bantuan Layanan Psikososial',
            'Bantuan Rehabilitasi Rumah',
            'Bantuan Pendidikan Nonformal',
            'Bantuan Pengasuhan Anak',
            'Bantuan Penempatan Kerja',
            'Bantuan Dana Usaha Keluarga',
            'Bantuan Kualitas Air Rumah',
            'Bantuan Pengelolaan Sampah',
            'Bantuan Perbaikan Infrastruktur',
        ],

        // rtm
        3 => [
            'Subsidi Listrik',
            'Subsidi Harga Bahan Pokok',
            'Kredit Usaha',
            'Pendampingan Usaha',
            'Pelatihan Kewirausahaan',
            'Bantuan Energi Terbarukan',
            'Bantuan Infrastruktur',
            'Bantuan Air Bersih',
            'Bantuan Sanitasi',
            'Bantuan Akses Internet',
            'Bantuan Pertanian',
            'Bantuan Perikanan',
            'Bantuan Perkebunan',
            'Bantuan Industri Kecil Menengah',
            'Bantuan Pemulihan Ekonomi Lokal',
            'Bantuan Pengembangan Produk',
            'Bantuan Pemasaran Produk',
            'Bantuan Keamanan Lingkungan',
            'Bantuan Peningkatan Kualitas Produk',
            'Bantuan Sertifikasi Produk',
            'Bantuan Promosi Produk',
            'Bantuan Pengolahan Produk',
            'Bantuan Teknologi Produksi',
            'Bantuan Kelembagaan Kelompok',
            'Bantuan Pengelolaan Keuangan',
        ],

        // kelompok
        4 => [
            'Bantuan Benih Unggul',
            'Bantuan Pupuk Subsidi',
            'Bantuan Alat Pertanian',
            'Pelatihan Pertanian Organik',
            'Bantuan Irigasi',
            'Bantuan Pengendalian Hama',
            'Bantuan Pemasaran Produk Tani',
            'Bantuan Peningkatan Kapasitas Kelompok Tani',
            'Bantuan Keuangan untuk Usaha Tani',
            'Bantuan Penyuluhan Pertanian',
            'Bantuan Pembangunan Infrastruktur Pertanian',
            'Bantuan Pendampingan Teknis Kelompok Tani',
            'Bantuan Pengolahan Hasil Pertanian',
            'Bantuan Program Peningkatan Produktivitas Tani',
            'Bantuan Program Diversifikasi Pertanian',
            'Bantuan Pembiayaan Investasi Pertanian',
            'Bantuan Pengelolaan Sumber Daya Alam Pertanian',
            'Bantuan Pengembangan Agribisnis',
            'Bantuan Penyediaan Sarana Produksi',
            'Bantuan Konservasi Lahan Pertanian',
            'Bantuan Program Perbaikan Infrastruktur Irigasi',
            'Bantuan Program Penyediaan Alat Pertanian Modern',
            'Bantuan Pelatihan Keahlian Pertanian',
            'Bantuan Program Pasar Tani',
            'Bantuan Program Riset Pertanian',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configId = config('seeder.wilayah.desa_aktif', 1);
        $name = config('seeder.wilayah.desa_nama_aktif', 'Desa kita');
        $this->init();
        $this->buatBantuan($configId);
        $this->command->info('Isi data bantuan untuk desa '.$name);
    }

    private function init()
    {
        $this->totalKelompok = Kelompok::count();
    }

    // Bantuan
    public function buatBantuan($configId)
    {
        $minProgram = config('seeder.bantuan.program_min', 1);
        $maxProgram = config('seeder.bantuan.program_max', 3);
        $jumlahProgram = fake()->numberBetween($minProgram, $maxProgram);

        for ($i = 1; $i <= $jumlahProgram; $i++) {
            $this->buatProgram($configId);
        }
    }

    // Program Bantuan
    public function buatProgram($configId)
    {
        $sasaran = fake()->randomElement([1, 1, 1, 1, 2, 2, 2, 3, 3, 4]);
        if ($sasaran == 4 && $this->totalKelompok == 0) {
            $sasaran = fake()->randomElement([1, 1, 1, 2, 2, 3]);
        }

        $nama = fake()->randomElement($this->sasaran[$sasaran]);
        $asalDana = [
            'Pusat' => 'Pusat',
            'Provinsi' => 'Provinsi',
            'Kab/Kota' => 'Kab/Kota',
            'Dana Desa' => 'Dana Desa',
            'Lain-lain (Hibah)' => 'Lain-lain (Hibah)',
        ];

        $data = [
            'config_id' => $configId,
            'nama' => $nama,
            'sasaran' => $sasaran,
            'ndesc' => fake()->paragraph(3),
            'sdate' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'edate' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'userid' => 0,
            'status' => 1,
            'asaldana' => fake()->randomElement($asalDana),
        ];
        $bantuan = Bantuan::create($data);

        // Peserta
        $minPeserta = config('seeder.bantuan.peserta_min', 5);
        $maxPeserta = config('seeder.bantuan.peserta_max', 50);
        $jumlahPeserta = fake()->numberBetween($minPeserta, $maxPeserta);

        $kecuali = [];
        foreach ($this->sasaran as $key => $val) {
            $kecuali[$key] = [];
        }

        for ($i = 0; $i < $jumlahPeserta; $i++) {
            $peserta = $this->buatPeserta($configId, $bantuan->id, $sasaran, $kecuali);
            if ($peserta) {
                $kecuali[$sasaran][] = $peserta;
            }
        }
    }

    // Peserta Bantuan
    public function buatPeserta($configId, $idProgram, $sasaran, $kecuali)
    {
        switch ($sasaran) {
            case 1:
                $penduduk = Penduduk::select(['id', 'nik', 'nama', 'tempatlahir', 'tanggallahir', 'alamat_sekarang'])
                    ->where('config_id', $configId)
                    ->whereNotIn('nik', $kecuali[$sasaran])
                    ->inRandomOrder()
                    ->first();

                $peserta = $penduduk->nik;
                break;

            case 2:
                $penduduk = Penduduk::select('tweb_penduduk.id', 'tweb_penduduk.nik', 'tweb_penduduk.nama', 'tweb_penduduk.tempatlahir', 'tweb_penduduk.tanggallahir', 'tweb_penduduk.alamat_sekarang', 'tweb_keluarga.no_kk')
                    ->join('tweb_keluarga', 'tweb_penduduk.id_kk', '=', 'tweb_keluarga.id')
                    ->where('tweb_penduduk.config_id', $configId)
                    ->whereIn('tweb_penduduk.kk_level', [SHDKEnum::KEPALA_KELUARGA, SHDKEnum::SUAMI, SHDKEnum::ISTRI, SHDKEnum::ANAK])
                    ->whereNotIn('tweb_keluarga.no_kk', $kecuali[$sasaran])
                    ->inRandomOrder()
                    ->first();

                $peserta = $penduduk?->no_kk ?? null;
                break;

            case 3:
                $penduduk = Penduduk::select('id', 'nik', 'nama', 'tempatlahir', 'tanggallahir', 'alamat_sekarang', 'id_rtm')
                    ->where('config_id', $configId)
                    ->whereNotNull('id_rtm')
                    ->whereNotIn('id_rtm', $kecuali[$sasaran])
                    ->inRandomOrder()
                    ->first();

                $peserta = $penduduk?->id_rtm ?? null;
                break;

            default:
                $peserta = null;
                break;
        }

        if ($penduduk) {
            // no_id_kartu = program_id + random 10 digit, jika 10 digit satuan maka tambahkan 0 di depan
            $noIdKartu = $idProgram.str_pad(fake()->numberBetween(1, 9999999999), 10, '0', STR_PAD_LEFT);

            $data = [
                'config_id' => $configId,
                'peserta' => $peserta,
                'program_id' => $idProgram,
                'no_id_kartu' => $noIdKartu,
                'kartu_nik' => $penduduk->nik,
                'kartu_nama' => $penduduk->nama,
                'kartu_tempat_lahir' => $penduduk->tempatlahir,
                'kartu_tanggal_lahir' => $penduduk->tanggallahir,
                'kartu_alamat' => $penduduk->alamat_sekarang ?? '',
                'kartu_id_pend' => $penduduk->id,
            ];

            BantuanPeserta::create($data);
        }

        return $peserta;
    }
}
