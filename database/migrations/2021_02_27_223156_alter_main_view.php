<?php

use Illuminate\Database\Migrations\Migration;

class AlterMainView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS main_view');
        DB::statement('DROP VIEW IF EXISTS agg_data_view');
        DB::statement(
            "
            CREATE VIEW agg_data_view AS
            SELECT
                t.nick trader,
                p.symbol symbol,
                p.size size,
                ROUND(p.invested) invested,
                ROUND(p.leverage) leverage,
                ROUND(p.cost) cost,
                p.initial_entry_price initial_entry_price,
                p.entry_price entry_price,
                p.mark_price mark_price,
                ROUND(ABS(((1 - p.entry_price / p.mark_price) * 100)), 2) price_diff_prc,
                ROUND(p.pnl) position_pnl,
                ROUND(p.roe) position_roe,
                ROUND(t.roi) roi,
                ROUND(t.roi_month) roi_month,
                ROUND(t.roi_week) roi_week,
                ROUND(t.roi_day) roi_day,
                t.sharing sharing,
                p.opened_at opened_at,
                p.closed_at closed_at
            FROM
                traders t
                LEFT JOIN positions p ON t.id = p.trader_id
            "
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS agg_data_view');
    }
}
