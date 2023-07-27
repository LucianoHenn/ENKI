<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 200);
            $table->string('english_translation', 200);
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('language_id')->constrained('languages');
            $table->unique(['keyword','language_id', 'country_id']);
            $table->index(['country_id', 'language_id']);
            $table->timestamps();
        });

        Schema::create('keywordables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords');
            $table->morphs('keywordable');
            $table->unique(['keyword_id', 'keywordable_id', 'keywordable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keywordables');
        Schema::dropIfExists('keywords');
    }
}
