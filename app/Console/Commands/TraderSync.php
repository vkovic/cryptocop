<?php

namespace App\Console\Commands;

use App\Models\Trader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TraderSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trader:sync';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $traderUids = config('traders');
        $url = 'https://www.binance.com/gateway-api/v1/public/future/leaderboard/getOtherLeaderboardBaseInfo';

        foreach ($traderUids as $traderUid) {
            $payload = [
                'encryptedUid' => $traderUid,
                'tradeType' => 'PERPETUAL'
            ];

            $response = Http::post($url, $payload);
            $rawBody = $response->body();
            $respArr = json_decode($rawBody);
            $data = $respArr->data;

            Trader::create([
                'uid' => $traderUid,
                'nick' => $data->nickName,
                'sharing' => $data->positionShared,
                'twitter' => $data->twitterUrl,

                'rank_roi' => 0,
                'rank_roi_day' => $data->dailyRoiRank,
                'rank_roi_week' => $data->weeklyRoiRank,
                'rank_roi_month' => $data->monthlyRoiRank,

                'rank_pnl' => 0,
                'rank_pnl_day' => $data->dailyPnlRank,
                'rank_pnl_week' => $data->weeklyPnlRank,
                'rank_pnl_month' => $data->monthlyPnlRank,

                'roi' => 0,
                'roi_day' => $data->dailyRoiValue,
                'roi_week' => $data->weeklyRoiValue,
                'roi_month' => $data->monthlyRoiValue,

                'pnl' => 0,
                'pnl_day' => $data->dailyPnlValue,
                'pnl_week' => $data->weeklyPnlValue,
                'pnl_month' => $data->monthlyPnlValue,
            ]);
        }

        return 0;
    }
}
