<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use APp\Helpers\ParameterHelper;
use App\Helpers\SalesOrderView;
use App\Helpers\SalesProcessView;
use App\Http\Requests;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\SalesDetail;
use App\SalesHistory;
use App\SalesHeader;
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

class SalesReturnController extends Controller
{
	// validation rules for 'create', 'update'
	CONST VALIDATION_RULES = [
		'reference' => 'required',
		'incoterm' => 'required',
		'inputdate' => 'required',
		'expiration' => 'required',
		'unitprice.*' => "required|numeric|min:0",
		'quantity.*' => "required|numeric|min:0",  // :TODO: need to restrict maximum? (total quantity purchased)
	];

	public function createReturn($customerId, Request $request)
	{
		$customer = TaxableEntity::find($customerId);
		if ($customer->isNotActiveEntity()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "", trans('crm.Return ### can not be created')));
		}

		$currencyFormat = TaxableEntity::find($customerId)->currency->getFormat();
		$fmtr = new \NumberFormatter($currencyFormat['regex'], \NumberFormatter::CURRENCY );
		// flash-in default value if no errors; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'type' => 'return',
				'increment' => '?????',
				'customer' => $customerId,
				'inputdate' => DateHelper::dbToGuiDate(date('Y-m-d')),
				'payment' => $customer->payment_term_id,
				'contact' => $customer->contact()->orderBy('id', 'desc')->first()->id,
				'staff' => Auth::user()->id,
				'currency' => $customer->currency_id,
				'tax_rate' => '0',
				'billing' => $customer->defaultBillingAddress[0]->id,
				'shipping' => $customer->defaultShippingAddress[0]->id,
				'line' => [],
				'product' => [],
				'display' => [],
				'unitprice' => [],
				'description' => [],
				'quantity' => [],
				'discount' => [],
				'disctype' => [],
				'taxable' => [],
				'subtotal' => [],
				'untaxed_subtotal' => $fmtr->format(0),
				'taxed_subtotal' => $fmtr->format(0),
				'tax_amount' => $fmtr->format(0),
				'grand_total' => $fmtr->format(0),
			]);
		}

		$optionArray = SalesOrderView::generateOptionArrayForTemplate($customerId, true);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
				//'customer' => $customerId,
				'title' => trans('crm.New return'),
				//'status' =>  trans('status.Open'),
				//'document' => '????',
				'currencyFormat' => TaxableEntity::find($customerId)->currency->getFormat(true),
				'currencySymbol' => $customer->currency->getSymbol(),
				'post_url' => '/' . $request->path(),
				//'type' => 'return',
				'history' => null,
				'action' => trans('forms.Create')
			];

		return view()->first(generateTemplateCandidates('form.sales_order'), $optionArray);
	}

	public function createReturnPost(Request $request)
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
				$header = SalesHeader::initialize('return', $request);
				$title = $header->title;
			});
		} catch (\Excepion $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('crm.Return ### created.')));
	}

	public function updateReturn($id, Request $request)
	{
		// make sure this IS an return, not a order nor quote
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be updated')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// load sales return detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$oldInput = $salesHeaderObj->generateArrayForOldInput();
			$request->session()->flashInput($oldInput);
		}

		$optionArray = SalesOrderView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
				//'customer' => $customerId,
				'title' => trans('crm.Update return'),
				//'document' => $salesHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($salesHeaderObj['status'])),
				'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
				'currencySymbol' => $salesHeaderObj->currency->getSymbol(),
				'post_url' => '/' . $request->path(),
				//'type' => 'return',
				'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Update')
			];

		return view()->first(generateTemplateCandidates('form.sales_order'), $optionArray);
	}

	public function updateReturnPost($id, Request $request)
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
		if ($salesHeaderObj->isNotReturn() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be updated')));
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
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### updated.')));
	}

	public function viewReturn($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be viewed')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = SalesOrderView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'customer' => $customerId,
				'title' => trans('crm.View return'),
				//'document' => $salesHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($salesHeaderObj['status'])),
				'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
				'currencySymbol' => $salesHeaderObj->currency->getSymbol(),
				'post_url' => '/' . $request->path(),
				//'type' => 'return',
				'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.sales_order'), $optionArray);
	}

	public function printReturn($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be printed')));
		}
		$pdf = $salesHeaderObj->generatePdf();
		$pdf->Output("Sales ".ucfirst($salesHeaderObj->type)." #".$salesHeaderObj->title.".pdf", "D");
	}

	public function approveReturn($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be approved')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// check this person's approval is required and missing.
		if (!$salesHeaderObj->requireApproval(Auth::user()->id)) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be approved')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = SalesOrderView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'customer' => $customerId,
				'title' => trans('crm.Approve order'),
				//'document' => $salesHeaderObj['title'],
				//'status' =>  trans('status.'.ucfirst($salesHeaderObj['status'])),
				'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'return',
				'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => [
							'approve' => trans('forms.Approve'),
							'disapprove' => trans('forms.Disapprove'),
						]
			];

		return view()->first(generateTemplateCandidates('form.sales_order'), $optionArray);
	}

	public function approveReturnPost($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be approved')));
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

		// show meesage of success, and give option to go back to 'dashboard' member function of this controller
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### processed')));
	}

	public function processReturn($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", trans('crm.Return ### can not be processed')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = SalesProcessView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				//'customer' => $customerId,
				'title' => trans('crm.Process return'),
				//'document' => $salesHeaderObj['title'],
				'currencyFormat' => $salesHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				//'type' => 'return',
				'action' => trans('forms.Process')
			];

		return view()->first(generateTemplateCandidates('form.sales_process'), $optionArray);
	}

	// this function is not being used.  reserved for future development
	public function processReturnPost($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotReturn() || $salesHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", trans('crm.Return ### can not be processed')));
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				// process return
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be processed')/*trans('crm.Return ### processed.')*/));
	}
}
