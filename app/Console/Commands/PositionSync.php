<?php

namespace App\Console\Commands;

use App\Models\Position;
use App\Models\Trader;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PositionSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'position:sync';

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
        $traders = Trader::where('sharing', true)->get();
        $url = 'https://www.binance.com/gateway-api/v1/public/future/leaderboard/getOtherPosition';

        foreach ($traders as $trader) {
            $payload = [
                'encryptedUid' => $trader->uid,
                'tradeType' => 'PERPETUAL'
            ];

            $resp = Http::post($url, $payload);
            $rawBody = $resp->body();
            $bodyObj = json_decode($rawBody);
            $data = $bodyObj->data;

            // Ids of positions which are obtained from the API.
            // Those are currently present positions for user.
            // Closed positions are not included in the response.
            $presentTradeIds = [];

            foreach ($data->otherPositionRetList as $respPosition) {
                $openedAt = Carbon::createFromTimestampMs($respPosition->updateTimeStamp);

                $position = Position::where([
                    'trader_id' => $trader->id,
                    'symbol' => $respPosition->symbol,
                    'entry_price' => $respPosition->entryPrice,
                    'size' => $respPosition->amount,
                    'opened_at' => $openedAt,
                    'closed_at' => null
                ])->first();

                // Pos exist - update it
                if ($position !== null) {
                    $position->mark_price = $respPosition->markPrice;
                    $position->pnl = $respPosition->pnl;
                    $position->roe = $respPosition->roe * 100;
                }
                //
                // New position
                else {
                    $position = new Position;

                    $position->trader_id = $trader->id;
                    $position->symbol = $respPosition->symbol;
                    $position->size = $respPosition->amount;
                    $position->entry_price = $respPosition->entryPrice;
                    $position->mark_price = $respPosition->markPrice;
                    $position->pnl = $respPosition->pnl;
                    $position->roe = $respPosition->roe * 100;
                    $position->opened_at = $openedAt;
                }

                $position->save();

                $presentTradeIds[] = $position->refresh()->id;

                usleep(100000); // 0.1 sec
            }

            // Close positions for current trader,
            // which do not exist in the list obtained from the API
            Position::whereNotIn('id', $presentTradeIds)
                ->where('trader_id', $trader->id)
                ->update(['closed_at' => Carbon::now()]);
        }

        return 0;
    }
}
