<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceProviderConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_provider_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('service_provider_id');
            $table->foreignId('market_id');
            $table->boolean('status');
            $table->timestamps();


            $table->index(['service_provider_id', 'market_id'], 'sp_mk_idx');
            $table->index(['market_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_provider_configs');
    }
}
