<?php

namespace App\Console\Commands;

use App\Models\Bantuan;
use App\Models\BantuanPeserta;
use App\Models\ClusterDesa;
use App\Models\Config;
use App\Models\Kategori;
use App\Models\Keluarga;
use App\Models\LogKeluarga;
use App\Models\LogPenduduk;
use App\Models\Penduduk;
use App\Models\Rtm;
use Database\Seeders\BantuanDemoSeeder;
use Database\Seeders\ConfigDemoSeeder;
use Database\Seeders\KategoriDemoSeeder;
use Database\Seeders\KeluargaDemoSeeder;
use Database\Seeders\RTMDemoSeeder;
use Database\Seeders\WilayahDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateDataDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openkab:demo-data {--kodekabupaten=} {--minpenduduk=} {--maxpenduduk=}  {--reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate data dummy untuk openkab contoh php artisan openkab:demo-data --kodekabupaten=35.25 --minpenduduk=1000 --maxpenduduk=5000';

    private $listClass = [
        Config::class,
        ClusterDesa::class,
        Penduduk::class,
        Keluarga::class,
        LogPenduduk::class,
        LogKeluarga::class,
        Rtm::class,
        Bantuan::class,
        BantuanPeserta::class,
        Kategori::class,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $minPenduduk = $this->option('minpenduduk') ?? 100;
        $maxPenduduk = $this->option('minpenduduk') ?? $minPenduduk * 2;
        $kodekabupaten = $this->option('kodekabupaten');
        config()->set('seeder.config.kode_kabupaten', $kodekabupaten);
        DB::connection('openkab')->statement('SET foreign_key_checks=0');
        $this->reset();
        $this->seedConfig();
        config()->set('seeder.keluarga.anggota_min', 2);
        config()->set('seeder.keluarga.anggota_max', 5);
        config()->set('seeder.bantuan.program_min', 1);
        config()->set('seeder.bantuan.program_max', 3);
        config()->set('seeder.bantuan.peserta_min', 5);
        config()->set('seeder.bantuan.peserta_max', 20);
        Config::select(['id', 'nama_desa'])->get()->each(function($item) use ($minPenduduk, $maxPenduduk) {
            config()->set('seeder.penduduk.max', fake()->numberBetween($minPenduduk, $maxPenduduk));
            config()->set('seeder.wilayah.desa_aktif', $item->id);
            config()->set('seeder.wilayah.desa_nama_aktif', $item->nama_desa);
            $this->seedDataDesa();
        });
        $this->seedKategoriBerita();
        DB::connection('openkab')->statement('SET foreign_key_checks=1');

    }

    private function reset()
    {
        foreach ($this->listClass as $class) {
            $tableObj = new $class;
            $tableObj->truncate();
            $this->info('hapus data pada tabel '.$tableObj->getTable().' dan reset auto_increment berhasil');
        }
    }

    private function seedConfig()
    {
        $exitCode = Artisan::call('db:seed', ['--class' => ConfigDemoSeeder::class]);
        $output = Artisan::output();
        Log::error($output);
        $this->info($output);
    }

    private function seedDataDesa()
    {
        $this->seedWilayah();
        $this->seedKeluarga();
        $this->seedRumahTangga();
        $this->seedBantuan();
    }

    private function seedWilayah()
    {
        $exitCode = Artisan::call('db:seed', ['--class' => WilayahDemoSeeder::class]);
        $output = Artisan::output();
        Log::info($output);
        $this->info($output);
    }

    private function seedKeluarga()
    {
        $exitCode = Artisan::call('db:seed', ['--class' => KeluargaDemoSeeder::class]);
        $output = Artisan::output();
        Log::info($output);
        $this->info($output);
    }

    private function seedRumahTangga()
    {
        $exitCode = Artisan::call('db:seed', ['--class' => RTMDemoSeeder::class]);
        $output = Artisan::output();
        Log::info($output);
        $this->info($output);
    }

    private function seedBantuan()
    {
        $exitCode = Artisan::call('db:seed', ['--class' => BantuanDemoSeeder::class]);
        $output = Artisan::output();
        Log::info($output);
        $this->info($output);
    }

    private function seedKategoriBerita()
    {
        $exitCode = Artisan::call('db:seed', ['--class' => KategoriDemoSeeder::class]);
        $output = Artisan::output();
        Log::info($output);
        $this->info($output);
    }
}
