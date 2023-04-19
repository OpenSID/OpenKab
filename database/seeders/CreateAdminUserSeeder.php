<?php

namespace Database\Seeders;

use App\Models\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return User::create([
            'username' => 'admin',
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => 'password',
            'active' => StatusEnum::aktif,
        ]);
    }
}
