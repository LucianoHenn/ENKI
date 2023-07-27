<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_provider_id');
            $table->foreignId('client_id');

            $table->json('request')->nullable();

            $table->enum('status', ['QUEUED', 'RUNNING', 'FAILED', 'SUCCESS'])->default('QUEUED');
            $table->json('response')->nullable();

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
        Schema::dropIfExists('client_reports');
    }
}
