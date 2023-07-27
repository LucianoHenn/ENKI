<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYadsEPNRReportRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yads_e_p_n_r_report_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id');
            $table->date('begin');
            $table->date('end');
            

            $table->json('info')->nullable();
            $table->json('email_addresses')->nullable();

            $table->enum('status', ['QUEUED', 'RUNNING', 'FAILED', 'COMPLETED', 'SENT'])->default('QUEUED');
            
            $table->json('response')->nullable();

            $table->timestamps();

            $table->unique(['client_id', 'begin', 'end']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yads_e_p_n_r_report_requests');
    }
}
