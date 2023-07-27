<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnershipIdToFacebookAdCopiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_ad_copies', function (Blueprint $table) {
            $table->unsignedBigInteger('partnership_id')->nullable();
            $table->foreign('partnership_id')->references('id')->on('facebook_partnerships')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facebook_ad_copies', function (Blueprint $table) {
            $table->dropForeign(['partnership_id']);
            $table->dropColumn('partnership_id');
        });
    }
}
