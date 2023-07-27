<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleGeoTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_geo_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('criteria_id')->unique();
            $table->string('name');
            $table->string('canonical_name');
            $table->integer('parent_id');
            $table->char('country_code', 10);
            $table->string('target_type');
            $table->string('status');
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
        Schema::dropIfExists('google_geo_targets');
    }
}
