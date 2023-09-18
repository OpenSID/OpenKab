<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->date('published_at');
            $table->string('slug');
            $table->string('title');
            $table->string('thumbnail')->nullable()->comment('gambar utama untuk artikel');
            $table->text('content');
            $table->tinyInteger('state', false, true)->default(0)->comment('aktif, non aktif');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['published_at', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
};
