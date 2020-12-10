<?php

namespace App\Http\Controllers;

use App;
use App\Downloadable;
use App\User;
use App\Helpers\HistoryHelper;
use App\Helpers\RouteHelper;
use App\Helpers\S3DownloadHelper;
use App\Http\Requests;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Storage;
use Validator;

class FileController extends Controller
{
	public function fileUpload(Request $request)
	{
		return view()->first(generateTemplateCandidates('tool.file_upload'), [
				'source' => [
					'post_url' => '/' . $request->path(),
				],
			]);
	}

	public function fileUploadPost(Request $request)
	{
		/*
		$rules = [
			'upload-selector' => "required|file",
		];

		$validator = Validator::make($request->all(), $rules);
		*/
		if (is_null($request->file('upload-selector'))) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors(['upload-selector' => str_replace(":attribute", "file", trans('validation.required'))])
					->withInput($request->all());
		}

		$theFile = $request->file('upload-selector');
		$originalName = $theFile->getClientOriginalName();
		$fileSize = $theFile->getSize();
		$fileMime = $theFile->getMimeType();
		$hashValue = md5(date('YmdHis').$originalName);

		try {
			DB::transaction(function() use ($hashValue, $theFile, $originalName, $fileSize, $fileMime) {
				// copy to storage location
				//$theFile->move(Storage::getDriver()->getAdapter()->getPathPrefix(), $hashValue);
				Storage::disk('s3')->put($hashValue, file_get_contents($theFile->getRealPath()), 'public');

				Downloadable::create([
					'uploader_id' => Auth::user()->id,
					'title' => '',
					'description' => 'file uploaded by ' . Auth::user()->name . ' at ' . date('Y-m-d H:i:s'),
					'original_name' => $originalName,
					'file_size' => $fileSize,
					'mime_type' => $fileMime,
					'hash' => $hashValue,
					'valid' => 1,
				]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success
		return redirect(HistoryHelper::goBackPages(1))->with('alert-success', trans('tool.Upload completed.  Download link is shown below') . '<br>' . str_replace('{hash}', $hashValue, url(RouteHelper::appReverseLookupRoute("FileController@fileDownload"))));
	}

	public function fileDownload($hash, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$downloadable = Downloadable::where('hash', $hash)->first();

		if ($request->input('base64')) {
			$base64content = base64_encode(file_get_contents(S3DownloadHelper::toLocal($downloadable->hash)));
			return response()->json([
				'success' => true,
				'content' => 'data:' . $downloadable->mime_type . ';base64, ' . $base64content,
			]);
		}

		$headers = [
						'Content-Type: ' . $downloadable->mime_type,
					];
		return response()->download(S3DownloadHelper::toLocal($downloadable->hash), $downloadable->original_name, $headers);
	}
}
