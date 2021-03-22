<?php

namespace App\Console\Commands;

use App\Services\Binance\BinanceApi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BinanceApiTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bapi:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $bApi = new BinanceApi();

        dd($bApi->limitSell('ETHUSDT', 0.01, 1600));

        // [
        //     "orderId" => 8389765494397188191,
        //     "symbol" => "ETHUSDT",
        //     "status" => "NEW",
        //     "clientOrderId" => "85Hg2nqwysA09JfDKDg4G3",
        //     "price" => "1568",
        //     "avgPrice" => "0.00000",
        //     "origQty" => "0.010",
        //     "executedQty" => "0",
        //     "cumQty" => "0",
        //     "cumQuote" => "0",
        //     "timeInForce" => "GTC",
        //     "type" => "LIMIT",
        //     "reduceOnly" => false,
        //     "closePosition" => false,
        //     "side" => "SELL",
        //     "positionSide" => "SHORT",
        //     "stopPrice" => "0",
        //     "workingType" => "CONTRACT_PRICE",
        //     "priceProtect" => false,
        //     "origType" => "LIMIT",
        //     "updateTime" => 1616450984889,
        // ];
    }
}
