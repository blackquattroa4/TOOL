<?php

namespace App\Http\Controllers;

use App;
use App\ChartAccount;
use App\Helpers\DataSetHelper;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Helpers\PurchaseProcessView;
use App\Helpers\TransactableView;
use App\Http\Requests;
use App\Http\Resources\TaxableEntity as TaxableEntityResource;
use App\Http\Resources\TransactableHeader as TransactableHeaderResource;
use App\ExpenseHeader;
use App\Parameter;
use App\PaymentTerm;
use App\PurchaseHeader;
use App\PurchaseDetail;
use App\Rules\PurchaseDetailQuantityRestriction;
use App\TaxableEntity;
use App\TaccountTransaction;
use App\TransactableDetail;
use App\TransactableHeader;
use App\TransactableHistory;
use App\UniqueTradable;
use App\User;
use App\WarehouseDetail;
use Auth;
use DB;
use Illuminate\Http\Request;
use Session;
use Validator;

class PayableController extends Controller
{
	public function create($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if (!$purchaseHeaderObj->isReturn()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable can not be created')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// load purchase order detail
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$oldInput['expiration'] = DateHelper::dbToGuiDate(date("Y-m-d"));
		$oldInput['processing'] = array();
		foreach ($oldInput['product'] as $rid => $product_id) {
			$hadError = (Session::has('alert-danger') || Session::has('alert-warning') || Session::has('errors'));
			// get warehouseDetail object, if product stockable.
			$warehouseDetail = WarehouseDetail::where([['src_table', 'purchase_details'], ['src_id', $oldInput['line'][$rid]]])->first();
			$processed = $warehouseDetail ? $warehouseDetail->processed_quantity : 0;
			$unprocessed = ($processed > $oldInput['shippedQuantity'][$rid]) ? ($processed - $oldInput['shippedQuantity'][$rid]) : 0;
			$oldInput['processing'][$rid] = $hadError ? $request->old('processing')[$rid] : $unprocessed;
		}
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseProcessView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					'supplier' => $supplierId,
					'title' => trans('finance.Create payable'),
					'document' => '????',
					'post_url' => '/' . $request->path(),
					'type' => 'return',
					'history' => $purchaseHeaderObj->history()->orderby('created_at', 'desc')->get(),
					'action' => trans('forms.Create')
				];

		return view()->first(generateTemplateCandidates('form.purchase_process'), $optionArray);
	}

