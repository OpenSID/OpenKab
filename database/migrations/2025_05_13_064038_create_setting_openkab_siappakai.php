<?php

use App\Enums\Status;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $setting = Setting::where('key', 'sinkronisasi_database_gabungan')->first();

        if($setting){
            $setting->update([
                'key' => 'OpenKab_SiapPakai',
                'name' => 'OpenKab SiapPakai',
                'description' => 'Aktifkan Sinkronisasi ke OpenKab SiapPakai.',
            ]);
        }else{
            $attribute = [
                ['text' => Status::getDescription(Status::TidakAktif), 'value' => Status::TidakAktif],
                ['text' => Status::getDescription(Status::Aktif), 'value' => Status::Aktif],
            ];
    
            Setting::create([            
                'key' => 'OpenKab_SiapPakai',
                'name' => 'OpenKab SiapPakai',
                'value' => Status::TidakAktif,
                'type' => 'dropdown',
                'attribute' => $attribute,
                'description' => 'Aktifkan Sinkronisasi ke OpenKab SiapPakai.',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::whereIn('key', ['OpenKab_SiapPakai'])->delete();
    }
};
