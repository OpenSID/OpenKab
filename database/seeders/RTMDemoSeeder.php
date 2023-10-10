<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\Enums\HubunganRTMEnum;
use App\Models\Enums\SHDKEnum;
use App\Models\KelasSosial;
use App\Models\Penduduk;
use App\Models\Rtm;
use Illuminate\Database\Seeder;

class RTMDemoSeeder extends Seeder
{
    private $kodeDesa;
    private $totalKeluargaSejahtera;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configId = config('seeder.wilayah.desa_aktif', 1);
        $name = config('seeder.wilayah.desa_nama_aktif', 'Desa kita');
        $this->kodeDesa = Config::find($configId)->kode_desa;
        $this->init();
        $this->buatRumahTangga($configId);
        $this->command->info('Isi data RTM untuk desa '.$name);
    }

    private function init(){
        $this->totalKeluargaSejahtera = KelasSosial::count();
    }

    public function buatRumahTangga($configId)
    {
        // ambil data dari penduduk dengan status kk_level 1 hanya data id, id_kk dan created_at
        $penduduk = Penduduk::select(['id', 'id_kk', 'created_at'])
            ->where('config_id', $configId)
            ->where('kk_level', SHDKEnum::KEPALA_KELUARGA)
            ->inRandomOrder()
            ->get();

        $noRtm = null;

        foreach ($penduduk as $urut => $pend) {
            // buat baru atau gabungkan? 1= buat baru, 2= gabungkan, kemungkinan buat baru 60 % sisanya digabungkan
            $gabungkanRtm = fake()->randomElement([1, 2, 1, 1, 2, 1, 2, 1, 1, 2]);
            if ($gabungkanRtm == 2 && $noRtm != null) {
                Penduduk::where('config_id', $configId)
                    ->where('id_kk', $pend->id_kk)
                    ->update([
                        'id_rtm' => $noRtm,
                        'rtm_level' => HubunganRTMEnum::ANGGOTA,
                    ]);
                $noRtm = null;
            } else {
                $rtm = $this->buatAnggotaRtm($configId, $urut + 1, $pend->id, $pend->id_kk, $pend->created_at);

                $noRtm = $rtm['no_kk'];
            }
        }
    }

    // Anggota Rumah Tangga
    public function buatAnggotaRtm($configId, $urut, $nikKepala, $idKK, $tglDaftar)
    {
        // no_rtm diambil dari kode desa + 4 digit urut, jika urut kurang dari 4 digit, tambahkan 0 di depan
        $noRtm = $this->kodeDesa.str_pad($urut, 4, '0', STR_PAD_LEFT);

        // apakah ada bdt? kemungkinan mengisi 80%
        if (fake()->randomElement([1, 1, 1, 1, 2, 1, 1, 1, 1, 2]) == 1) {
            // $bdt 16 digit random, jika kurang dari 16 digit, tambahkan 0 di depan
            $bdt = str_pad(fake()->numberBetween(1, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } else {
            $bdt = null;
        }

        $data = [
            'config_id' => $configId,
            'nik_kepala' => $nikKepala,
            'no_kk' => $noRtm,
            'tgl_daftar' => $tglDaftar,
            'kelas_sosial' => fake()->numberBetween(1, $this->totalKeluargaSejahtera),
            'bdt' => $bdt,
        ];

        $rtm = Rtm::create($data);

        // Update semua anggota keluarga kk_level = 1
        Penduduk::where('config_id', $configId)
            ->where('kk_level', SHDKEnum::KEPALA_KELUARGA)
            ->where('id_kk', $idKK)
            ->update([
                'id_rtm' => $noRtm,
                'rtm_level' => HubunganRTMEnum::KEPALA_RUMAH_TANGGA,
            ]);

        // Update semua anggota keluarga selain kk_level = 1
        Penduduk::where('config_id', $configId)
            ->where('config_id', $configId)
            ->where('kk_level', '!=', SHDKEnum::KEPALA_KELUARGA)
            ->where('id_kk', $idKK)
            ->update([
                'id_rtm' => $noRtm,
                'rtm_level' => HubunganRTMEnum::ANGGOTA,
            ]);

        return $data;
    }
}
