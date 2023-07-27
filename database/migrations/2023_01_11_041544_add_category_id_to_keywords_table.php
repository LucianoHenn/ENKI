<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToKeywordsTable extends Migration
{
    public function up()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->index()->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    public function down()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}
