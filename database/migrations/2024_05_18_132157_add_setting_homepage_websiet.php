<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attribute = [
            ['text' => 'Default', 'value' => 'default'],
            ['text' => 'Presisi', 'value' => 'presisi'],
        ];
        Setting::create([
            'key' => 'home_page',
            'name' => 'Pengaturan halaman utama website',
            'value' => 'default',
            'type' => 'dropdown',
            'attribute' => $attribute,
            'description' => 'Pengaturan halaman utama website',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('key', 'home_page')->delete();
    }
};
