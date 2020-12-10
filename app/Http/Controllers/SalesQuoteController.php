<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Helpers\SalesQuoteView;
use App\Http\Requests;
use App\Http\Resources\SalesHeader as SalesHeaderResource;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\SalesDetail;
use App\SalesHistory;
use App\SalesHeader;
use App\TaxableEntity;
use App\UniqueTradable;
use App\User;
use Auth;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use NumberFormatter;
use Session;
use Validator;

class SalesQuoteController extends Controller
{
	// validation rules for 'create', 'update'
	CONST VALIDATION_RULES = [
		'reference' => 'required',
		'incoterm' => 'required',
		'inputdate' => 'required',
		'expiration' => 'required',
		'unitprice.*' => "required|numeric",
		'quantity.*' => "required|numeric",
	];

	public function createQuote($customerId, Request $request)
	{
		$customer = TaxableEntity::find($customerId);
		if ($customer->isNotActiveCustomer()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", trans('crm.Quote ### can not be created')));
		}

		// flash-in default value if no errors; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'increment' => '?????',
				'type' => 'quote',
				'customer' => $customerId,
				'inputdate' => DateHelper::dbToGuiDate(date('Y-m-d')),
				'expiration' => '',
				'incoterm' => '',
				'payment' => $customer->payment_term_id,
				'contact' => $customer->contact()->orderBy('id', 'desc')->first()->id,
				'staff' => Auth::user()->id,
				'currency' => $customer->currency_id,
				'line' => [],
				'product' => [],
				'display' => [],
				'description' => [],
				'unitprice' => [],
				'discount' => [],
				'quantity' => [],
			]);
		}

		$optionArray = SalesQuoteView::generateOptionArrayForTemplate($customerId, true);
		//$optionArray = array();
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
					//'customer' => $customerId,
					'title' => trans('vrm.New quote'),
					//'document' => '????',
					//'status' =>  trans('status.Open'),
					'currencyFormat' => TaxableEntity::find($customerId)->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					//'type' => 'quote',
					'history' => [],
					'action' => trans('forms.Create')
				];

		return view()->first(generateTemplateCandidates('form.sales_quote'), $optionArray);
	}

	public function createQuotePost(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}
		$title = null;

		try {
			DB::transaction(function() use ($request, &$title) {
				$header = SalesHeader::initialize('quote', $request);
				$title = $header->title;
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('crm.Quote ### created.')));
	}

	public function createPostAjax(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$header = null;
		try {
			DB::transaction(function() use ($request, &$header) {
				$header = SalesHeader::initialize('quote', $request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new SalesHeaderResource($header) ]);
	}

	public function updateQuote($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be updated')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// flash-in default value if no errors; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$oldInput = $salesHeaderObj->generateArrayForOldInput();
			$request->session()->flashInput($oldInput);
		}

		$optionArray = SalesQuoteView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
					//'customer' => $customerId,
					'title' => trans('crm.Update quote'),
					//'document' => $salesHeaderObj['title'],
					//'status' =>  trans('status.'.ucfirst($salesHeaderObj['status'])),
					'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					//'type' => 'quote',
					'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Update')
				];

		return view()->first(generateTemplateCandidates('form.sales_quote'), $optionArray);
	}

	public function updateQuotePost($id, Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be updated')));
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				$salesHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$salesHeaderObj['title'], trans('crm.Quote ### updated.')));
	}

	public function updatePostAjax($id, Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote() || $salesHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be updated')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				$salesHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new SalesHeaderResource($salesHeaderObj) ]);
	}

	public function viewQuote($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be viewed')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = SalesQuoteView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					//'customer' => $customerId,
					'title' => trans('crm.View quote'),
					//'document' => $salesHeaderObj['title'],
					//'status' =>  trans('status.'.ucfirst($salesHeaderObj['status'])),
					'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					//'type' => 'quote',
					'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.View PDF')
				];

		return view()->first(generateTemplateCandidates('form.sales_quote'), $optionArray);
	}

	public function loadSalesQuoteAjax(Request $request, $id)
	{
		if ($id) {
			$salesHeaderObj = SalesHeader::find($id);
			$salesDetails = $salesHeaderObj->salesDetail;
			$monetaryFormat = "%0." . $salesHeaderObj->currency->getFormat()['fdigit'] . "f";

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $salesHeaderObj->id,
					'csrf' => csrf_token(),
					'increment' => $salesHeaderObj->title,
					'entity' => $salesHeaderObj->entity_id,
					'inputdate' => DateHelper::dbToGuiDate($salesHeaderObj->order_date),
					'expiration' => DateHelper::dbToGuiDate($salesHeaderObj->order_date),
					'payment' => $salesHeaderObj->payment_term_id,
					'incoterm' => $salesHeaderObj->fob,
					'contact' => $salesHeaderObj->contact_id,
					'reference' => $salesHeaderObj->reference,
					'staff' => $salesHeaderObj->sales_id,
					'currency' => $salesHeaderObj->currency_id,
					'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $salesHeaderObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
					'line' => $salesDetails->pluck('id'),
	        'product' => $salesDetails->pluck('unique_tradable_id'),
	        'display' => $salesDetails->pluck('display_as'),
	        'unitprice' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $salesDetails->pluck('unit_price')->toArray()),
	        'description' =>  $salesDetails->pluck('description'),
	        'quantity' => array_map(function($elem) { return sprintf(env('APP_QUANTITY_FORMAT'), $elem['ordered_quantity'] - $elem['shipped_quantity']); }, $salesDetails->toArray()),
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
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be printed')));
		}
		$pdf = $salesHeaderObj->generatePdf();
		$pdf->Output("Sales ".ucfirst($salesHeaderObj->type)." #".$salesHeaderObj->title.".pdf", "D");
	}

	public function printPostAjax($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		$pdf = $salesHeaderObj->generatePdf();
		$pdf->Output("Sales ".ucfirst($salesHeaderObj->type)." #".$salesHeaderObj->title.".pdf", "D");
	}

	public function approveQuote($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be approved')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// check this person's approval is required and missing.
		if (!$salesHeaderObj->requireApproval(Auth::user()->id)) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be approved')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = SalesQuoteView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					//'customer' => $customerId,
					'title' => trans('crm.Approve quote'),
					//'document' => $salesHeaderObj['title'],
					//'status' =>  trans('status.'.ucfirst($salesHeaderObj['status'])),
					'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					//'type' => 'quote',
					'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => [
							'approve' => trans('forms.Approve'),
							'disapprove' => trans('forms.Disapprove'),
						]
				];

		return view()->first(generateTemplateCandidates('form.sales_quote'), $optionArray);
	}

	public function approveQuotePost($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be approved')));
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				switch ($request->input('submit')) {
				case 'approve':
					$salesHeaderObj->approve(Auth::user()->id, $request->ip());
					break;
				case 'disapprove':
					$salesHeaderObj->disapprove(Auth::user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### processed')));
	}

	public function approvePostAjax($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotQuote() || $salesHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$salesHeaderObj->title, trans('crm.Quote ### can not be approved')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				switch ($request->input('submit')) {
				case 'approve':
					$salesHeaderObj->approve(Auth::user()->id, $request->ip());
					break;
				case 'disapprove':
					$salesHeaderObj->disapprove(Auth::user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => new SalesHeaderResource($salesHeaderObj) ]);
	}
}
