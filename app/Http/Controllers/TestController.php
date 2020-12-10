<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
use App;
use App\Downloadable;
use App\PurchaseHeader;
use App\User;
use App\Helpers\BarcodeHelper;
use App\Http\Requests;
use Validator;
use Storage;
use Fpdf;

class TestController extends Controller
{

	public function test(Request $request, $id)
	{
		/*
		Fpdf::AddPage();
		Fpdf::SetFont('Courier', 'B', 18);
		Fpdf::Cell(50, 25, 'Hello World!');
		Fpdf::Output("test.pdf", "D");
		*/

		/*
		echo BarcodeHelper::generateBarcode128("852189001384", 2, 17, "ENUWI-G2");

		echo BarcodeHelper::generateBarcodeUPCA("852189001384", 2, 17, "ENUWI-G2");
		*/

		/*
		return view('test.test2');
		*/

		/*
		$order = PurchaseHeader::find($id);
		$order->release(auth()->user()->id);
		return "done";
		*/

		/*
		$content = Storage::disk('s3')->get('us.png');
		Storage::disk('local')->put('Andrew '.date('Y-m-d H:i:s').'.png', $content, 'public');

		$content = Storage::disk('local')->get('andrew.png');
		Storage::disk('s3')->put('/Andrew '.date('Y-m-d H:i:s').'.png', $content, 'public');

		echo "done";
		*/

		/*
		$path = Storage::disk('s3')->getDriver()->getAdapter()->getPathPrefix();
		return $path;
		*/
	}

	public function testPost($id, Request $request)
	{
		/*
		return redirect()->back();
		*/
	}
}
