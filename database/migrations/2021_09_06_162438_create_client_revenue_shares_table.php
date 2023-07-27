<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientRevenueSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_revenue_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id');

            $table->string('provider', 20);
            $table->decimal('revenue_share', 10,6);

            $table->date('begin')->nullable()->default(null);
            $table->date('end')->nullable()->default(null);

            $table->timestamps();

            $table->index(['client_id', 'provider', 'begin', 'end']);
            $table->unique(['client_id', 'provider', 'end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_revenue_shares');
    }
}
