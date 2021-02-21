<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trader_id');

            $table->string('symbol', 32);
            $table->double('size');
            $table->unsignedDouble('invested');
            $table->unsignedDouble('leverage');
            $table->unsignedDouble('cost');
            $table->unsignedDouble('entry_price');
            $table->unsignedDouble('mark_price');
            $table->double('pnl');
            $table->double('roe');

            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('positions');
    }
}
