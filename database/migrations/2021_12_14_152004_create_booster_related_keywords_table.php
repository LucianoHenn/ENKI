<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoosterRelatedKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booster_related_keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('keyword_id')->unsigned();
            $table->string('keyword');
            $table->decimal('score', 14, 4);
            $table->smallInteger('percentage')->default(0);
            $table->timestamps();

            $table->unique(['keyword_id', 'keyword']);

            $table->foreign('keyword_id')
                ->references('id')
                ->on('booster_keywords')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booster_related_keywords');
    }
}
