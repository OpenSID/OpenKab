<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        update team set menu = REPLACE(menu,'"Group"','"Grup"')
SQL;
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = <<<SQL
        update team set menu = REPLACE(menu,'"Grup"','"Group"')
SQL;
        DB::statement($sql);
    }
};
