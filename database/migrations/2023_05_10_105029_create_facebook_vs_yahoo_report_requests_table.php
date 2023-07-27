<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookVsYahooReportRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_vs_yahoo_report_requests', function (Blueprint $table) {
            $table->id();

            $table->date('begin');
            $table->date('end');

            $table->json('info')->nullable();

            $table->enum('status', ['QUEUED', 'RUNNING', 'FAILED', 'COMPLETED', 'SENT'])->default('QUEUED');

            $table->json('response')->nullable();

            $table->timestamps();

            $table->unique(['begin', 'end']);
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
        Schema::dropIfExists('facebook_vs_yahoo_report_requests');
    }
}
