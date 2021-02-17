<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traders', function (Blueprint $table) {
            $table->id();

            $table->string('uid', 32);
            $table->string('name');
            $table->boolean('sharing');
            $table->string('twitter');

            $table->unsignedSmallInteger('ranking');
            $table->unsignedSmallInteger('ranking_day');
            $table->unsignedSmallInteger('ranking_week');
            $table->unsignedSmallInteger('ranking_month');

            $table->unsignedSmallInteger('roi');
            $table->unsignedSmallInteger('roi_day');
            $table->unsignedSmallInteger('roi_week');
            $table->unsignedSmallInteger('roi_month');

            $table->unsignedSmallInteger('pnl');
            $table->unsignedSmallInteger('pnl_day');
            $table->unsignedSmallInteger('pnl_week');
            $table->unsignedSmallInteger('pnl_month');

            $table->dateTime('unshared_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traders');
    }
}
