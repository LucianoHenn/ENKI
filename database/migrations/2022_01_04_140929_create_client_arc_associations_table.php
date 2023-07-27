<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientArcAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_arc_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id', 10);
            $table->foreignId('market_id', 10);
            $table->string('source', 50);
            $table->string('in_source', 50)->nullable();
            $table->enum('source_type', ['cost', 'revenue']);
            $table->enum('device', ['all', 'mobile', 'tablet', 'desktop']);
            $table->json('info');
            $table->boolean('status')->default(true);
            $table->date('begin')->nullable();
            $table->date('end')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'source', 'begin', 'end'], 'cid_src_bd_end_idx');
            $table->index(['source', 'begin', 'end'], 'src_bd_end_idx');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_arc_associations');
    }
}
