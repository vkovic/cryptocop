<?php

namespace App\Services\Binance;

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
            'timeInForce' => 'GTC', // Mandatory with 'LIMIT' order type
            'quantity' => $quantity,
            'price' => (float) $price + (2 * $price / 100), // Add 2% price to buy instantly
            'recvWindow' => 60000,
            'timestamp' => $this->timestamp()
        ];

        return $this->httpRequest('v1/order', 'POST', $params, true);
    }

    public function limitSell(string $symbol, $quantity, $price)
    {
        $params = [
            'symbol' => $symbol,
            'side' => 'SELL',
            'positionSide' => 'SHORT',
            'type' => 'LIMIT',
            'timeInForce' => 'GTC',
            'quantity' => $quantity,
            'price' => (float) $price - (2 * $price / 100), // Reduce by 2% to sell instantly
            'recvWindow' => 60000,
            'timestamp' => $this->timestamp()
        ];

        return $this->httpRequest('v1/order', 'POST', $params, true);
    }

    protected function timestamp()
    {
        return number_format(microtime(true) * 1000, 0, '.', '');
    }

    protected function request(string $url, string $method = 'GET', array $params = [], bool $signed = false)
    {
        $curl = curl_init();
        $base = $this->base;
        $query = http_build_query($params, '', '&');

        // Signing
        if ($signed === true) {
            $query = http_build_query($params, '', '&');
            $signature = hash_hmac('sha256', $query, $this->apiSecret);
            if ($method === 'POST') {
                $endpoint = $base . $url;
                $params['signature'] = $signature; // signature needs to be inside BODY
                $query = http_build_query($params, '', '&'); // rebuilding query
            } else {
                $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-MBX-APIKEY: ' . $this->apiKey]);
        }

        curl_setopt($curl, CURLOPT_USERAGENT, 'User-Agent: Mozilla/4.0 (compatible; PHP Binance API)');

        // POST method
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }

        // DELETE method
        if ($method === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        // PUT method
        if ($method === 'PUT') {
            curl_setopt($curl, CURLOPT_PUT, true);
        }

        // Std CURL options
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        $output = curl_exec($curl);

        // Check if any error occurred
        if (curl_errno($curl) > 0) {
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            throw new \Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        $json = json_decode($output, true);

        return $json;
    }
}
