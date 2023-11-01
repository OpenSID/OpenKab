<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ConfigDemoSeeder extends Seeder
{
    private $listKecamatan = [];

    private $listDesa = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kodeKabupaten = config('seeder.config.kode_kabupaten', '35.25');
        $this->getKecamatan($kodeKabupaten);
        if ($this->listKecamatan) {
            foreach ($this->listKecamatan as $kecamatan) {
                $this->getDesa($kecamatan['kode_kec'], $kecamatan['nama_kec']);
            }
        }

        if ($this->listDesa) {
            $this->command->info('mulai isi data tabel config');
            foreach ($this->listDesa as $desa) {
                $desa['kode_propinsi'] = $desa['kode_prov'];
                $desa['nama_propinsi'] = $desa['nama_prov'];
                Config::factory(1)->create([
                    'nama_desa' => $desa['nama_desa'],
                    'kode_desa' => $desa['kode_desa'],
                    'kode_pos' => fake()->postcode,
                    'nama_kecamatan' => $desa['nama_kec'],
                    'kode_kecamatan' => $desa['kode_kec'],
                    'nama_kabupaten' => $desa['nama_kab'],
                    'kode_kabupaten' => $desa['kode_kab'],
                    'nama_propinsi' => $desa['nama_prov'],
                    'kode_propinsi' => $desa['kode_prov'],
                ]);
                $this->command->info('data config desa '.$desa['nama_desa'].' berhasil dibuat');
            }
        }
    }

    private function getKecamatan($kodeKabupaten)
    {
        $this->command->info('ambil data kecamatan dari kabupaten dengan kode '.$kodeKabupaten.' dari pantau');
        $page = 1;
        $masihAda = true;
        $maxLoop = 20;
        do {
            $data = $this->ambilDataPantau($kodeKabupaten, $page);
            $this->listKecamatan = array_merge($this->listKecamatan, $data['results']);
            $masihAda = $data['pagination']['more'];
            $page++;
            // pastikan tidak terjadi infinity loop
            if ($page >= $maxLoop) {
                $masihAda = false;
            }
        } while ($masihAda);
    }

    private function getDesa($kodeKecamatan, $namaKecamatan)
    {
        $this->command->info('ambil data desa dari kecamatan '.$namaKecamatan.' dengan kode '.$kodeKecamatan.' dari pantau');
        $page = 1;
        $masihAda = true;
        $maxLoop = 20;
        do {
            $data = $this->ambilDataPantau($kodeKecamatan, $page);
            if ($data['results']) {
                $this->listDesa = array_merge($this->listDesa, $data['results']);
                $masihAda = $data['pagination']['more'];
                $page++;
            }
            // pastikan tidak terjadi infinity loop
            if ($page >= $maxLoop) {
                $masihAda = false;
            }
        } while ($masihAda);
    }

    private function ambilDataPantau($kode, $page = 1)
    {
        $serverPantau = config('app.serverPantau');
        $tokenPantau = config('app.tokenPantau');
        $urlCariDesa = $serverPantau.'index.php/api/wilayah/list_wilayah';

        try {
            $response = Http::withOptions([
                'debug' => false,
            ])->get($urlCariDesa, [
                'token' => $tokenPantau,
                'kode' => $kode,
                'page' => $page,
            ]);
            $data = $response->json();

            return $data;
        } catch (RequestException  $e) {
            // Menangani pengecualian, misalnya menampilkan pesan kesalahan
            // atau melakukan tindakan lain sesuai kebutuhan
            $this->command->error($e->getMessage());
            exit;
        }
    }
}
