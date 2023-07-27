<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPagesSitesMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes and uniques.
        // page site market
        // page market
        // site market
        Schema::create('facebook_pages_sites_markets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('market_id');

            $table->timestamps();

            $table->unique(['page_id', 'site_id', 'market_id']);
            $table->index(['page_id', 'market_id']);
            $table->index(['site_id', 'market_id']);

            $table->foreign('market_id')->references('id')->on('markets')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('facebook_sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_pages_sites_markets');
    }
}
