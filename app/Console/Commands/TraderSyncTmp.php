<?php

namespace App\Console\Commands;

use App\Models\Position;
use App\Models\Trader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TraderSyncTmp extends Command
{
    use ColoredLinesOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:trader';

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
        $baseInfoUrl = 'https://www.binance.com/gateway-api/v1/public/future/leaderboard/getOtherLeaderboardBaseInfo';
        $infoUrl = 'https://www.binance.com/gateway-api/v1/public/future/leaderboard/getOtherLeaderboardInfo';

        // Remove all trades from removed traders (from traders config array)
        // and corresponding positions
        Trader::whereNotIn('uid', $traderUids)->get()->each(function (Trader $trader) {
            $trader->positions()->delete();
            $trader->delete();
        });

        foreach ($traderUids as $traderUid) {
            $payloadBaseInfoUrl = [
                'encryptedUid' => $traderUid,
                'tradeType' => 'PERPETUAL'
            ];

            $payloadInfoUrl = [
                'encryptedUid' => $traderUid,
                'tradeType' => 'PERPETUAL',
                'periodType' => 'ALL',
            ];

            $resp = Http::post($baseInfoUrl, $payloadBaseInfoUrl);
            $rawBodyBaseInfo = $resp->body();
            $respArrBaseInfo = json_decode($rawBodyBaseInfo);
            $dataBaseInfo = $respArrBaseInfo->data;

            $resp = Http::post($infoUrl, $payloadInfoUrl);
            $rawBodyInfo = $resp->body();
            $respArrInfo = json_decode($rawBodyInfo);
            $dataInfo = $respArrInfo->data;

            $trader = Trader::where('uid', $traderUid)->first();

            if ($trader === null) {
                $trader = new Trader;
                $trader->uid = $traderUid;
            }

            $trader->nick = $dataBaseInfo->nickName;
            $trader->sharing = $dataBaseInfo->positionShared;
            $trader->twitter = $dataBaseInfo->twitterUrl;

            $trader->rank_roi = $dataInfo->roiRank;
            $trader->rank_roi_day = $dataBaseInfo->dailyRoiRank;
            $trader->rank_roi_week = $dataBaseInfo->weeklyRoiRank;
            $trader->rank_roi_month = $dataBaseInfo->monthlyRoiRank;

            $trader->rank_pnl = $dataInfo->pnlRank;
            $trader->rank_pnl_day = $dataBaseInfo->dailyPnlRank;
            $trader->rank_pnl_week = $dataBaseInfo->weeklyPnlRank;
            $trader->rank_pnl_month = $dataBaseInfo->monthlyPnlRank;

            $trader->roi = $dataInfo->roiValue * 100;
            $trader->roi_day = $dataBaseInfo->dailyRoiValue * 100;
            $trader->roi_week = $dataBaseInfo->weeklyRoiValue * 100;
            $trader->roi_month = $dataBaseInfo->monthlyRoiValue * 100;

            $trader->pnl = $dataInfo->pnlValue;
            $trader->pnl_day = $dataBaseInfo->dailyPnlValue;
            $trader->pnl_week = $dataBaseInfo->weeklyPnlValue;
            $trader->pnl_month = $dataBaseInfo->monthlyPnlValue;

            $trader->save();
        }

        return 0;
    }
}
