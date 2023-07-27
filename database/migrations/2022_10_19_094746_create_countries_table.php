<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->char('code', 3)->unique();
            $table->index(['name', 'code']);
            $table->timestamps();
        });

        Schema::create('countryables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries');
            $table->morphs('countryables');
            $table->unique(['country_id', 'countryables_id', 'countryables_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countryables');
        Schema::dropIfExists('countries');
    }
}
