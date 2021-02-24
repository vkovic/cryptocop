<?php

use Illuminate\Database\Migrations\Migration;

class AlterPositionStatsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS position_stats');
        DB::statement(
            "
            CREATE VIEW position_stats AS
            SELECT
                symbol,
                COUNT(symbol) AS total,
                # Positions count
                COUNT(CASE WHEN size < 0 THEN 1 END) shorts,
                COUNT(CASE WHEN size > 0 THEN 1 END) longs,
                # Total position size
                COALESCE(SUM(CASE WHEN size < 0 THEN -size END), 0) size_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN size END), 0) size_longs,
                # Total invested
                COALESCE(SUM(CASE WHEN size < 0 THEN invested END), 0) invested_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN invested END), 0) invested_longs,
                # Total cost (invested*leverage)
                COALESCE(SUM(CASE WHEN size < 0 THEN cost END), 0) cost_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN cost END), 0) cost_longs,
                # Avg. leverage8
                COALESCE(ROUND(AVG(CASE WHEN size < 0 THEN leverage END)), 0) avg_lev_shorts,
                COALESCE(ROUND(AVG(CASE WHEN size > 0 THEN leverage END)), 0) avg_lev_longs,
                # Total trader score
                COALESCE(SUM(CASE WHEN size < 0 THEN trader_score END), 0) trader_score_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN trader_score END), 0) trader_score_longs
            FROM main_view
            WHERE closed_at IS NULL
            GROUP BY symbol
            ORDER BY total DESC
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
