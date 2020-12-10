<?php

namespace App;

use App\Contracts\StockQuoteServiceContract as StockQuoteServiceContract;
use Cache;
use GuzzleHttp\Client as Client;

class Quandl implements StockQuoteServiceContract
{
  public static function getQuote($ticker, $simplified = false)
  {
    return Cache::remember($ticker.'_quote_'.'_'.($simplified ? 'simplified' : 'verbose'), 60, function () use ($ticker, $simplified) {
      $httpClient = new Client([
          'base_uri' => env('QUANDL_STOCK_QUOTE_API_ENDPOINT'),
          'timeout' => 60,
      ]);

      $request = $httpClient->request(
              'GET',
              str_replace('{ticker}', $ticker, 'api/v3/datasets/WIKI/{ticker}/data.json'),
              [
                  'query' => [
                      'api_key' => env('QUANDL_STOCK_QUOTE_API_KEY'),
                  ],
              ]
          );

      $result = json_decode($request->getBody(), true);
      if ($simplified) {
        return floatval($result['dataset_data']['data'][0][4]);
      }
      return $result;
    });
  }

  public static function getHistory($ticker, $from, $to)
  {
    $httpClient = new Client([
        'base_uri' => env('QUANDL_STOCK_QUOTE_API_ENDPOINT'),
        'timeout' => 60,
    ]);

    $request = $httpClient->request(
            'GET',
            str_replace('{ticker}', $ticker, 'api/v3/datasets/WIKI/{ticker}/data.json'),
            [
                'query' => [
                    'api_key' => env('QUANDL_STOCK_QUOTE_API_KEY'),
                ],
            ]
        );

    $result = json_decode($request->getBody(), true);

    // "column_names":[
    //    "Date",
    //    "Open",
    //    "High",
    //    "Low",
    //    "Close",
    //    "Volume",
    //    "Ex-Dividend",
    //    "Split Ratio",
    //    "Adj. Open",
    //    "Adj. High",
    //    "Adj. Low",
    //    "Adj. Close",
    //    "Adj. Volume"
    //  ]
    // "data" : [
    //  {
    //    "1980-12-12",
    //    28.75,
    //    28.87,
    //    28.75,
    //    28.75,
    //    2093900,
    //    0,
    //    1,
    //    0.42270591588018,
    //    0.42447025361603,
    //    0.42270591588018,
    //    0.42270591588018,
    //    117258400
    //  }
    // ]

    $label = $result['dataset_data']['column_names'];

    $dateKey = array_search('Date', $label);

    $result = array_filter($result['dataset_data']['data'], function ($value) use ($from, $to, $dateKey) {
      return ((strtotime($value[$dateKey]) >= strtotime($from)) &&
              (strtotime($value[$dateKey]) <= strtotime($to)));
    });

    $result = array_combine(array_column($result, 0), $result);

    $label = array_flip($label);

    array_walk($result, function(&$value, &$key) use ($label) {
      unset($value[$label['Date']]);
      $value['open'] = $value[$label['Open']];
      unset($value[$label['Open']]);
      $value['high'] = $value[$label['High']];
      unset($value[$label['High']]);
      $value['low'] = $value[$label['Low']];
      unset($value[$label['Low']]);
      $value['close'] = $value[$label['Close']];
      unset($value[$label['Close']]);
      $value['volume'] = $value[$label['Volume']];
      unset($value[$label['Volume']]);
      unset($value[$label['Ex-Dividend']]);
      unset($value[$label['Split Ratio']]);
      unset($value[$label['Adj. Open']]);
      unset($value[$label['Adj. High']]);
      unset($value[$label['Adj. Low']]);
      unset($value[$label['Adj. Close']]);
      unset($value[$label['Adj. Volume']]);
    });

    return $result;
  }
}
