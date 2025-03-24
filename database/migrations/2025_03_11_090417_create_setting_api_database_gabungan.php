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
        Setting::create([
            'key' => 'database_gabungan_api_key',
            'name' => 'Database Gabungan API Key',
            'value' => '',
            'type' => 'textarea',
            'description' => 'Pengaturan API Key Database Gabungan',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::whereIn('key', ['database_gabungan_api_key'])->delete();
    }
};
