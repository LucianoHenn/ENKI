<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPagesPartnershipsMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_pages_partnerships_markets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('partnership_id');
            $table->unsignedBigInteger('market_id');

            $table->timestamps();

            $table->unique(['page_id', 'partnership_id', 'market_id'], 'unique_page_partnership_market');
            $table->index(['page_id', 'market_id'], 'index_page_market');
            $table->index(['partnership_id', 'market_id'], 'index_partnership_market');

            $table->foreign('market_id')->references('id')->on('markets')->onDelete('cascade');
            $table->foreign('partnership_id')->references('id')->on('facebook_partnerships')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_pages_partnerships_markets');
    }
}
