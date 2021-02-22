<?php

namespace App\Console\Commands;

use App\Models\Position;
use App\Models\Trader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PositionSync extends Command
{
    use ColoredLinesOutput;

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

            // Loop all present positions from the api response
            foreach ($data->otherPositionRetList as $respPosition) {
                $presentPositionIds[] = $this->createOrUpdatePositionForTrader($trader, $respPosition);

                usleep(50000); // 0.05 sec
            }

            $this->closePositionsForTraderByIds($trader, $presentPositionIds);
        }

        return 0;
    }

    /**
     * Close positions for current trader,
     * which do not exist in the list obtained from the API
     *
     * @param $trader
     * @param $ids
     */
    protected function closePositionsForTraderByIds($trader, $ids)
    {
        $positionsForClosing = $trader->positions()
            ->whereNotIn('id', $ids)
            ->whereNull('closed_at')
            ->get();

        foreach ($positionsForClosing as $position) {
            $position->closed_at = now();
            $position->save();

            $size = $position->size;
            $coin = str_replace('USDT', '', $position->symbol);
            $pnl = $position->pnl;

            $this->lineRed(sprintf('POSITION CLOSED | %s: size:%s | pnl:%s', $trader->nick, $size . $coin, $pnl));
        }
    }

    protected function createOrUpdatePositionForTrader($trader, $respPosition)
    {
        $type = $respPosition->amount < 0 ? 'short' : 'long';

        // Trader might have multiple positions one long, one short
        // (determined by negative amount)
        $position = $trader->positions()
            ->where('symbol', $respPosition->symbol)
            ->whereNull('closed_at')
            ->where('size', $type === 'short' ? '<' : '>', 0)
            ->first();

        $position = $position === null
            ? $this->createNewPosition($trader, $respPosition)
            : $this->updatePositionsForTrader($trader, $position, $respPosition);

        return $position->id;
    }

    protected function createNewPosition($trader, $respPosition)
    {
        $symbol = $respPosition->symbol;
        $coin = str_replace('USDT', '', $symbol);

        // Save position
        $position = new Position;
        $position->trader_id = $trader->id;
        $position->symbol = $symbol;
        $position->size = $respPosition->amount;
        $position->entry_price = $respPosition->entryPrice;
        $position->mark_price = $respPosition->markPrice;
        $position->pnl = $respPosition->pnl;
        $position->roe = $respPosition->roe * 100;
        $position->opened_at = now();
        $position->save();

        $message = sprintf('NEW POSITION | %s: %s%s@%s',
            $trader->nick,
            $respPosition->amount,
            $coin,
            $respPosition->entryPrice
        );
        $this->lineGreen($message);

        return $position->refresh();
    }

    protected function updatePositionsForTrader($trader, $position, $respPosition)
    {
        $coin = str_replace('USDT', '', $respPosition->symbol);

        // Difference from previous position size
        $diff = $respPosition->amount - $position->size;

        // Update position amount
        if ($position->size != $respPosition->amount) {
            $message = sprintf('POSITION UPDATE | %s: %sx%s@%s => %sx%s@%s (%s)',
                $trader->nick,
                // Old position: size x coin @ price
                $position->size,
                $coin,
                $position->entry_price,
                // New position: size x coin @ price
                $respPosition->amount,
                $coin,
                $respPosition->entryPrice,
                // Diff (add plus sign, minus is already there)
                $diff < 0 ? $diff : '+' . $diff
            );

            $this->lineYellow($message);
        }

        $position->size = $respPosition->amount;
        $position->entry_price = $respPosition->entryPrice;
        $position->mark_price = $respPosition->markPrice;
        $position->pnl = $respPosition->pnl;
        $position->roe = $respPosition->roe * 100;
        $position->save();

        return $position->refresh();
    }
}
