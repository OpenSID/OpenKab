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
            'key' => 'openkab_api_baseurl',
            'name' => 'Pengaturan OpenKAB API Base URL',
            'value' => preg_replace('/^https?:\/\//', '$0api.', env('APP_URL')),
            'type' => 'text',
            'attribute' => null,
            'description' => 'Pengaturan OpenKA API Base URL',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('key', 'openkab_api_baseurl')->delete();
    }
};
