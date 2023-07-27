<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueConstraintFromFacebookVsYahooReportRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_vs_yahoo_report_requests', function (Blueprint $table) {
            $table->dropUnique(['begin', 'end']);
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
            $table->unique(['begin', 'end']);
        });
    }
}
