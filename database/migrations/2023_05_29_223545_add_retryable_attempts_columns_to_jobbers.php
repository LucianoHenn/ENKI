<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetryableAttemptsColumnsToJobbers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobbers', function (Blueprint $table) {
            $table->boolean('is_retryable')->default(false);
            $table->tinyInteger('attempts')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobbers', function (Blueprint $table) {
            $table->dropColumn('is_retryable');
            $table->dropColumn('attempts');
        });
    }
}
