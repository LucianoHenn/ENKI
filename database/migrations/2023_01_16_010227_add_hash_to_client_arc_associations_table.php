<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHashToClientArcAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_arc_associations', function (Blueprint $table) {
            $table->string('hash', 64);
            $table->index(['hash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_arc_associations', function (Blueprint $table) {
            $table->dropColumn('hash');
        });
    }
}
