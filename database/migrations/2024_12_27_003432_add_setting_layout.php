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
            ['text' => 'Horizontal', 'value' => 'horizontal'],
            ['text' => 'Vertikal', 'value' => 'vertikal'],
        ];
        Setting::create([
            'key' => 'layout_menu',
            'name' => 'Pengaturan layout menu yang tampil bisa vertikal atau horizontal',
            'value' => 'vertikal',
            'type' => 'dropdown',
            'attribute' => $attribute,
            'description' => 'Pengaturan layout menu yang tampil bisa vertikal atau horizontal',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('key', 'layout_menu')->delete();
    }
};
