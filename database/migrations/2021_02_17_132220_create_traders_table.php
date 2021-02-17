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
            $table->string('nick');
            $table->boolean('sharing');
            $table->string('twitter')->nullable();

            $table->double('roi');
            $table->double('roi_day');
            $table->double('roi_week');
            $table->double('roi_month');

            $table->double('pnl');
            $table->double('pnl_day');
            $table->double('pnl_week');
            $table->double('pnl_month');

            $table->unsignedInteger('rank_roi');
            $table->unsignedInteger('rank_roi_day');
            $table->unsignedInteger('rank_roi_week');
            $table->unsignedInteger('rank_roi_month');

            $table->double('rank_pnl');
            $table->double('rank_pnl_day');
            $table->double('rank_pnl_week');
            $table->double('rank_pnl_month');

            $table->dateTime('last_unshared_at')->nullable();
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
