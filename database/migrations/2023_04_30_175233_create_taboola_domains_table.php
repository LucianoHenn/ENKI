<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaboolaDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taboola_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('domain');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->unsignedBigInteger('partnership_id')->nullable();
            $table->timestamps();

            $table->foreign('partnership_id')
                ->references('id')
                ->on('taboola_partnerships')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taboola_domains');
    }
}
