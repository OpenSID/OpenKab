<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /* untuk IdentitasSeeder dan CreateAdminUserSeeder sudah dipanggil dalam migration */
        $this->call(SettingSeeder::class);
        $this->call(SettingTemaSeeder::class);
    }
}
