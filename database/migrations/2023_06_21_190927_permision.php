<?php

use App\Enums\Modul;
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
        $roles = Role::get();

        foreach (Modul::Menu as $main_menu) {
            // buat role

            $roles = Role::where('name', $main_menu['role'])->get();

            foreach (Modul::permision as $permision) {
                $name_permision = $main_menu['role'].'-'.$permision;
                $permision = Permission::create(['name' => $name_permision]);
                foreach ($roles as $role) {
                    $role->givePermissionTo($name_permision);
                }
            }

            if (isset($main_menu['submenu'])) {
                foreach ($main_menu['submenu'] as $sub_menu) {
                    $roles = Role::where('name', $sub_menu['role'])->get();
                    foreach (Modul::permision as $permision) {
                        $name_permision = $sub_menu['role'].'-'.$permision;
                        Permission::create(['name' => $name_permision]);
                        foreach ($roles as $role) {
                            $role->givePermissionTo($name_permision);
                        }
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
        //
    }
};
