<?php

namespace App\Services\Binance;

use GuzzleHttp\Client;

class BinanceApi
{
    const SIDE_BUY = 'BUY';
    const SIDE_SELL = 'SELL';
    const PSIDE_LONG = 'LONG';
    const PSIDE_SHORT = 'SHORT';

    protected $apiKey;
    protected $apiSecret;

    protected $base = 'https://fapi.binance.com/fapi/';

    public function __construct()
    {
        $this->apiKey = env('BINANCE_API_KEY');
        $this->apiSecret = env('BINANCE_SECRET_KEY');
    }

    public function limitBuy(string $symbol, $quantity, $price = null)
    {
        $params = [
            'symbol' => $symbol,
            'side' => 'BUY',
            'positionSide' => 'LONG',
            'type' => 'LIMIT',
            'quantity' => $quantity,
            'price' => (float) $price + (2 * $price / 100), // Add 2% price to buy instantly
        ];

        return $this->apiRequest('POST', 'v1/order', $params, true);
    }

    public function limitSell(string $symbol, $quantity, $price)
    {
        $params = [
            'symbol' => $symbol,
            'side' => 'SELL',
            'positionSide' => 'SHORT',
            'type' => 'LIMIT',
            'quantity' => $quantity,
            'price' => (float) $price - (2 * $price / 100), // Reduce by 2% to sell instantly
        ];

        return $this->apiRequest('POST', 'v1/order', $params, true);
    }

    public function timestamp()
    {
        return number_format(microtime(true) * 1000, 0, '.', ''); // TRY  (int) (time() * 1000)
    }

    public function apiRequest($method, $uri, $params = [])
    {
        $defaultParams = [
            'timeInForce' => 'GTC',
            'recWindows' => 5000,
            'timestamp' => $this->timestamp()
        ];

        $params = array_merge($defaultParams, $params);

        // Append signature to the request params
        $params['signature'] = $this->sign($params);

        return (new Client())->request($method, $this->base . $uri, [
            'headers' => ['X-MBX-APIKEY' => $this->apiKey],
            'query' => $params,
        ])->getBody()->getContents();
    }

    protected function sign($params)
    {
        return hash_hmac('sha256', http_build_query($params), $this->apiSecret);
    }
}
