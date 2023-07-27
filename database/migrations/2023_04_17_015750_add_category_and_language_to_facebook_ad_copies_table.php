<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryAndLanguageToFacebookAdCopiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_ad_copies', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();

            $table->index('category_id');
            $table->index('language_id');
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
            $table->dropColumn('category_id');
            $table->dropColumn('language_id');

            $table->dropIndex('facebook_ad_copies_category_id_index');
            $table->dropIndex('facebook_ad_copies_language_id_index');
        });
    }
}
