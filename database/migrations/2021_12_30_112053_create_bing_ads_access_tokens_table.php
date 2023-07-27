<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBingAdsAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bing_ads_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('application', 30)->unique();
            $table->text('refresh_token');
            $table->text('access_token');
            $table->string('token_type', 30)->nullable();
            $table->integer('expires_in')->nullable();
            $table->string('scope', 100)->nullable();
            $table->text('id_token')->nullable();
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
        Schema::dropIfExists('bing_ads_access_tokens');
    }
}
