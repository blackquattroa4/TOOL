<?php

namespace App\Http\Controllers;

use App\Facades\OcrService as Ocr;
use App\Helpers\DateHelper;
use App\UniqueTradable;
use Illuminate\Http\Request;
use Validator;

class OpticalCharacterRecognitionController extends Controller
{
	public function recognizeExpenseAjax(Request $request)
	{
    $validator = Validator::make($request->all(), [ 'the_file' => 'required|file' ]);
    if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

    // grab the file send to 3rd party site for recognition
    $theFile = $request->file('the_file');
		// can't access file in system temp directory; move to storage/app
		$tempFileLocation = storage_path('app') . DIRECTORY_SEPARATOR . $theFile->getClientOriginalName();
		copy($theFile->getRealPath(), $tempFileLocation);
    $result = Ocr::rawResult($tempFileLocation);
		unlink($tempFileLocation);

		if (!$result['success']) {
			return response()->json([ 'success' => false, 'error' => $result['error'] ]);
		}

    // :TODO: after found a good OCR, parse $result into following variables
    $accountId = UniqueTradable::where('current', 1)->where('expendable', 1)->inRandomOrder()->first()->id;
    $date = DateHelper::dbToGuiDate(date("Y-m-d"));
    $description = "some description of the expense";
    $unitPrice = rand(1001, 4999) / 100;
    $quantity = 1;

    return response()->json([
        'success' => true,
        'account_id' => $accountId,
        'incur_date' => $date,
        'description' => $description,
        'unit_price' => $unitPrice,
        'quantity' => $quantity
      ]);
  }

}
