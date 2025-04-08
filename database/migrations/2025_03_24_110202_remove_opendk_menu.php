<?php

use App\Models\Setting;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $menuBaru = [
            'icon' => 'far fa-fw fa-circle',
            'text' => 'OpenDK',
            'url' => 'pengaturan/opendk',
            'permission' => 'pengaturan-opendk',
        ];
        $team = Team::where('name', 'administrator')->first();
        $menuOrder = $team->menu_order ?? [];
        if (! $menuOrder) {
            return;
        }
        $menuOrderBaru = collect($menuOrder)->map(function ($item) use ($menuBaru) {
            if ($item['text'] === 'Pengaturan') {
                $item['submenu'] = collect($item['submenu'])->filter(function ($submenu) use ($menuBaru) {
                    return $submenu['url'] !== $menuBaru['url'];
                })->toArray();
            }

            return $item;
        })->toArray();

        $team->menu_order = $menuOrderBaru;
        $team->save();

        Setting::whereIn('key', ['opendk_synchronize', 'opendk_api_key'])->delete();
        Artisan::call('admin:menu-update');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
