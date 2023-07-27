<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoosterKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booster_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('device', 25);
            $table->string('identifier', 50);
            $table->char('market_code', 2);
            $table->string('revenue_partner', 50);
            $table->string('keyword');
            $table->timestamp('race_started_at')->nullable();
            $table->timestamp('race_processed_at')->nullable();
            $table->timestamps();

            $table->unique(['identifier', 'device', 'market_code', 'keyword', 'revenue_partner'], 'kp_keywords_unique');
            $table->index(['revenue_partner']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booster_keywords');
    }
}
