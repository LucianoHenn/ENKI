<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobbers', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->string('class', 1024);
            $table->json('args');
            $table->foreignId('creator_id'); 
            $table->datetime('dispatched_at')->nullable();
            $table->datetime('run_at')->nullable();
            $table->datetime('finished_at')->nullable();
            $table->json('summary')->nullable();
            $table->json('error')->nullable();
            $table->json('log');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobbers');
    }
}
