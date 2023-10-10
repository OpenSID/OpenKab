<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class WilayahDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configId = config('seeder.wilayah.desa_aktif', 1);
        $name = config('seeder.wilayah.desa_nama_aktif', 'Desa kita');
        $this->buatWilayah($configId);
        $this->command->info('Isi data wilayah untuk desa '.$name);
    }

    // Wilayah
    private function buatWilayah($configId)
    {
        // Dusun
        $minDusun = config('seeder.wilayah.dusun_min', '1');
        $maxDusun = config('seeder.wilayah.dusun_max', '8');
        $jumlahDusun = fake()->numberBetween($minDusun, $maxDusun);

        $uniqueNames = [];
        $maxRetries = 100;
        foreach (range(1, $jumlahDusun) as $index) {
            $name = fake()->word(45);
            $retry = 1;
            while (in_array($name, $uniqueNames) && $retry <= $maxRetries) {
                $name = fake()->word(45);
                $retry++;
            }
            $uniqueNames[] = $name;
        }
        foreach ($uniqueNames as $key => $namaDusun) {
            $dusun[] = $this->buatDusun($configId, $namaDusun, ($key + 1));
        }

        $wilayah = collect($dusun)->flatten(1)->toArray();

        Wilayah::insert($wilayah);
    }

    // Wilayah Dusun
    private function buatDusun($configId, $namaDusun, $urutDusun)
    {
        $dusun = [
            [
                'config_id' => $configId,
                'rt' => 0,
                'rw' => 0,
                'dusun' => $namaDusun,
            ],
            [
                'config_id' => $configId,
                'rt' => 0,
                'rw' => '-',
                'dusun' => $namaDusun,
            ],
            [
                'config_id' => $configId,
                'rt' => '-',
                'rw' => '-',
                'dusun' => $namaDusun,
            ],
        ];

        // RW
        $minRW = config('seeder.wilayah.rw_min', '1');
        $maxRW = config('seeder.wilayah.rw_max', '5');
        $jumlahRW = fake()->numberBetween($minRW, $maxRW);

        $rw = [];

        for ($i = 1; $i <= $jumlahRW; $i++) {
            $rw[] = $this->buatRW($configId, $namaDusun, $urutDusun, $i);
        }

        return collect($dusun)->merge(collect($rw)->flatten(1))->all();
    }

    // Wilayah RW
    private function buatRW($configId, $namaDusun, $urutDusun, $urutRW)
    {
        $namaRW = $urutDusun.$urutRW;

        $rw = [
            [
                'config_id' => $configId,
                'rt' => 0,
                'rw' => $namaRW,
                'dusun' => $namaDusun,
            ],
            [
                'config_id' => $configId,
                'rt' => '-',
                'rw' => $namaRW,
                'dusun' => $namaDusun,
            ],
        ];

        // RW
        $minRT = config('seeder.wilayah.rt_min', '1');
        $maxRT = config('seeder.wilayah.rt_max', '6');
        $jumlahRT = fake()->numberBetween($minRT, $maxRT);

        $rt = [];

        for ($i = 1; $i <= $jumlahRT; $i++) {
            $rt[] = $this->buatRT($configId, $namaDusun, $namaRW, $i);
        }

        return collect($rw)->merge(collect($rt)->flatten(1))->all();
    }

    // Wilayah RT
    private function buatRT($configId, $namaDusun, $namaRW, $urutRT)
    {
        $namaRT = $namaRW.$urutRT;

        return [
            [
                'config_id' => $configId,
                'rt' => $namaRT,
                'rw' => $namaRW,
                'dusun' => $namaDusun,
            ],
        ];
    }
}
