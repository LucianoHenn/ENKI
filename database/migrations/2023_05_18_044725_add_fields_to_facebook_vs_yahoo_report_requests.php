<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToFacebookVsYahooReportRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_vs_yahoo_report_requests', function (Blueprint $table) {
            Schema::table('facebook_vs_yahoo_report_requests', function (Blueprint $table) {
                $table->foreignId('client_id');
                $table->unique(['client_id', 'begin', 'end']);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facebook_vs_yahoo_report_requests', function (Blueprint $table) {
            $table->dropUnique(['client_id', 'begin', 'end']);
            $table->dropForeign(['client_id']);
        });
    }
}
