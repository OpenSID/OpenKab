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
            ['text' => 'Tidak Aktif', 'value' => 0],
            ['text' => 'Aktif', 'value' => 1],
        ];
        Setting::create([
            'key' => 'opendk_synchronize',
            'name' => 'Status',
            'value' => 0,
            'type' => 'dropdown',
            'attribute' => $attribute,
            'description' => 'Pengaturan sinkronisasi dengan OpenDK',
        ]);
        Setting::create([
            'key' => 'opendk_api_key',
            'name' => 'API Key',
            'value' => '',
            'type' => 'textarea',
            'description' => 'Pengaturan API Key Untuk Sinkronisasi OpenDK',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::whereIn('key', ['opendk_synchronize', 'opendk_api_key'])->delete();
    }
};