	public function createPost($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if (!$purchaseHeaderObj->isReturn() || ($purchaseHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Return ### can not be invoiced')));
		}

		$allZero = array_reduce(array_keys($request->input('line')), function($carry, $item) use ($request) { return ($carry &= ($request->input('processing.'.$item) == 0)); }, true);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
				'expiration' => 'required|date',
				'processing.*' => [
					"required",
					"numeric",
					"min:" . ($allZero ? "1" : "0"),
					new PurchaseDetailQuantityRestriction($request->input('line'),
																								DateHelper::guiToDbDate($request->input('expiration')),
																								TaxableEntity::theCompany()->id),
				],
			]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect(HistoryHelper::goBackPages(1))
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors
					->withInput($request->all()); // send back the input so that we can repopulate the form
		}
		$title = null;

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj, &$title) {
				// create the transaction
				$transactableHeaderObj = $purchaseHeaderObj->createPayable($request);
				$title = $transactableHeaderObj->title;

				// if amount is 0, close it.
				if ($transactableHeaderObj->balance == 0) {
					$transactableHeaderObj->close($request);
					if (($transactableHeaderObj->src_table == 'expense_headers') &&
						($transactableHeaderObj->src_id > 0)) {
						$expenseHdr = ExpenseHeader::find($transactableHeaderObj->src_id);
						$expenseHdr->update([ 'status' => 'paid' ]);
						event(new \App\Events\ExpenseUpsertEvent($expenseHdr));
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		if ($title) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('finance.Payable ### created.')));
		} else {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Return ### can not be invoiced')));
		}
	}

	public function update($id, Request $request)
	{
		recordAndReportProblem(new \Exception("PayableController@update should not be called"));
	}

	public function updatePost($id, Request $request)
	{
		recordAndReportProblem(new \Exception("PayableController@updatePost should not be called"));
	}

	public function view($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableInvoice()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable ### can not be viewed')));
		}

		$entityId = $transactableHeaderObj->entity_id;

		// load transactable detail; no need to check error-redirect, since this read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.View payable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				'type' => 'payable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function printInvoice($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableInvoice()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable ### can not be printed')));
		}
		$pdf = $transactableHeaderObj->generatePdf();
		DataSetHelper::addDataSetValue($transactableHeaderObj, 'flags', 'printed');
		$pdf->Output("Payable #".$transactableHeaderObj->title.".pdf", "D");
	}

	public function void($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableInvoice() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable ### can not be voided')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// load transactable detail; no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.View payable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				'type' => 'payable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Void')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function voidPost($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableInvoice() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Payable ### can not be voided')));
		}

		try {
			DB::transaction(function() use ($request, $transactableHeaderObj) {
				$transactableHeaderObj->void($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Payable ### voided.')));
	}

	public function createCredit($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if (!$purchaseHeaderObj->isOrder()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable can not be created')));
		}
		$supplierId = $purchaseHeaderObj->entity_id;

		// load purchase order detail
		$oldInput = $purchaseHeaderObj->generateArrayForOldInput();
		$oldInput['expiration'] = DateHelper::dbToGuiDate(date("Y-m-d"));
		$oldInput['processing'] = array();
		foreach ($oldInput['product'] as $rid => $product_id) {
			$hadError = (Session::has('alert-danger') || Session::has('alert-warning') || Session::has('errors'));
			// get warehouseDetail object, if product stockable.
			$warehouseDetail = WarehouseDetail::where([['src_table', 'purchase_details'], ['src_id', $oldInput['line'][$rid]]])->first();
			$processed = $warehouseDetail ? $warehouseDetail->processed_quantity : 0;
			$unprocessed = ($processed > $oldInput['shippedQuantity'][$rid]) ? ($processed - $oldInput['shippedQuantity'][$rid]) : 0;
			$oldInput['processing'][$rid] = $hadError ? $request->old('processing')[$rid] : $unprocessed;
		}
		$request->session()->flashInput($oldInput);

		$optionArray = PurchaseProcessView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					'supplier' => $supplierId,
					'title' => trans('finance.Create credit'),
					'document' => '????',
					'post_url' => '/' . $request->path(),
					'type' => 'order',
					'history' => $purchaseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Create')
				];

		return view()->first(generateTemplateCandidates('form.purchase_process'), $optionArray);
	}

	public function createCreditPost($id, Request $request)
	{
		$purchaseHeaderObj = PurchaseHeader::find($id);
		if (!$purchaseHeaderObj->isOrder() || ($purchaseHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be invoiced')));
		}

		$allZero = array_reduce(array_keys($request->input('line')), function($carry, $item) use ($request) { return ($carry &= ($request->input('processing.'.$item) == 0)); }, true);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
				'expiration' => 'required|date',
				'processing.*' => [
					"required",
					"numeric",
					"min:" . ($allZero ? "1" : "0"),
					new PurchaseDetailQuantityRestriction($request->input('line'),
																								DateHelper::guiToDbDate($request->input('expiration')),
																								TaxableEntity::theCompany()->id),
				],
			]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect(HistoryHelper::goBackPages(1))
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors
					->withInput($request->all()); // send back the input so that we can repopulate the form
		}
		$title = null;

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj, &$title) {
				// create the transaction
				$transactableHeaderObj = $purchaseHeaderObj->createPayable($request);
				$title = $transactableHeaderObj->title;

				// if amount is 0, close it.
				if ($transactableHeaderObj->balance == 0) {
					$transactableHeaderObj->close($request);
					if (($transactableHeaderObj->src_table == 'expense_headers') &&
						($transactableHeaderObj->src_id > 0)) {
						$expenseHdr = ExpenseHeader::find($transactableHeaderObj->src_id);
						$expenseHdr->update([ 'status' => 'paid' ]);
						event(new \App\Events\ExpenseUpsertEvent($expenseHdr));
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		if ($title) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('finance.Payable ### created.')));
		} else {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be invoiced')));
		}
	}

	public function updateCredit($id, Request $request)
	{
		recordAndReportProblem(new \Exception("PayableController@updateCredit should not be called"));
	}

	public function updateCreditPost($id, Request $request)
	{
		recordAndReportProblem(new \Exception("PayableController@updateCreditPost should not be called"));
	}

	public function viewCredit($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableCredit()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable credit ### can not be viewed')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// load transactable detail; no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.View payable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				'type' => 'payable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function printCredit($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableCredit()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable credit ### can not be printed')));
		}
		$pdf = $transactableHeaderObj->generatePdf();
		DataSetHelper::addDataSetValue($transactableHeaderObj, 'flags', 'printed');
		$pdf->Output("Payable credit #".$transactableHeaderObj->title.".pdf", "D");
	}

	public function voidCredit($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableCredit() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('finance.Payable credit ### can not be voided')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// load transactable detail; no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.Void payable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				'type' => 'payable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Void')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function voidCreditPost($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isPayableCredit() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Credit ### can not be voided')));
		}

		try {
			DB::transaction(function() use ($request, $transactableHeaderObj) {
				// void the transaction
				$transactableHeaderObj->void($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Credit ### voided.')));
	}

	public function issuePayment($id, Request $request)
	{
		// load date; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
					'inputdate' => DateHelper::dbToGuiDate(date("Y-m-d")),
				]);
		}

		// create GUI (receive amount / show available credit / apply receivable)
		$entity = TaxableEntity::find($id);
		$currencyFormat = $entity->currency->getFormat(false);
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		// prepare available bank account
		$bankAccounts = array();
		foreach (ChartAccount::whereIn('id', unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value))->where('active', 1)->get() as $oneAccount) {
			$bankAccounts[] = [
				'id' => $oneAccount->id,
				'description' => $oneAccount->description,
			];
		}

		foreach (ChartAccount::whereIn('id', unserialize(Parameter::where('key', 'credit_card_t_account_ids')->first()->value))->where('active', 1)->get() as $oneAccount) {
			$bankAccounts[] = [
				'id' => $oneAccount->id,
				'description' => $oneAccount->description,
			];
		}

		// gather all unclosed transactables.
		$transactables = array();
		foreach (TransactableHeader::where('entity_id', $id)->where('status', 'open')->get() as $oneTransactable) {
			$transactables[] = [
				'id' => $oneTransactable->id,
				'title' => $oneTransactable->title,
				'credit' => $oneTransactable->isCredit(),
				'incur' => $oneTransactable->incur_date,
				'due' => $oneTransactable->approx_due_date,
				'summary' => $oneTransactable->reference,
				'total' => $fmtr->format($oneTransactable->totalAmount()),
				'balance_raw' => sprintf("%0.".$currencyFormat['fdigit']."f" , $oneTransactable->balance),
				'balance' =>  $fmtr->format($oneTransactable->balance),
			];
		}

		// put all parameters together for blade-template
		$parameter = [
			'customer_id' => $id,
			'code' => $entity->code,
			'initialAmount' => $fmtr->format(0),
			'currencyFormat' => $entity->currency->getFormat(true),
			'bankaccount' => $bankAccounts,
			'phrases' => [
					'title' => trans('forms.Issue payment') . "&nbsp;/&nbsp;" . trans('forms.Apply payment'),
					'direction' => trans('forms.To'),
					'transact_term' => trans('forms.Amount issued'),
				],
			'transactables' => $transactables,
			'post_url' => "/" . $request->path(),
		];
		return view()->first(generateTemplateCandidates('form.expenditure'), $parameter);
	}

	public function disbursePaymentAjax($id, Request $request)
	{

		// create GUI (receive amount / show available credit / apply receivable)
		$entity = TaxableEntity::find($id);
		$currencyFormat = $entity->currency->getFormat(false);
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		// gather all unclosed transactables.
		$transactables = TransactableHeader::where('entity_id', $id)->where('status', 'open')->get();

		// put all parameters together for blade-template
		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'entity_id' => $entity->id,
				'code' => $entity->code,
				'currency_regex' => $entity->currency->getFormat(true)['regex'],
				'currency_symbol' => $currencyFormat['symbol'],
				'currency_min' => $currencyFormat['min'],
				'transactable_id' => $transactables->pluck('id'),
				'transactable_title' => $transactables->pluck('title'),
				'transactable_date' => $transactables->map(function ($item) { return DateHelper::dbToGuiDate($item->incur_date); }),
				'transactable_duedate' => $transactables->map(function ($item) { return DateHelper::dbToGuiDate($item->approx_due_date); }),
				'transactable_summary' => $transactables->pluck('reference'),
				'transactable_credit' => $transactables->map(function ($item) { return $item->isCredit(); }),
				'transactable_total' => $transactables->map(function ($item) use ($fmtr) { return $fmtr->format($item->totalAmount()); }),
				'transactable_balance' => $transactables->map(function ($item) use ($fmtr) { return $fmtr->format($item->balance); }),
				'transactable_balance_raw' => $transactables->map(function ($item) use ($currencyFormat) { return sprintf("%0." . $currencyFormat['fdigit'] . "f", $item->balance); }),
			]
		]);
	}

	public function issuePaymentPost($sid, Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(),
			[
				'bank_account' => 'required',
				'inputdate' => 'required',
				'reference' => 'required',
				'transactable.*' => "required|numeric",
			]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect(HistoryHelper::goBackPages(1))
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors
					->withInput($request->all()); // send back the input so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request, $sid) {
				// some should be converted into negative number
				$amountIssued = 0;
				foreach ($request->input('transactable') as $key => $value)
				{
					$header = TransactableHeader::find($key);
					if ($header->isInvoice()) {
						$amountIssued -= $value;
					} elseif ($header->isCredit()) {
						$amountIssued += $value;
					}
				}

				$paymentAccount = ChartAccount::find($request->input('bank_account'));
				// process payment issued.
				if ($amountIssued > 0) {
					$entity = TaxableEntity::find($sid);
					if ($paymentAccount->type == 'liability') {
						// create a transactable header and associated history
						$transferee = TaxableEntity::where('transaction_t_account_id', $paymentAccount->id)->first();
						$paymentTerm = PaymentTerm::find($transferee->payment_term_id);
						$transferredHeader = TransactableHeader::create([
								'title' => ParameterHelper::getNextSequence('transaction_number'),
								'src_table' => 'expense_headers',
								'src_id' => 0,
								'flags' => '',
								'reference' => $request->input('reference') . " " . $request->input('inputdate'),
								'entity_id' => $transferee->id,
								'contact_id' => $transferee->contact->max('id'),
								'staff_id' => auth()->user()->id,
								'status' => 'open',
								'balance' => $amountIssued,
								'billing_address_id' => $transferee->defaultBillingAddress[0]->id,
								'shipping_address_id' => $transferee->defaultShippingAddress[0]->id,
								'payment_term_id' => $transferee->payment_term_id,
								'incur_date' => DateHelper::guiToDbDate($request->input('inputdate')),
								'approx_due_date' => date("Y-m-d", strtotime(DateHelper::guiToDbDate($request->input('inputdate')) . "+" . $paymentTerm->grace_days . " days")),
								'tax_rate' => 0,
								'currency_id' => $transferee->currency_id,
								'notes' => 'credit transfer',
								'internal_notes' => 'credit transfer',
							]);
						// create transactable history
						TransactableHistory::create([
							'src' => 'transactable_headers',
							'src_id' => $transferredHeader->id,
							'amount' => $amountIssued,
							'staff_id' => auth()->user()->id,
							'machine' => $request->ip(),
							'process_status' => 'created',
							'notes' => 'credit transfer',
						]);
						// grab A/P transfer expendable-item
						$uniqueTradable = UniqueTradable::getApTransferItem();
						// create transactable detail and associated history
						$transferredDetail = TransactableDetail::create([
								'transactable_header_id' => $transferredHeader->id,
								'src_table' => 'expense_details',
								'src_id' => 0,
								'unique_tradable_id' => $uniqueTradable->id,
								'display_as' => $uniqueTradable->sku,
								'description' => 'credit transfer',
								'unit_price' => $amountIssued,
								'discount' => 0,
								'discount_type' => 'amount',
								'transacted_quantity' => 1,
								'transacted_amount' => $amountIssued,
								'discount_amount' => 0,
								'tax_amount' => 0,
								'status' => 'valid',
							]);
						TransactableHistory::create([
							'src' => 'transactable_details',
							'src_id' => $transferredDetail->id,
							'amount' => $amountIssued,
							'staff_id' => Auth::user()->id,
							'machine' => $request->ip(),
							'process_status' => 'created',
							'notes' => 'credit transfer',
						]);
						event(new \App\Events\TransactableUpsertEvent($transferredHeader));
					}
					TaccountTransaction::create([
						'debit_t_account_id' => $entity->transaction_t_account_id,
						'credit_t_account_id' => $paymentAccount->id,
						'amount' => $amountIssued,
						'currency_id' => ChartAccount::find($request->input('bank_account'))->currency_id,
						'book_date' => DateHelper::guiToDbDate($request->input('inputdate')),
						'src' => 'cash_expenditure',
						'src_id' => 0,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => $request->input('reference'),
					]);
				}

				// process payment issued.
				if ($request->input('transactable')) {
					// apply amount of each invoice
					foreach ($request->input('transactable') as $idx => $amount) {
						$transactableHeaderObj = TransactableHeader::find($idx);
						$transactableHeaderObj->balance -= floatval($amount);
						$transactableHeaderObj->save();
						TransactableHistory::create([
							'src' => 'transactable_headers',
							'src_id' => $idx,
							'amount' => $amount,
							'staff_id' => Auth::user()->id,
							'machine' => $request->ip(),
							'process_status' => $transactableHeaderObj->isCredit() ? 'debited' : 'credited',
							'notes' => $request->input('reference'),
						]);
						if ($transactableHeaderObj->balance == 0) {
							$transactableHeaderObj->close($request);
							if (($transactableHeaderObj->src_table == 'expense_headers') &&
							 	($transactableHeaderObj->src_id > 0)) {
								$expenseHdr = ExpenseHeader::find($transactableHeaderObj->src_id);
								$expenseHdr->update([ 'status' => 'paid' ]);
								event(new \App\Events\ExpenseUpsertEvent($expenseHdr));
							}
						}
						event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));
					}
				}
			});
		} catch (\Exception $e){
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('finance.Payment issued'));
	}

	public function disbursePaymentPostAjax(Request $request, $id)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(),
			[
				'bank_account' => 'required',
				'inputdate' => 'required',
				'reference' => 'required',
				'transactable.*' => "required|numeric",
			]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		try {
			DB::transaction(function() use ($request, $id) {

				$headerAmountMap = array_combine($request->input('line'), $request->input('transactable'));
				// some should be converted into negative number
				$amountIssued = TransactableHeader::find($request->input('line'))->sum(function ($item) use ($headerAmountMap) {
					return $item->isInvoice() ? -$headerAmountMap[$item->id] : $headerAmountMap[$item->id];
				});

				$paymentAccount = ChartAccount::find($request->input('bank_account'));
				// process payment issued.
				if ($amountIssued > 0) {
					$entity = TaxableEntity::find($id);
					if ($paymentAccount->type == 'liability') {
						// create a transactable header and associated history
						$transferee = TaxableEntity::where('transaction_t_account_id', $paymentAccount->id)->first();
						$paymentTerm = PaymentTerm::find($transferee->payment_term_id);
						$transferredHeader = TransactableHeader::create([
								'title' => ParameterHelper::getNextSequence('transaction_number'),
								'src_table' => 'expense_headers',
								'src_id' => 0,
								'flags' => '',
								'reference' => $request->input('reference') . " " . $request->input('inputdate'),
								'entity_id' => $transferee->id,
								'contact_id' => $transferee->contact->max('id'),
								'staff_id' => auth()->user()->id,
								'status' => 'open',
								'balance' => $amountIssued,
								'billing_address_id' => $transferee->defaultBillingAddress[0]->id,
								'shipping_address_id' => $transferee->defaultShippingAddress[0]->id,
								'payment_term_id' => $transferee->payment_term_id,
								'incur_date' => DateHelper::guiToDbDate($request->input('inputdate')),
								'approx_due_date' => date("Y-m-d", strtotime(DateHelper::guiToDbDate($request->input('inputdate')) . "+" . $paymentTerm->grace_days . " days")),
								'tax_rate' => 0,
								'currency_id' => $transferee->currency_id,
								'notes' => 'credit transfer',
								'internal_notes' => 'credit transfer',
							]);
						// create transactable history
						TransactableHistory::create([
							'src' => 'transactable_headers',
							'src_id' => $transferredHeader->id,
							'amount' => $amountIssued,
							'staff_id' => auth()->user()->id,
							'machine' => $request->ip(),
							'process_status' => 'created',
							'notes' => 'credit transfer',
						]);
						// grab A/P transfer expendable-item
						$uniqueTradable = UniqueTradable::getApTransferItem();
						// create transactable detail and associated history
						$transferredDetail = TransactableDetail::create([
								'transactable_header_id' => $transferredHeader->id,
								'src_table' => 'expense_details',
								'src_id' => 0,
								'unique_tradable_id' => $uniqueTradable->id,
								'display_as' => $uniqueTradable->sku,
								'description' => 'credit transfer',
								'unit_price' => $amountIssued,
								'discount' => 0,
								'discount_type' => 'amount',
								'transacted_quantity' => 1,
								'transacted_amount' => $amountIssued,
								'discount_amount' => 0,
								'tax_amount' => 0,
								'status' => 'valid',
							]);
						TransactableHistory::create([
							'src' => 'transactable_details',
							'src_id' => $transferredDetail->id,
							'amount' => $amountIssued,
							'staff_id' => Auth::user()->id,
							'machine' => $request->ip(),
							'process_status' => 'created',
							'notes' => 'credit transfer',
						]);
						event(new \App\Events\TransactableUpsertEvent($transferredHeader));
					}
					TaccountTransaction::create([
						'debit_t_account_id' => $entity->transaction_t_account_id,
						'credit_t_account_id' => $paymentAccount->id,
						'amount' => $amountIssued,
						'currency_id' => ChartAccount::find($request->input('bank_account'))->currency_id,
						'book_date' => DateHelper::guiToDbDate($request->input('inputdate')),
						'src' => 'cash_expenditure',
						'src_id' => 0,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => $request->input('reference'),
					]);
				}

				// process payment issued.
				if (count($headerAmountMap)) {
					// apply amount of each invoice
					foreach ($request->input('line') as $transactable_id) {
						$transactableHeaderObj = TransactableHeader::find($transactable_id);
						$amountProcessing = floatval($headerAmountMap[$transactableHeaderObj->id]);
						if ($amountProcessing) {
							$transactableHeaderObj->balance -= $amountProcessing;
							$transactableHeaderObj->save();
							TransactableHistory::create([
								'src' => 'transactable_headers',
								'src_id' => $transactableHeaderObj->id,
								'amount' => $headerAmountMap[$transactableHeaderObj->id],
								'staff_id' => Auth::user()->id,
								'machine' => $request->ip(),
								'process_status' => $transactableHeaderObj->isCredit() ? 'debited' : 'credited',
								'notes' => $request->input('reference'),
							]);
							event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));
						}
						if ($transactableHeaderObj->balance == 0) {
							$transactableHeaderObj->close($request);
							if (($transactableHeaderObj->src_table == 'expense_headers') &&
							 	($transactableHeaderObj->src_id > 0)) {
								$expenseHdr = ExpenseHeader::find($transactableHeaderObj->src_id);
								$expenseHdr->update([ 'status' => 'paid' ]);
								event(new \App\Events\ExpenseUpsertEvent($expenseHdr));
							}
						}
					}
				}
			});
		} catch (\Exception $e){
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [
				'entity' => new TaxableEntityResource(TaxableEntity::find($id)),
				'payables' => TransactableHeaderResource::collection(TransactableHeader::find($request->input('line')))
			]
		]);
	}
}
