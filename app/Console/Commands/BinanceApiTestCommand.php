<?php

namespace App\Console\Commands;

use App\Services\Binance\BinanceApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BinanceApiTestCommand extends Command
{
    protected $positions = [];
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
        // CryptoNifeCatch
        // 9745A111F31F836D6D2E9F758DA3A07B

        //$this->syncPos();

        $bApi = new BinanceApi();

        //$bApi->apiRequest('POST', 'test', ['a' => 12, 'b' => 'as']);

        //dd($bApi->getCurrentPosMode());

        $bApi->timestamp = $bApi->timestamp();

        dd($bApi->limitBuy('ETHUSDT', 0.01, 1700));
    }

    public function syncPos()
    {

        $endpoint = 'https://www.binance.com/gateway-api/v1/public/future/leaderboard/getOtherPosition';
        $payload = ['encryptedUid' => '9745A111F31F836D6D2E9F758DA3A07B', 'tradeType' => 'PERPETUAL'];

        $resp = Http::post($endpoint, $payload);

        $rawBody = $resp->body();
        $bodyObj = json_decode($rawBody);
        $data = $bodyObj->data;

        dd($data);

        // Ids of positions which are obtained from the API.
        // Those are currently present positions for user.
        // Closed positions are not included in the response.
        $presentPositionIds = [];

        // Loop all present positions from the api response
        foreach ($data->otherPositionRetList as $respPosition) {
            $leverage = 3;
            $invested = abs($respPosition->size * $respPosition->entry_price) / $leverage;


            sleep(1);
        }


    }


}
