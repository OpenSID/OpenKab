<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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

        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 100)->after('id');;
            });

            foreach (User::all() as $value) {
                $user = User::find($value->id);
                $user->username = fake()->unique()->firstName();
                $user->save();
            }

            //ubah unique
            Schema::table('users', function ($table) {
                $table->unique('username');
            });
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
