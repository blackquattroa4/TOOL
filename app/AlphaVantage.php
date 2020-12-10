<?php

namespace App;

use App\Contracts\ForexServiceContract as ForexServiceContract;
use Cache;
use GuzzleHttp\Client as Client;

class AlphaVantage implements ForexServiceContract
{
  public static function getExchangeRate($from, $to, $simplified = false)
  {
    return Cache::remember('currency_exchange_rate_'.$from.'_'.$to.'_'.($simplified ? 'simplified' : 'verbose'), 60, function () use ($from, $to, $simplified) {
      $httpClient = new Client([
          'base_uri' => env('ALPHA_VANTAGE_FOREX_API_ENDPOINT'),
          'timeout' => 60,
      ]);

      $request = $httpClient->request(
          'GET',
          'query',
          [
              'query' => [
                  'function' => 'CURRENCY_EXCHANGE_RATE',
                  'from_currency' => $from,
                  'to_currency' => $to,
                  'apikey' => env('ALPHA_VANTAGE_FOREX_API_KEY'),
              ],
          ]
      );

      $result = json_decode($request->getBody(), true);
      if ($simplified) {
        return floatval($result['Realtime Currency Exchange Rate']['5. Exchange Rate']);
      }
      return $result;
    });
  }

}
