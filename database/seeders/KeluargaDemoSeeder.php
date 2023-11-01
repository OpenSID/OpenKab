<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\Enums\JenisKelaminEnum;
use App\Models\Enums\SHDKEnum;
use App\Models\GolonganDarah;
use App\Models\KelasSosial;
use App\Models\Keluarga;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use App\Models\PendidikanKK;
use App\Models\Penduduk;
use App\Models\StatusKawin;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeluargaDemoSeeder extends Seeder
{
    private $totalStatusKawin;

    private $totalAgama;

    private $totalPendidikan;

    private $totalPendidikanKK;

    private $totalPekerjaan;

    private $totalGolonganDarah;

    private $dataWilayah;

    private $totalKeluargaSejahtera = 0;

    private $totalPenduduk = 0;

    private $kodeKecamatan;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configId = config('seeder.wilayah.desa_aktif', 1);
        $name = config('seeder.wilayah.desa_nama_aktif', 'Desa kita');
        $this->kodeKecamatan = Config::find($configId)->kode_kec;
        $this->init($configId);
        $this->buatKeluarga($configId);
        $this->generateLogPenduduk($configId);
        $this->generateLogKeluarga($configId);
        $this->command->info('Isi data keluarga untuk desa '.$name);
    }

    private function init($configId)
    {
        $this->dataWilayah = Wilayah::whereConfigId($configId)->pluck('id');
        $this->totalStatusKawin = StatusKawin::count();
        $this->totalPekerjaan = Pekerjaan::count();
        $this->totalPendidikanKK = PendidikanKK::count();
        $this->totalPendidikan = Pendidikan::count();
        $this->totalGolonganDarah = GolonganDarah::count();
        $this->totalKeluargaSejahtera = KelasSosial::count();
    }

    private function buatKeluarga($configId)
    {
        $maxPenduduk = config('seeder.penduduk.max', 1000);
        $jumlahKeluarga = fake()->numberBetween(intval($maxPenduduk / 3), $maxPenduduk / 2);

        for ($i = 1; $i <= $jumlahKeluarga; $i++) {
            $this->buatSatuKeluargaLengkap($configId, $i);
            if ($this->totalPenduduk >= $maxPenduduk) {
                break;
            }
        }
    }

    private function buatSatuKeluargaLengkap($configId, $urut)
    {
        $idCluster = $this->dataWilayah->random(1)->first();
        // buat kepala keluarga
        $kepalaKeluarga = $this->buatIndividu($configId, SHDKEnum::KEPALA_KELUARGA, $idCluster);

        // buat keluarga
        // urut 4 digit, jika kurang dari 4 digit, tambahkan 0 di depan
        $urut = str_pad($urut, 4, '0', STR_PAD_LEFT);
        $tglRekam = fake()->dateTimeBetween('-5 years')->format('Y-m-d');

        // No KK diambil dari kode kecamatan + 6 digit tgl rekam + 4 digit nomer urut random
        $tglKK = substr($tglRekam, 8, 2).substr($tglRekam, 5, 2).substr($tglRekam, 2, 2);
        $noKk = $this->kodeKecamatan.$tglKK.$urut;

        Keluarga::create([
            'config_id' => $configId,
            'no_kk' => $noKk,
            'nik_kepala' => $kepalaKeluarga->id,
            'tgl_daftar' => $tglRekam,
            'kelas_sosial' => fake()->numberBetween(1, $this->totalKeluargaSejahtera),
            'alamat' => $kepalaKeluarga->alamat_sekarang,
            'id_cluster' => $idCluster,
            'updated_by' => 1,
        ]);
        // buat anggota keluarga
        $minAnggotaKeluarga = config('seeder.keluarga.anggota_min', 1);
        $maxAnggotaKeluarga = config('seeder.keluarga.anggota_max', 2);
        if ($minAnggotaKeluarga > 1) {
            $this->buatAnggotaKeluarga($configId, $kepalaKeluarga, $maxAnggotaKeluarga);
        }
    }

    private function buatAnggotaKeluarga($configId, $kepalaKeluarga, $jumlahAnggota = 0)
    {
        $adaPasangan = 0;
        $i = 0;
        while ($i < $jumlahAnggota) {
            // Apakah ada pasangan?
            if ($kepalaKeluarga->status_kawin != 1 && fake()->boolean() && ! $adaPasangan) {
                $this->buatIndividu($configId, $kepalaKeluarga->sex == JenisKelaminEnum::laki_laki ? SHDKEnum::ISTRI : SHDKEnum::SUAMI, $kepalaKeluarga->id_cluster);
                $adaPasangan = 1;
                $i++;
            }

            if ($kepalaKeluarga->status_kawin != 1 && fake()->boolean()) {
                $this->buatIndividu($configId, SHDKEnum::ANAK, $kepalaKeluarga->id_cluster);
                $i++;
            }

            if (fake()->boolean()) {
                $statusLainnya = fake()->randomElement([5, 5, 6, 7, 7, 8, 9, 9, 9, 10, 11, 11]);
                $this->buatIndividu($configId, $statusLainnya, $kepalaKeluarga->id_cluster);
                $i++;
            }
        }
    }

    // Penduduk
    private function buatIndividu($configId, $kkLevel, $idCluster, $statusKawin = null)
    {
        // Buat data Penduduk
        if ($kkLevel === SHDKEnum::ISTRI) {
            $sex = JenisKelaminEnum::perempuan;
            $nameForSex = 'female';
        } else {
            $sex = fake()->randomElement([1, 1, 2, 1, 1, 2, 1, 1, 2, 1]);
            $nameForSex = $sex === 1 ? 'male' : 'female';
        }

        // tanggal lahir anak harus 17 tahun lebih kecil dari ayah atau ibu
        if ($kkLevel === SHDKEnum::ANAK) {
            $tanggallahir = fake()->dateTimeBetween('-17 years', '-1 years')->format('Y-m-d');
        } else {
            $tanggallahir = fake()->dateTimeBetween('-70 years', '-17 years')->format('Y-m-d');
        }

        // status kawin
        if ($kkLevel === SHDKEnum::ANAK || $kkLevel === SHDKEnum::CUCU) {
            $statusKawin = 1;
        } elseif (in_array($kkLevel, [SHDKEnum::SUAMI, SHDKEnum::ISTRI])) {
            $statusKawin = 2;
        } else {
            $statusKawin = fake()->numberBetween(2, $this->totalStatusKawin);
        }

        // NIK diambil dari kode kecamatan + 6 digit tgl lahir + 4 digit nomer urut
        // ambil 6 digit tanggal lahir, jika perempuan + 40
        $tglAwal = substr($tanggallahir, 8, 2);
        if ($sex === 2) {
            $tglAwal = $tglAwal + 40;
        }
        $tglLahir = $tglAwal.substr($tanggallahir, 5, 2).substr($tanggallahir, 2, 2);
        $rand = str_pad(fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
        $nik = $this->kodeKecamatan.$tglLahir.$rand;

        $data = [
            'config_id' => $configId,
            'nik' => $nik,
            'nama' => fake()->name($nameForSex),
            'id_kk' => 0,
            'kk_level' => $kkLevel,
            'id_rtm' => 0,
            'rtm_level' => 0,
            'sex' => $sex,
            'tempatlahir' => fake()->city,
            'tanggallahir' => $tanggallahir,
            'agama_id' => fake()->numberBetween(1, $this->totalAgama),
            'pendidikan_kk_id' => fake()->numberBetween(1, $this->totalPendidikanKK),
            'pendidikan_sedang_id' => fake()->numberBetween(1, $this->totalPendidikan),
            'pekerjaan_id' => fake()->numberBetween(1, $this->totalPekerjaan),
            'status_kawin' => $statusKawin,
            'id_cluster' => $idCluster,
            'warganegara_id' => 1,
            'alamat_sekarang' => fake()->address,
            'ayah_nik' => $this->kodeKecamatan.fake()->numberBetween(1000000000, 9999999999),
            'nama_ayah' => fake()->name('male'),
            'ibu_nik' => $this->kodeKecamatan.fake()->numberBetween(1000000000, 9999999999),
            'nama_ibu' => fake()->name('female'),
            'golongan_darah_id' => fake()->numberBetween(1, $this->totalGolonganDarah),
            'status' => 1,
            'status_dasar' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ];
        $penduduk = Penduduk::create($data);
        $this->totalPenduduk++;

        return $penduduk;
    }

    private function generateLogPenduduk($configId)
    {
        $sql = "insert into log_penduduk (config_id, id_pend, kode_peristiwa, tgl_lapor, updated_by)
                select {$configId} as config_id, id as id_pend, 5 as kode_peristiwa, DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 5 YEAR), INTERVAL FLOOR(RAND() * DATEDIFF(DATE_SUB(CURDATE(), INTERVAL 5 YEAR), CURDATE())) DAY) AS tgl_lapor, 1 as updated_by
                from tweb_penduduk where config_id = '{$configId}'";
        DB::connection('openkab')->statement($sql);
    }

    private function generateLogKeluarga($configId)
    {
        $sql = "insert into log_keluarga (config_id, id_kk, id_peristiwa, tgl_peristiwa, updated_by)
                select {$configId} as config_id, id as id_kk, 1 as id_peristiwa, tgl_daftar as tgl_peristiwa, 1 as updated_by
                from tweb_keluarga where config_id = '{$configId}'";
        DB::connection('openkab')->statement($sql);
    }
}
