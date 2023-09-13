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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->date('published_at');
            $table->string('slug');
            $table->string('title');
            $table->string('thumbnail')->nullable()->comment('gambar utama untuk artikel');
            $table->text('content');
            $table->tinyInteger('state', false, true)->default(0)->comment('draft, terbitkan');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['published_at', 'slug']);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
