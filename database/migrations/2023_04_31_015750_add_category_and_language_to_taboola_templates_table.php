<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryAndLanguageToTaboolaTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taboola_templates', function (Blueprint $table) {
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
        Schema::table('taboola_templates', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropColumn('language_id');

            $table->dropIndex('taboola_templates_category_id_index');
            $table->dropIndex('taboola_templates_language_id_index');
        });
    }
}
