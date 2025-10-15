<?php

namespace App\Console\Commands;

use App\Enums\Modul;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class updateAdminMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:menu-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update default menu administrator';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('cache:clear');
        $team = Team::whereName('administrator')->first();
        setPermissionsTeamId($team->id);
        if ($team) {
            $team->menu = Modul::Menu;
            if(Schema::hasColumn('teams', 'menu_order')){
                $team->menu_order = null;
            }

            $team->save();

            $role = Role::firstOrCreate(
                [
                    'name' => 'administrator',
                    'team_id' => $team->id,
                    'guard_name' => 'web',
                ]
            );

            $permissions = $this->collectPermissions();
            $role->syncPermissions($permissions);
        }

        return Command::SUCCESS;
    }

    private function collectPermissions()
    {
        $permissions = [];
        foreach (Modul::Menu as $main_menu) {
            foreach (Modul::permision as $permission) {
                $menuPermission = $main_menu['permission'] ?? null;
                if(empty($menuPermission)){
                    continue;
                }
                $permissionName = $main_menu['permission'].'-'.$permission;
                Permission::findOrCreate($permissionName, 'web');
                $permissions[] = $permissionName;
            }
            if (isset($main_menu['submenu'])) {
                foreach ($main_menu['submenu'] as $sub_menu) {
                    foreach (Modul::permision as $permission) {
                        $subMenuPermission = $sub_menu['permission'] ?? null;
                        if(empty($subMenuPermission)){
                            continue;
                        }
                        $permissionName = $sub_menu['permission'].'-'.$permission;
                        Permission::findOrCreate($permissionName, 'web');
                        $permissions[] = $permissionName;
                    }
                }
            }
        }

        return $permissions;
    }
}
