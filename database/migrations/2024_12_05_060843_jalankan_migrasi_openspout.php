<?php

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
        try {
            // Jalankan perintah composer update
            $output = shell_exec('composer update openspout/openspout 2>&1');

            // Log output hasil perintah
            // Log::info('Composer Update Output: ' . $output);
        } catch (Exception $e) {
            // Log error jika ada masalah
            // Log::error('Error during composer update: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Anda bisa menambahkan rollback jika diperlukan
    }
};
