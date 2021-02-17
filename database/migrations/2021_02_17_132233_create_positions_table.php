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
            $table->double('invested');
            $table->unsignedTinyInteger('leverage');
            $table->double('cost');
            $table->double('entry_price');
            $table->double('current_price');
            $table->double('pnl');
            $table->double('roe');

            $table->dateTime('provider_updated_at');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at');

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
        Schema::dropIfExists('positions');
    }
}
