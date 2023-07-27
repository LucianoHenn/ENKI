<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportLogbookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_logbooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('source', 100);
            $table->string('report_type', 50)->nullable();
            $table->string('family', 100)->nullable();

            $table->date('date_begin');
            $table->date('date_end');

            $table->char('market', 3)->default('all');

            $table->string('identifier', 255);
            $table->string('downloaded_path')->nullable();

            $table->json('info')->nullable();

            $table->integer('imported_records')->nullable();

            $table->integer('status_id')->nullable();
            $table->timestamp('last_attempt_at')->nullable();

            $table->tinyInteger('checked')->default(0);
            $table->timestamp('checked_at')->nullable();

            $table->timestamps();

            $table->unique( ['source', 'report_type', 'date_begin', 'date_end', 'identifier', 'market'], "source_type_date_unique");
            $table->index(['source', 'report_type', 'date_end']);

            $table->foreign('status_id')
                ->references('id')
                ->on('report_logbook_statuses')
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
        Schema::dropIfExists('report_logbooks');
    }
}
