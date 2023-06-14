<?php

use App\Models\Enums\StatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('password', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('last_login')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(StatusEnum::tidakAktif);
            $table->string('name', 50)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('foto', 100)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => 'CreateAdminUserSeeder',
            '--force' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
