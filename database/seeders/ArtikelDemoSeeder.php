<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Config;
use Illuminate\Database\Seeder;

class ArtikelDemoSeeder extends Seeder
{
    private $articles = [];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $randomConfig = Config::inRandomOrder()->limit(10)->get();
        if ($randomConfig) {
            foreach($randomConfig as $config){
                for($i = 0; $i < random_int(3, 10); $i++){
                    $this->articles[] = $this->generateArticle($config);
                }
            }
            Artikel::insert($this->articles);
        }
    }

    private function generateArticle($config) {
        return [
            'config_id' => $config->id,
            'isi' => fake()->paragraph(),
            'judul' => fake()->realText(50),
            'enabled' => 1,
            'headline' => 0,
            'id_kategori' => 1,
            'id_user' => 1
        ];
    }
}
