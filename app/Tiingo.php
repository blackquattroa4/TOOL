<?php

namespace App;

use App\Contracts\StockQuoteServiceContract as StockQuoteServiceContract;
use Cache;
use GuzzleHttp\Client as Client;

class Tiingo implements StockQuoteServiceContract
{
  public static function getQuote($ticker, $simplified = false)
  {
    return Cache::remember($ticker.'_quote_'.'_'.($simplified ? 'simplified' : 'verbose'), 60, function () use ($ticker, $simplified) {
      $httpClient = new Client([
          'base_uri' => env('TIINGO_STOCK_QUOTE_API_ENDPOINT'),
          'timeout' => 60,
      ]);

      $request = $httpClient->request(
              'GET',
              str_replace('{ticker}', $ticker, 'tiingo/daily/{ticker}/prices'),
              [
                  'query' => [
                      'token' => env('TIINGO_STOCK_QUOTE_API_KEY'),
                  ],
              ]
          );

      $result = json_decode($request->getBody(), true);
      if ($simplified) {
        return floatval($result[0]['close']);
      }
      return $result;
    });
  }

  public static function getHistory($ticker, $from, $to)
  {
    $httpClient = new Client([
        'base_uri' => env('TIINGO_STOCK_QUOTE_API_ENDPOINT'),
        'timeout' => 60,
    ]);

    $request = $httpClient->request(
            'GET',
            str_replace('{ticker}', $ticker, 'tiingo/daily/{ticker}/prices'),
            [
                'query' => [
                    'startDate' => $from,
                    'endDate' => $to,
                    'resampleFreq' => 'daily',
                    'token' => env('TIINGO_STOCK_QUOTE_API_KEY'),
                ],
            ]
        );

    $result = json_decode($request->getBody(), true);

    // raw format
    // [
    //  {
    //    "date":"2019-08-06T00:00:00.000Z",
    //    "close":197,
    //    "high":198.067,
    //    "low":194.04,
    //    "open":196.31,
    //    "volume":35824787,
    //    "adjClose":197,
    //    "adjHigh":198.067,
    //    "adjLow":194.04,
    //    "adjOpen":196.31,
    //    "adjVolume":35824787,
    //    "divCash":0,
    //    "splitFactor":1
    //  }
    // ]

    $label = array_column($result, "date");
    array_walk($label, function(&$value, &$key) {
      $value = preg_replace("/t\d+:\d+:\d+.\d+z/i", "", $value);
    });
    $result = array_combine($label, $result);

    // go through each element and unset unnecessary data
    array_walk($result, function(&$value, &$key) {
      unset($value['date']);
      unset($value['adjClose']);
      unset($value['adjHigh']);
      unset($value['adjLow']);
      unset($value['adjOpen']);
      unset($value['adjVolume']);
      unset($value['divCash']);
      unset($value['splitFactor']);
    });

    return $result;
  }
}
