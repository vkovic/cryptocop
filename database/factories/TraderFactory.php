<?php

namespace Database\Factories;

use App\Models\Trader;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TraderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trader::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nick' => 'xx',
            'uid' => strtoupper(Str::random(32)),
            'sharing' => true,
            'twitter' => null,
            'rank_roi' => 1,
            'rank_roi_day' => 1,
            'rank_roi_week' => 1,
            'rank_roi_month' => 1,
            'rank_pnl' => 1,
            'rank_pnl_day' => 1,
            'rank_pnl_week' => 1,
            'rank_pnl_month' => 1,
            'roi' => 1,
            'roi_day' => 1,
            'roi_week' => 1,
            'roi_month' => 1,
            'pnl' => 1,
            'pnl_day' => 1,
            'pnl_week' => 1,
            'pnl_month' => 1,
        ];
    }
}
