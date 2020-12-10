<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Http\Requests;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\PurchaseDetail;
use App\PurchaseHistory;
use App\PurchaseHeader;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Helpers\PurchaseOrderView;
use App\Helpers\PurchaseProcessView;
use App\Http\Resources\PurchaseHeader as PurchaseHeaderResource;
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

class PurchaseOrderController extends Controller
{
	 // validation rules for 'create', 'update'
	CONST VALIDATION_RULES = [
		'reference' => 'required',
		'incoterm' => 'required',
		'inputdate' => 'required',
		'ivcost.*' => "required|numeric",
		'unitprice.*' => "required|numeric",
		'quantity.*' => "required|numeric",
		'ddate.*' => "required|date",
	];

	public function createOrder($supplierId, Request $request)
	{
		$supplier = TaxableEntity::find($supplierId);
		if ($supplier->isNotActiveSupplier()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "", trans('vrm.Order can not be created')));
		}

		$currencyFormat = TaxableEntity::find($supplierId)->currency->getFormat();
		$fmtr = new \NumberFormatter($currencyFormat['regex'], \NumberFormatter::CURRENCY );
		// flash-in default value if no errors; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'type' => 'order',
				'increment' => '?????',
				'supplier' => $supplierId,
				'inputdate' => DateHelper::dbToGuiDate(date('Y-m-d')),
				'payment' => $supplier->payment_term_id,
				'incoterm' => '',
				'via' => '',
				'tax_rate' => '0',
				'contact' => $supplier->contact()->orderBy('id', 'desc')->first()->id,
				'reference' => '',
				'staff' => Auth::user()->id,
				'currency' => $supplier->currency_id,
				'billing' => $supplier->defaultBillingAddress[0]->id,
				'shipping' => $supplier->defaultShippingAddress[0]->id,
				'notes' => '',
				'line' => [],
				'product' => [],
				'display' => [],
				'ivcost' => [],
				'unitprice' => [],
				'description' => [],
				'quantity' => [],
				'ddate' => [],
				'warehouse' => [],
				'taxable' => [],
				'subtotal' => [],
				'untaxed_subtotal' => $fmtr->format(0),
				'taxed_subtotal' => $fmtr->format(0),
				'tax_amount' => $fmtr->format(0),
				'grand_total' => $fmtr->format(0),
			]);
		}

		$optionArray = PurchaseOrderView::generateOptionArrayForTemplate($supplierId, true);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.New order'),
				//'document' => '????',
				//'status' =>  trans('status.Open'),
				'currencyFormat' => TaxableEntity::find($supplierId)->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'order',
				'history' => [],
				'action' => trans('forms.Create')
			];

		return view()->first(generateTemplateCandidates('form.purchase_order'), $optionArray);
	}

	public function createOrderPost(Request $request)
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
		$title = "?????";

		try {
			DB::transaction(function() use ($request, &$title) {
				$header = PurchaseHeader::initialize('order', $request);
				$title = $header->title;
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace('###', '#'.$title, trans('vrm.Order ### created.')));
	}

	public function updateOrder($id, Request $request)
	{
		// make sure this IS an order, not a return nor quote
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be updated')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// load purchase order detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
			$request->session()->flashInput($oldInput);
		}

		$optionArray = PurchaseOrderView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.Update order'),
				//'document' => $purchaseHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($purchaseHeaderObj['status'])),
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'order',
				'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Update')
			];

		return view()->first(generateTemplateCandidates('form.purchase_order'), $optionArray);
	}

	public function updateOrderPost($id, Request $request)
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

		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be updated')));
		}

		try {
			// update database
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				$purchaseHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### updated.')));
	}

	public function viewOrder($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be viewed')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// load purchase order detail; no need to check error-redirect since this is read only
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseOrderView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.View order'),
				//'document' => $purchaseHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($purchaseHeaderObj['status'])),
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'order',
				'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.purchase_order'), $optionArray);
	}

	public function printOrder($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be printed')));
		}
		$pdf = $purchaseHeaderObj->generatePdf();
		$pdf->Output("Purchase ".ucfirst($purchaseHeaderObj->type)." #".$purchaseHeaderObj->title.".pdf", "D");
	}

	public function approveOrder($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be approved')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// check this person's approval is required and missing.
		if (!$purchaseHeaderObj->requireApproval(Auth::user()->id)) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be approved')));
		}

		// load purchase order detail; no need to check error-redirect since this is read only
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseOrderView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.Approve order'),
				//'document' => $purchaseHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($purchaseHeaderObj['status'])),
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'order',
				'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => [
							'approve' => trans('forms.Approve'),
							'disapprove' => trans('forms.Disapprove'),
						],
			];

		return view()->first(generateTemplateCandidates('form.purchase_order'), $optionArray);
	}

	public function approveOrderPost($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be approved')));
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

		// show meesage of success, and give option to go back to 'dashboard' member function of this controller
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### processed.')));
	}

	public function processOrder($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be processed')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseProcessView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'supplier' => $supplierId,
				'title' => trans('vrm.Process order'),
				//'document' => $purchaseHeaderObj['title'],
				'currencyFormat' => $purchaseHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'order',
				'action' => trans('forms.Process')
			];

		return view()->first(generateTemplateCandidates('form.purchase_process'), $optionArray);
	}

	// this function is not being used.  reserved for future development
	public function processOrderPost($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be processed')));
		}

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				// what to do to process?
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be processed')/*trans('vrm.Order ### processed.')*/));
	}

	public function releaseOrderPost($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOrder() || $purchaseHeaderObj->isNotApproved()) {
			return response()->json(['success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be processed')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				$purchaseHeaderObj->release(auth()->user()->id, $request->ip());
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => new PurchaseHeaderResource($purchaseHeaderObj) ]);
	}
}
