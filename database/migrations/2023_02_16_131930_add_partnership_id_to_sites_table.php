<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnershipIdToSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_sites', function (Blueprint $table) {
            $table->unsignedBigInteger('partnership_id')->after('id')->nullable();

            $table->foreign('partnership_id')
                ->references('id')
                ->on('facebook_partnerships')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facebook_sites', function (Blueprint $table) {
            $table->dropForeign(['partnership_id']);
            $table->dropColumn('partnership_id');
        });
    }
}
