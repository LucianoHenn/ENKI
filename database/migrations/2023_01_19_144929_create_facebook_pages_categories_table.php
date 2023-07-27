<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPagesCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_pages_categories', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('category_id');

            $table->timestamps();

            $table->unique(['page_id', 'category_id'], 'page_category_unique');
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
        Schema::dropIfExists('facebook_pages_categories');
    }
}
