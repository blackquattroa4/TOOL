<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Http\Requests;
use App\Http\Resources\PurchaseHeader as PurchaseHeaderResource;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\PurchaseDetail;
use App\PurchaseHistory;
use App\PurchaseHeader;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Helpers\PurchaseQuoteView;
use App\TaxableEntity;
use App\User;
use Auth;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use NumberFormatter;
use Session;
use Validator;

class PurchaseQuoteController extends Controller
{
	// validation rules for 'create', 'update'
	CONST VALIDATION_RULES = [
		'reference' => 'required',
		'incoterm' => 'required',
		'inputdate' => 'required',
		'expiration' => 'required',
		'unitprice.*' => "required|numeric",
		'quantity.*' => "required|min:{min_val}|numeric",
	];

	public function createQuote($supplierId, Request $request)
	{
		$supplier = TaxableEntity::find($supplierId);
		if ($supplier->isNotActiveSupplier()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "", trans('vrm.Quote ### can not be created')));
		}

		// flash-in default value if no errors; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'increment' => '?????',
				'type' => 'quote',
				'supplier' => $supplier->id,
				'inputdate' => DateHelper::dbToGuiDate(date('Y-m-d')),
				'payment' => $supplier->payment_term_id,
				'contact' => $supplier->contact()->orderBy('id', 'desc')->first()->id,
				'staff' => Auth::user()->id,
				'currency' => $supplier->currency_id,
				'line' => [ ],
				'product' => [ ],
				'display' => [ ],
				'unitprice' => [ ],
				'description' => [ ],
				'quantity' => [ ],
			]);
		}

		$optionArray = PurchaseQuoteView::generateOptionArrayForTemplate($supplierId, true);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.New quote'),
				//'document' => '????',
				//'status' =>  trans('status.Open'),
				'currencyFormat' => TaxableEntity::find($supplierId)->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'quote',
				'history' => [],
				'action' => trans('forms.Create')
			];

		return view()->first(generateTemplateCandidates('form.purchase_quote'), $optionArray);
	}

	public function createQuotePost(Request $request)
	{
		// validate first.
		$rules = str_replace("{min_val}", "1", self::VALIDATION_RULES);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}
		$title = "?????";

		try {
			DB::transaction(function() use ($request, &$title) {
				$header = PurchaseHeader::initialize('quote', $request);
				$title = $header->title;
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('vrm.Quote ### created.')));
	}

	public function createPostAjax(Request $request)
	{
		// validate first.
		$rules = str_replace("{min_val}", "1", self::VALIDATION_RULES);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$header = null;

		try {
			DB::transaction(function() use ($request, &$header) {
				$header = PurchaseHeader::initialize('quote', $request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new PurchaseHeaderResource($header) ]);
	}

	public function updateQuote($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be updated')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// load purchase quote detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
			$oldInput['expiration'] = $oldInput['ddate'][0];
			$request->session()->flashInput($oldInput);
		}

		$optionArray = PurchaseQuoteView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.Update quote'),
				//'document' => $purchaseHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($purchaseHeaderObj['status'])),
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'quote',
				'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Update')
			];

		return view()->first(generateTemplateCandidates('form.purchase_quote'), $optionArray);
	}

	public function updateQuotePost($id, Request $request)
	{
		// validate first.
		$rules = str_replace("{min_val}", "0", self::VALIDATION_RULES);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		// update database
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be updated')));
		}

		try {
			DB::transaction(function() use ($purchaseHeaderObj, $request) {
				$purchaseHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### updated.')));
	}

	public function updatePostAjax($id, Request $request)
	{
		// validate first.
		$rules = str_replace("{min_val}", "0", self::VALIDATION_RULES);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		// update database
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote() || $purchaseHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be updated')) ]]]);
		}

		try {
			DB::transaction(function() use ($purchaseHeaderObj, $request) {
				$purchaseHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new PurchaseHeaderResource($purchaseHeaderObj) ]);
	}

	public function viewQuote($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be viewed')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$oldInput['expiration'] = $oldInput['ddate'][0];
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseQuoteView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.View quote'),
				//'document' => $purchaseHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($purchaseHeaderObj['status'])),
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'quote',
				'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.purchase_quote'), $optionArray);
	}

	public function loadPurchaseQuoteAjax($id, Request $request)
	{
		if ($id) {
			$purchaseHeaderObj = PurchaseHeader::find($id);
			$purchaseDetails = $purchaseHeaderObj->purchaseDetail;
			$monetaryFormat = "%0." . $purchaseHeaderObj->currency->getFormat()['fdigit'] . "f";

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $purchaseHeaderObj->id,
					'csrf' => csrf_token(),
					'increment' => $purchaseHeaderObj->title,
					'entity' => $purchaseHeaderObj->entity_id,
					'inputdate' => DateHelper::dbToGuiDate($purchaseHeaderObj->order_date),
					'expiration' => DateHelper::dbToGuiDate($purchaseHeaderObj->order_date),
					'payment' => $purchaseHeaderObj->payment_term_id,
					'incoterm' => $purchaseHeaderObj->fob,
					'contact' => $purchaseHeaderObj->contact_id,
					'reference' => $purchaseHeaderObj->reference,
					'staff' => $purchaseHeaderObj->purchase_id,
					'currency' => $purchaseHeaderObj->currency_id,
					'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $purchaseHeaderObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
					'line' => $purchaseDetails->pluck('id'),
	        'product' => $purchaseDetails->pluck('unique_tradable_id'),
	        'display' => $purchaseDetails->pluck('display_as'),
	        'unitprice' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $purchaseDetails->pluck('unit_price')->toArray()),
	        'description' =>  $purchaseDetails->pluck('description'),
	        'quantity' => array_map(function($elem) { return sprintf(env('APP_QUANTITY_FORMAT'), $elem['ordered_quantity'] - $elem['shipped_quantity']); }, $purchaseDetails->toArray()),
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'increment' => '????',
				'entity' => 0,
				'inputdate' => DateHelper::dbToGuiDate(date("Y-m-d")),
				'expiration' => '',
				'payment' => 0,
				'incoterm' => '',
				'contact' => 0,
				'reference' => '',
				'staff' => auth()->user()->id,
				'currency' => 0,
				'history' => [ ],
				'line' => [ ],
        'product' => [ ],
        'display' => [ ],
        'unitprice' => [ ],
        'description' => [ ],
        'quantity' => [ ]
			 ]
		 ]);
	}

	public function printQuote($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be printed')));
		}
		$pdf = $purchaseHeaderObj->generatePdf();
		$pdf->Output("Purchase ".ucfirst($purchaseHeaderObj->type)." #".$purchaseHeaderObj->title.".pdf", "D");
	}

	public function printPostAjax($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		$pdf = $purchaseHeaderObj->generatePdf();
		$pdf->Output("Purchase ".ucfirst($purchaseHeaderObj->type)." #".$purchaseHeaderObj->title.".pdf", "D");
	}

	public function approveQuote($id, Request $request)
	{
		// make sure this is an order
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be approved')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// check this person's approval is required and missing.
		if (!$purchaseHeaderObj->requireApproval(Auth::user()->id)) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be approved')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$oldInput['expiration'] = $oldInput['ddate'][0];
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseQuoteView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.Approve quote'),
				//'document' => $purchaseHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($purchaseHeaderObj['status'])),
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'quote',
				'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => [
							'approve' => trans('forms.Approve'),
							'disapprove' => trans('forms.Disapprove'),
						],
			];

		return view()->first(generateTemplateCandidates('form.purchase_quote'), $optionArray);
	}

	public function approveQuotePost($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be approved')));
		}

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				switch ($request->input('submit')) {
				case 'approve':
					$purchaseHeaderObj->approve(Auth::user()->id, $request->ip());
					break;
				case 'disapprove':
					$purchaseHeaderObj->disapprove(Auth::user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### processed.')));
	}

	public function approvePostAjax($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotQuote() || $purchaseHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Quote ### can not be approved')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				switch ($request->input('submit')) {
				case 'approve':
					$purchaseHeaderObj->approve(Auth::user()->id, $request->ip());
					break;
				case 'disapprove':
					$purchaseHeaderObj->disapprove(Auth::user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => new PurchaseHeaderResource($purchaseHeaderObj) ]);
	}

}
