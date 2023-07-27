<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoosterKeywordStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booster_keyword_stats', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('order_prefix');
            $table->date('date');
            $table->integer('hour');
            $table->datetime('stats_timestamp');
            $table->string('revenue_partner', 50);

            $table->string('input_keyword');
            $table->string('keyword');

            $table->string('device', 25);
            $table->string('identifier', 50);
            $table->string('market', 25);

            $table->decimal('revenue_share', 8, 4);
            $table->decimal('gross_revenue', 10, 4);

            $table->unsignedInteger('impressions');
            $table->unsignedInteger('clicks');

            $table->string('hash', 60)->unique();


            $table->timestamps();


            $table->index(
                ['order_prefix', 'revenue_partner', 'stats_timestamp', 'input_keyword', 'identifier'],
                'ord_revp_ts_ikw_idf'
            );
            $table->index(['order_prefix', 'revenue_partner', 'input_keyword', 'identifier'], 'ord_revp_ikw_idf');
            $table->index(['revenue_partner', 'input_keyword', 'identifier'], 'revp_ikw_idf');
            $table->index(['input_keyword', 'identifier'], 'ikw_idf');
            $table->index(
                ['order_prefix', 'revenue_partner', 'market',
                'device',  'input_keyword', 'identifier', 'stats_timestamp'],
                'ord_revp_mkt_dev_src_ikw_idf_ts'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keyword_stats');
    }
}
