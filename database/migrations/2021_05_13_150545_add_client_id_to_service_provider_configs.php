<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientIdToServiceProviderConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_provider_configs', function (Blueprint $table) {
            $table->foreignId('client_id');
            $table->index(['client_id', 'service_provider_id', 'market_id'], 'c_sp_mk_idx');
            $table->index(['client_id', 'market_id'], 'c_mkt_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_provider_configs', function (Blueprint $table) {
            $table->dropColumn('client_id');
            $table->dropIndex('c_sp_mk_idx');
            $table->dropIndex('c_mkt_idx');
        });
    }
}
