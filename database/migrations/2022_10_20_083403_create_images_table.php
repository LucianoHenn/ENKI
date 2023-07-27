<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('original_image_id')->default(0);
            $table->text('url');
            $table->string('hash','40')->unique();
            $table->string('perceptual_hash')->unique();
            $table->integer('width');
            $table->integer('height');
            $table->integer('size');
            $table->string('mimetype', 20);
            $table->json('info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
