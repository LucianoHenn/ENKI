<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYahooAssociations extends Migration
{

    protected $suffix_chars = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f'];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            foreach ($this->suffix_chars as $c1) {
                foreach ($this->suffix_chars as $c2) {
                    $table_name = 'yahoo_associations_' . $c1 . $c2;
                    Schema::connection('mysql_yahoo_associations')->create($table_name, function (Blueprint $table) {
                        $table->id();

                        $table->char('market', 3);

                        $table->string('hash', 42);
                        $table->date('date');
                        $table->json('info');

                        $table->timestamps();

                        $table->unique(['hash']);
                        $table->index(['date', 'market'], 'dt_mkt_idx');
                        $table->index(['market'], 'mkt_idx');
                    });
                }
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            //we throw an execption if it's not the duplicate table for the yahoo associations
            if (stripos($msg, '42S01') === FALSE) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->suffix_chars as $c1) {
            foreach ($this->suffix_chars as $c2) {
                $table_name = 'yahoo_associations_' . $c1 . $c2;
                Schema::connection('mysql_yahoo_associations')->dropIfExists($table_name);
            }
        }
    }
}
