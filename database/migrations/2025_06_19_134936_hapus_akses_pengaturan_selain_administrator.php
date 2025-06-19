<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
        $sql = <<<SQL
            delete from role_has_permissions where permission_id in (
                select id from permissions where name like 'pengaturan-settings%'
                union
                select id from permissions where name like 'pengaturan-identitas%'
                ) and role_id not in (
                select id from roles where name = 'administrator'
            )
SQL;
        DB::statement($sql);
        Artisan::call('cache:clear');
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
