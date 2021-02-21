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
    protected $signature = 'sync:position';

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
            $presentPositionIds = [];

            // New trader is one which dont have prev. positions
            $isNewTrader = $trader->positions()->count() === 0;

            foreach ($data->otherPositionRetList as $respPosition) {
                // Try to find same in our db
                $position = Position::where([
                    'trader_id' => $trader->id,
                    'symbol' => $respPosition->symbol,
                    'entry_price' => $respPosition->entryPrice,
                    'size' => $respPosition->amount,
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
                    $position->opened_at = $isNewTrader ? null : now();

                    $coin = str_replace('USDT', '', $respPosition->symbol);
                    $message = sprintf('NEW POSITION: @%s %s%s@%s',
                        $trader->nick,
                        $respPosition->amount,
                        $coin,
                        $respPosition->entryPrice
                    );
                    $this->info($message);
                }

                $position->save();

                $presentPositionIds[] = $position->refresh()->id;

                usleep(50000); // 0.05 sec
            }

            // Close positions for current trader,
            // which do not exist in the list obtained from the API
            $positionsForClosing = $trader->positions()
                ->whereNotIn('id', $presentPositionIds)
                ->get();

            foreach ($positionsForClosing as $position) {
                $position->closed_at = now();
                $position->save();

                $size = $position->size;
                $coin = str_replace('USDT', '', $position->symbol);
                $pnl = $position->pnl;

                $this->info(sprintf('POSITION CLOSED: @%s | size:%s | pnl:%s', $trader->nick, $size . $coin, $pnl));
            }
        }

        return 0;
    }
}
