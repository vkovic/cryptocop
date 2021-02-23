<?php

use Illuminate\Database\Migrations\Migration;

class CreateMainView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS main_view');
        DB::statement(
            "
            CREATE VIEW main_view AS
            SELECT
                t.nick trader,
                ROUND((t.roi_week + 4 * t.roi_month + 12 * t.roi) / 100) trader_score,
                CONCAT('w:', ROUND(t.roi_week), '|m:', ROUND(t.roi_month), '|a:', ROUND(t.roi), ')') roi_stats,
                t.sharing shared,
                ROUND(t.roi_week) roi_week,
                ROUND(t.roi_month) roi_month,
                ROUND(t.roi) roi_all,
                p.symbol symbol,
                p.size size,
                ROUND(p.invested) invested,
                ROUND(p.leverage) leverage,
                ROUND(p.cost) cost,
                p.entry_price entry_price,
                p.mark_price mark_price,
                ROUND(ABS(((1 - p.entry_price / p.mark_price) * 100)), 2) price_diff,
                ROUND(p.pnl) pnl,
                ROUND(p.roe) roe,
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
        DB::statement('DROP VIEW IF EXISTS main_view');
    }
}
