<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update menu untuk semua team yang ada
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $menu = $team->menu;
            
            // Cari menu "Pengaturan OpenSID"
            foreach ($menu as $key => $menuItem) {
                if ($menuItem['text'] === 'Pengaturan OpenSID') {
                    // Cari posisi "Kategori Artikel"
                    $submenu = $menuItem['submenu'];
                    $kategoriIndex = null;
                    
                    foreach ($submenu as $index => $sub) {
                        if ($sub['text'] === 'Kategori Artikel') {
                            $kategoriIndex = $index;
                            break;
                        }
                    }
                    
                    // Jika Kategori Artikel ditemukan, tambahkan Artikel setelahnya
                    if ($kategoriIndex !== null) {
                        $newSubmenu = [
                            'icon' => 'far fa-fw fa-circle',
                            'text' => 'Artikel',
                            'url' => 'master/artikel',
                            'permission' => 'master-data-artikel',
                        ];
                        
                        // Insert setelah Kategori Artikel
                        array_splice($submenu, $kategoriIndex + 1, 0, [$newSubmenu]);
                        $menu[$key]['submenu'] = $submenu;
                    }
                    
                    break;
                }
            }
            
            // Update menu team
            $team->update(['menu' => $menu]);
        }

        // Tidak perlu membuat permission baru karena sudah ada 'master-data-artikel'
        // Permission ini sudah digunakan untuk kategori artikel
        
        // Sync permission ke role yang sudah ada
        $roles = Role::all();
        foreach ($roles as $role) {
            // Pastikan role punya permission master-data-artikel
            if (!$role->hasPermissionTo('master-data-artikel-read')) {
                $permissions = [
                    'master-data-artikel-read',
                    'master-data-artikel-write',
                    'master-data-artikel-edit',
                    'master-data-artikel-delete',
                ];
                
                foreach ($permissions as $permissionName) {
                    $permission = Permission::where('name', $permissionName)->first();
                    if ($permission && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove menu artikel dari semua team
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $menu = $team->menu;
            
            foreach ($menu as $key => $menuItem) {
                if ($menuItem['text'] === 'Pengaturan OpenSID') {
                    $submenu = $menuItem['submenu'];
                    
                    // Hapus menu Artikel
                    $submenu = array_filter($submenu, function($sub) {
                        return $sub['text'] !== 'Artikel';
                    });
                    
                    $menu[$key]['submenu'] = array_values($submenu);
                    break;
                }
            }
            
            $team->update(['menu' => $menu]);
        }
    }
};
