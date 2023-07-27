<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_languages', function (Blueprint $table) {
            $table->id();
            $table->string('language_name', 100)->unique();
            $table->char('language_code', 10)->unique();
            $table->integer('criterion_id')->unsigned();
            $table->index(['language_name', 'language_code', 'criterion_id']);
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
        Schema::dropIfExists('google_languages');
    }
}
