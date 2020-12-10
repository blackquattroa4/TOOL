<?php

namespace App;

use App\Contracts\FinancialStatementServiceContract as FinancialStatementServiceContract;
use GuzzleHttp\Client as Client;

class Financialmodelingprep implements FinancialStatementServiceContract
{
  public static function getData($ticker, $from, $to, $quarterly = false)
  {
    $httpClient = new Client([
        'base_uri' => env('FINANCIALMODELINGPREP_API_ENDPOINT'),
        'timeout' => 60,
    ]);

    $request = $httpClient->get(
            str_replace('{ticker}', $ticker, '/api/v3/income-statement/{ticker}?apikey=' . env('FINANCIALMODELINGPREP_API_KEY')) .
              ($quarterly ? "&period=quarter" : ""),
            []
        );

    $incomeStatements = json_decode($request->getBody(), true);

    // unfortunately this API return "" for 0.0, so we have to fill up empty elements
    $incomeStatements['financials'] = array_map(function($onePeriod) {
        return array_map(function($element) {
          return empty($element) ? "0.0" : $element;
        }, $onePeriod);
      }, $incomeStatements);

    $request = $httpClient->get(
            str_replace('{ticker}', $ticker, '/api/v3/balance-sheet-statement/{ticker}?apikey=' . env('FINANCIALMODELINGPREP_API_KEY')) .
              ($quarterly ? "&period=quarter" : ""),
            []
        );

    $balanceSheetStatements = json_decode($request->getBody(), true);

    // unfortunately this API return "" for 0.0, so we have to fill up empty elements
    $balanceSheetStatements['financials'] = array_map(function($onePeriod) {
        return array_map(function($element) {
          return empty($element) ? "0.0" : $element;
        }, $onePeriod);
      }, $balanceSheetStatements);

    // apply date range before return
    return [
      'income' => array_reverse(array_filter($incomeStatements['financials'], function($value) use ($from, $to) {
        return ((strtotime($from) <= strtotime($value['date'])) &&
                (strtotime($to) >= strtotime($value['date'])));
      })),
      'balance' => array_reverse(array_filter($balanceSheetStatements['financials'], function($value) use ($from, $to) {
        return ((strtotime($from) <= strtotime($value['date'])) &&
                (strtotime($to) >= strtotime($value['date'])));
      })),
    ];
  }
}
