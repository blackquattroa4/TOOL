<?php

namespace App;

use App\Contracts\OcrServiceContract as OcrServiceContract;
use GuzzleHttp;

class OcrSpace implements OcrServiceContract
{
  public function rawResult($filePath)
  {
    return $this->scan($filePath, true);
  }

  public function tokenizedResult($filePath)
  {
    $result = $this->scan($filePath, true);

    if ($result['success']) {
      // tokenize result & remove
      $result = preg_split("/\s+/", $result['result']);
      $result = array_map(function($val) {
        return preg_replace("/[^\w]/", "", $val);
      }, $result);

      return array_unique(array_filter($result));
    }
    return [];
  }

  private function scan($filePath, $isTable = false)
  {
    $result = [ 'success' => false, 'error' => 'API call failure' ];
    try {
      $fileData = fopen($filePath, 'r');
      if (!$fileData) {
        throw new \Exception('File can not be opened for OCR');
      }
      $client = new GuzzleHttp\Client();
      if (!$client) {
        throw new \Exception('GuzzleHttp client not found');
      }
      $r = $client->request(
            'POST',
            env('OCR_SPACE_API_ENDPOINT'),
            [
              'headers' => [
                'apiKey' => env('OCR_SPACE_API_KEY'),
                'isTable' => $isTable,
              ],
              'multipart' => [
                [
                  'name' => 'file-' . date("YmdHis"),
                  'contents' => $fileData
                ]
              ]
            ],
            [
              'file' => $fileData
            ]);
      $response =  json_decode($r->getBody(),true);
      if($response['ParsedResults'][0]['ErrorMessage'] == "") {
        $result['success'] = true;
        $result['result'] = $response['ParsedResults'][0]['ParsedText'];
        unset($result['error']);
      } else {
        $result['success'] = false;
        $result['error'] = $response['ParsedResults'][0]['ErrorDetails'];
      }
    } catch(\Exception $e) {
      $registration = recordAndReportProblem($e);
      $result['error'] = $e->getMessage();
    }
    return $result;
  }
}
