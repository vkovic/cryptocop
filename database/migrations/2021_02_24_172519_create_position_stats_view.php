<?php

use Illuminate\Database\Migrations\Migration;

class CreatePositionStatsView extends Migration
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
                COUNT(CASE WHEN size < 0 THEN 1 END) shorts,
                COUNT(CASE WHEN size > 0 THEN 1 END) longs,
            
                COALESCE(SUM(CASE WHEN size < 0 THEN invested END), 0) invested_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN invested END), 0) invested_longs,
            
                COALESCE(SUM(CASE WHEN size < 0 THEN invested END), 0) total_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN invested END), 0) total_longs,
            
                COALESCE(ROUND(AVG(CASE WHEN size < 0 THEN leverage END)), 0) avg_lev_shorts,
                COALESCE(ROUND(AVG(CASE WHEN size > 0 THEN leverage END)), 0) avg_lev_longs,
            
                COALESCE(SUM(CASE WHEN size < 0 THEN trader_score END), 0) score_shorts,
                COALESCE(SUM(CASE WHEN size > 0 THEN trader_score END), 0) score_longs
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
