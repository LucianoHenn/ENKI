<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientServiceProviderRelationShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_service_provider_relation_ships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id');
            $table->foreignId('client_id');
            $table->boolean('status');
            $table->timestamps();

            $table->index(['service_provider_id', 'client_id'], 'sp_cid_idx');
            $table->index(['client_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_service_provider_relation_ships');
    }
}
