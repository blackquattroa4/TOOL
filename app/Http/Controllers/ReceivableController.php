<?php

namespace App\Http\Controllers;

use App;
use App\Parameter;
use App\ChartAccount;
use App\Helpers\DataSetHelper;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\SalesProcessView;
use App\Helpers\TransactableView;
use App\Http\Resources\TaxableEntity as TaxableEntityResource;
use App\Http\Resources\TransactableHeader as TransactableHeaderResource;
use App\Location;
use App\PaymentTerm;
use App\Rules\SalesDetailQuantityRestriction;
use App\SalesHeader;
use App\SalesDetail;
use App\TaccountTransaction;
use App\TaxableEntity;
use App\TradableTransaction;
use App\TransactableDetail;
use App\TransactableHeader;
use App\TransactableHistory;
use App\User;
use App\WarehouseDetail;
use App\Http\Requests;
use Auth;
use DB;
use Illuminate\Http\Request;
use Session;
use Validator;

class ReceivableController extends Controller
{
	public function create($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if (!$salesHeaderObj->isOrder()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Order ### can not be invoiced')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// load sales order detail; errors imply redirect back, flash input will remove old value
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$oldInput['expiration'] = DateHelper::dbToGuiDate(date("Y-m-d"));
		$oldInput['processing'] = array();
		foreach ($oldInput['product'] as $rid => $product_id) {
			$hadError = (Session::has('alert-danger') || Session::has('alert-warning') || Session::has('errors'));
			// get warehouseDetail object, if product stockable.
			$warehouseDetail = WarehouseDetail::where([['src_table', 'sales_details'], ['src_id', $oldInput['line'][$rid]]])->first();
			$processed = $warehouseDetail ? $warehouseDetail->processed_quantity : 0;
			$unprocessed = ($processed > $oldInput['shippedQuantity'][$rid]) ? ($processed - $oldInput['shippedQuantity'][$rid]) : 0;
			$oldInput['processing'][$rid] = $hadError ? $request->old('processing')[$rid] : $unprocessed;
		}
		$request->session()->flashInput($oldInput);

		$optionArray = SalesProcessView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['currencyFormat'] = $salesHeaderObj->currency->getFormat(true);
		$optionArray['source'] = [
					'customer' => $customerId,
					'title' => trans('finance.Create receivable'),
					'document' => '????',
					'post_url' => '/' . $request->path(),
					'currencySymbol' => $salesHeaderObj->currency->getSymbol(),
					'type' => 'order',
					'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Create')
				];

		return view()->first(generateTemplateCandidates('form.sales_process'), $optionArray);
	}

	public function createPost($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if (!$salesHeaderObj->isOrder() || ($salesHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Order ### can not be invoiced')));
		}

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
			'expiration' => 'required|date',
			'processing.*' => [
					"required",
					"numeric",
					"min:" . (array_reduce(array_keys($request->input('line')), function($carry, $item) use ($request) { return ($carry &= ($request->input('processing.'.$item) == 0)); }, true) ? "1" : "0"),
					new SalesDetailQuantityRestriction($request->input('line'),
																							DateHelper::guiToDbDate($request->input('expiration')),
																							env('ACCOUNT_CONSIGNMENT_INVENTORY') ? -1 : TaxableEntity::theCompany()->id),
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
			DB::transaction(function() use ($request, $salesHeaderObj, &$title) {
				// create the transaction
				$transactableHeaderObj = $salesHeaderObj->createReceivable($request);
				$title = $transactableHeaderObj->title;

				// if $amount is $0, close it.
				if ($transactableHeaderObj->balance == 0) {
					$transactableHeaderObj->close($request);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect()->action('FinanceController@index')->with('alert-success', str_replace("###", "#".$title, trans('finance.Receivable ### created.')));
	}

	public function update($id, Request $request)
	{
		recordAndReportProblem(new \Exception("ReceivableController@update should not be called"));
	}

	public function updatePost($id, Request $request)
	{
		recordAndReportProblem(new \Exception("ReceivableController@updatePost should not be called"));
	}

	public function view($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableInvoice()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Receivable ### can not be viewed')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.View receivable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'currencySymbol' => $transactableHeaderObj->currency->getSymbol(),
				'post_url' => '/' . $request->path(),
				'type' => 'receivable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function printInvoice($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableInvoice()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Receivable ### can not be printed')));
		}
		$pdf = $transactableHeaderObj->generatePdf();
		DataSetHelper::addDataSetValue($transactableHeaderObj, 'flags', 'printed');
		$pdf->Output("Receivable #".$transactableHeaderObj->title.".pdf", "D");
	}

	public function void($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableInvoice() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Receivable ### can not be voided')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.Void receivable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				'type' => 'receivable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Void')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function voidPost($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableInvoice() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Receivable ### can not be voided')));
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

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Receivable ### voided.')));
	}

	public function createCredit($id, Request $request)
	{
		$salesHeaderObj = SalesHeader::find($id);
		if (!$salesHeaderObj->isReturn()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$salesHeaderObj->title, trans('crm.Return ### can not be invoiced')));
		}
		$customerId = $salesHeaderObj->entity_id;

		// load sales order detail; errors imply redirect back, flash input will remove old value
		$oldInput = $salesHeaderObj->generateArrayForOldInput();
		$oldInput['processing'] = array();
		foreach ($oldInput['product'] as $rid => $product_id) {
			$hadError = (Session::has('alert-danger') || Session::has('alert-warning') || Session::has('errors'));
			// get warehouseDetail object, if product stockable.
			$warehouseDetail = WarehouseDetail::where([['src_table', 'sales_details'], ['src_id', $oldInput['line'][$rid]]])->first();
			$processed = $warehouseDetail ? $warehouseDetail->processed_quantity : 0;
			$unprocessed = ($processed > $oldInput['shippedQuantity'][$rid]) ? ($processed - $oldInput['shippedQuantity'][$rid]) : 0;
			$oldInput['processing'][$rid] = $hadError ? $request->old('processing')[$rid] : $unprocessed;
		}
		$request->session()->flashInput($oldInput);

		$optionArray = SalesProcessView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['currencyFormat'] = $salesHeaderObj->currency->getFormat(true);
		$optionArray['source'] = [
					'customer' => $customerId,
					'title' => trans('finance.Create credit'),
					'document' => '????',
					'post_url' => '/' . $request->path(),
					'currencySymbol' => $salesHeaderObj->currency->getSymbol(),
					'type' => 'return',
					'history' => $salesHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Create')
				];

		return view()->first(generateTemplateCandidates('form.sales_process'), $optionArray);
	}

	public function createCreditPost($id, Request $request)
	{
		$salesReturnObj = SalesHeader::find($id);
		if (!$salesReturnObj->isReturn() || ($salesReturnObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$salesReturnObj->title, trans('crm.Return ### can not be invoiced')));
		}

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
			'expiration' => 'required|date',
			'processing.*' => [
					"required",
					"numeric",
					"min:" . (array_reduce(array_keys($request->input('line')), function($carry, $item) use ($request) { return ($carry &= ($request->input('processing.'.$item) == 0)); }, true) ? "1" : "0"),
					new SalesDetailQuantityRestriction($request->input('line'),
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
			DB::transaction(function() use ($request, $salesReturnObj, &$title) {
				// create the transaction
				$transactableHeaderObj = $salesReturnObj->createReceivable($request);
				$title = $transactableHeaderObj->title;

				// if $amount is $0, close it.
				if ($transactableHeaderObj->balance == 0) {
					$transactableHeaderObj->close($request);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('finance.Credit ### created.')));
	}

	public function updateCredit($id, Request $request)
	{
		recordAndReportProblem(new \Exception("ReceivableController@updateCredit should not be called"));
	}

	public function updateCreditPost($id, Request $request)
	{
		recordAndReportProblem(new \Exception("ReceivableController@updateCreditPost should not be called"));
	}

	public function viewCredit($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableCredit()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Credit ### can not be viewed')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.View receivable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'currencySymbol' => $transactableHeaderObj->currency->getSymbol(),
				'post_url' => '/' . $request->path(),
				'type' => 'receivable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.View PDF')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function printCredit($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableCredit()) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Credit ### can not be printed')));
		}
		$pdf = $transactableHeaderObj->generatePdf();
		DataSetHelper::addDataSetValue($transactableHeaderObj, 'flags', 'printed');
		$pdf->Output("Receivable credit #".$transactableHeaderObj->title.".pdf", "D");
	}

	public function voidCredit($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableCredit() || ($transactableHeaderObj->status != 'open')) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Credit ### can not be voided')));
		}
		$entityId = $transactableHeaderObj->entity_id;

		// no need to check error-redirect since this is read only
		$oldInput = $transactableHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = TransactableView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
				'entity' => $entityId,
				'title' => trans('finance.Void receivable'),
				'valid' => $transactableHeaderObj->status != 'void',
				'document' => $oldInput['increment'],
				'status' =>  trans('status.'.ucfirst($transactableHeaderObj['status'])),
				'currencyFormat' => $transactableHeaderObj->currency->getFormat(true),
				'post_url' => '/' . $request->path(),
				'type' => 'receivable',
				'history' => $transactableHeaderObj->history()->orderBy('created_at', 'desc')->get(),
				'action' => trans('forms.Void')
			];

		return view()->first(generateTemplateCandidates('form.transactable_form'), $optionArray);
	}

	public function voidCreditPost($id, Request $request)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		if (!$transactableHeaderObj->isReceivableCredit() || ($transactableHeaderObj->status != 'open')) {
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

	public function receivePayment($id, Request $request)
	{
		// create GUI (receive amount / show available credit / apply receivable)
		$entity = TaxableEntity::find($id);
		$currencyFormat = $entity->currency->getFormat(false);
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		// obtain company cash account
		$accounts = unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value);

		// calculate pending(unapplied) credit
		$availableCredit = DB::select("SELECT
		    				IFNULL((SELECT
		                    SUM(amount) AS 'paid_amount'
		                FROM
		                    taccount_transactions
		                WHERE
		                    credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND debit_t_account_id IN (" . implode(",", $accounts) . ")
														AND src = 'cash_receipt'
		                        AND valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(amount) AS 'dispense_amount'
		                FROM
		                    taccount_transactions
		                WHERE
		                    debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND credit_t_account_id IN (" . implode(",", $accounts) . ")
														AND src = 'cash_expenditure'
		                        AND valid = 1),
		            0) + IFNULL((SELECT
		                    SUM(t1.balance) AS 'debit_balance_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_headers'
		                        AND t2.valid = 1),
		            0) + IFNULL((SELECT
		                    SUM(balance) AS 'debit_balance_part2'
		                FROM
		                    transactable_headers
		                WHERE
		                    id IN (SELECT DISTINCT
		                            t1.transactable_header_id
		                        FROM
		                            transactable_details t1
		                                JOIN
		                            taccount_transactions t2 ON t2.src_id = t1.id
		                        WHERE
		                            t2.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                                AND t2.src = 'transactable_details'
		                                AND t2.valid = 1)),
		            0) - IFNULL((SELECT
		                    SUM(t1.balance) AS 'credit_balance_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_headers'
		                        AND t2.valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(balance) AS 'credit_balance_part2'
		                FROM
		                    transactable_headers
		                WHERE
		                    id IN (SELECT DISTINCT
		                            t1.transactable_header_id
		                        FROM
		                            transactable_details t1
		                                JOIN
		                            taccount_transactions t2 ON t2.src_id = t1.id
		                        WHERE
		                            t2.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                                AND t2.src = 'transactable_details'
		                                AND t2.valid = 1)),
		            0) + IFNULL((SELECT
		                    SUM(t2.total) AS 'credit_total_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    (SELECT
		                        transactable_header_id,
		                            SUM(transacted_amount - discount_amount + tax_amount) AS 'total'
		                    FROM
		                        transactable_details
		                    GROUP BY transactable_header_id) t2 ON t2.transactable_header_id = t1.id
		                        JOIN
		                    taccount_transactions t3 ON t3.src_id = t1.id
		                        AND t3.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t3.src = 'transactable_headers'
		                        AND t3.valid = 1),
		            0) + IFNULL((SELECT
		                    SUM(t1.transacted_amount - t1.discount_amount + t1.tax_amount) AS 'credit_total_par2'
		                FROM
		                    transactable_details t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_details'
		                        AND t2.valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(t2.total) AS 'debit_total_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    (SELECT
		                        transactable_header_id,
		                            SUM(transacted_amount - discount_amount + tax_amount) AS 'total'
		                    FROM
		                        transactable_details
		                    GROUP BY transactable_header_id) t2 ON t2.transactable_header_id = t1.id
		                        JOIN
		                    taccount_transactions t3 ON t3.src_id = t1.id
		                        AND t3.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t3.src = 'transactable_headers'
		                        AND t3.valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(t1.transacted_amount - t1.discount_amount + t1.tax_amount) AS 'debit_total_part2'
		                FROM
		                    transactable_details t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_details'
		                        AND t2.valid = 1),
		            0) AS 'available_credit';")[0]->available_credit;

		$availableCredit = sprintf("%0.".$currencyFormat['fdigit']."f", $availableCredit);

		// prepare available bank account
		$bankAccounts = array();
		foreach (ChartAccount::whereIn('id', unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value))->where('active', 1)->get() as $oneAccount) {
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

		// load date; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
					'inputdate' => DateHelper::dbToGuiDate(date("Y-m-d")),
				]);
		}

		// put all parameters together for blade-template
		$parameter = [
			'customer_id' => $id,
			'code' => $entity->code,
			'currencyFormat' => $entity->currency->getFormat(true),
			'available' => $availableCredit,
			'bankaccount' => $bankAccounts,
			'phrases' => [
					'title' => trans('forms.Receive payment') . "&nbsp;/&nbsp;" . trans('forms.Apply payment'),
					'direction' => trans('forms.From'),
					'transact_term' => trans('forms.Amount received'),
				],
			'transactables' => $transactables,
			'initialCredit' => $fmtr->format($availableCredit),
			'post_url' => "/" . $request->path(),
		];

		return view()->first(generateTemplateCandidates('form.payment'), $parameter);
	}

	public function receivePaymentAjax(Request $request, $id)
	{
		$entity = TaxableEntity::find($id);
		$currencyFormat = $entity->currency->getFormat(false);
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		// obtain company cash account
		$accounts = unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value);

		// obtain credit carried-forward
		$availableCredit = DB::select("SELECT
		    				IFNULL((SELECT
		                    SUM(amount) AS 'paid_amount'
		                FROM
		                    taccount_transactions
		                WHERE
		                    credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND debit_t_account_id IN (" . implode(",", $accounts) . ")
														AND src = 'cash_receipt'
		                        AND valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(amount) AS 'dispense_amount'
		                FROM
		                    taccount_transactions
		                WHERE
		                    debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND credit_t_account_id IN (" . implode(",", $accounts) . ")
														AND src = 'cash_expenditure'
		                        AND valid = 1),
		            0) + IFNULL((SELECT
		                    SUM(t1.balance) AS 'debit_balance_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_headers'
		                        AND t2.valid = 1),
		            0) + IFNULL((SELECT
		                    SUM(balance) AS 'debit_balance_part2'
		                FROM
		                    transactable_headers
		                WHERE
		                    id IN (SELECT DISTINCT
		                            t1.transactable_header_id
		                        FROM
		                            transactable_details t1
		                                JOIN
		                            taccount_transactions t2 ON t2.src_id = t1.id
		                        WHERE
		                            t2.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                                AND t2.src = 'transactable_details'
		                                AND t2.valid = 1)),
		            0) - IFNULL((SELECT
		                    SUM(t1.balance) AS 'credit_balance_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_headers'
		                        AND t2.valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(balance) AS 'credit_balance_part2'
		                FROM
		                    transactable_headers
		                WHERE
		                    id IN (SELECT DISTINCT
		                            t1.transactable_header_id
		                        FROM
		                            transactable_details t1
		                                JOIN
		                            taccount_transactions t2 ON t2.src_id = t1.id
		                        WHERE
		                            t2.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                                AND t2.src = 'transactable_details'
		                                AND t2.valid = 1)),
		            0) + IFNULL((SELECT
		                    SUM(t2.total) AS 'credit_total_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    (SELECT
		                        transactable_header_id,
		                            SUM(transacted_amount - discount_amount + tax_amount) AS 'total'
		                    FROM
		                        transactable_details
		                    GROUP BY transactable_header_id) t2 ON t2.transactable_header_id = t1.id
		                        JOIN
		                    taccount_transactions t3 ON t3.src_id = t1.id
		                        AND t3.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t3.src = 'transactable_headers'
		                        AND t3.valid = 1),
		            0) + IFNULL((SELECT
		                    SUM(t1.transacted_amount - t1.discount_amount + t1.tax_amount) AS 'credit_total_par2'
		                FROM
		                    transactable_details t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.credit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_details'
		                        AND t2.valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(t2.total) AS 'debit_total_part1'
		                FROM
		                    transactable_headers t1
		                        JOIN
		                    (SELECT
		                        transactable_header_id,
		                            SUM(transacted_amount - discount_amount + tax_amount) AS 'total'
		                    FROM
		                        transactable_details
		                    GROUP BY transactable_header_id) t2 ON t2.transactable_header_id = t1.id
		                        JOIN
		                    taccount_transactions t3 ON t3.src_id = t1.id
		                        AND t3.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t3.src = 'transactable_headers'
		                        AND t3.valid = 1),
		            0) - IFNULL((SELECT
		                    SUM(t1.transacted_amount - t1.discount_amount + t1.tax_amount) AS 'debit_total_part2'
		                FROM
		                    transactable_details t1
		                        JOIN
		                    taccount_transactions t2 ON t2.src_id = t1.id
		                WHERE
		                    t2.debit_t_account_id = " . $entity->transaction_t_account_id . "
		                        AND t2.src = 'transactable_details'
		                        AND t2.valid = 1),
		            0) AS 'available_credit';")[0]->available_credit;

		$transactables = TransactableHeader::where('entity_id', $id)->where('status', 'open')->get();

		return [
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'entity_id' => $entity->id,
				'code' => $entity->code,
				'currency_regex' => $entity->currency->getFormat(true)['regex'],
				'currency_symbol' => $currencyFormat['symbol'],
				'currency_min' => $currencyFormat['min'],
				'available' => sprintf("%0.".$currencyFormat['fdigit']."f", $availableCredit),
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
		];
	}

	public function receivePaymentPost($id, Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(),
			[
				'bank_account' => 'required',
				'amount_received' => 'required|numeric',
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
			DB::transaction(function() use ($request, $id) {
				// process payment received.
				if (floatval($request->input('amount_received')) > 0) {
					$entity = TaxableEntity::find($id);
					TaccountTransaction::create([
						'debit_t_account_id' => $request->input('bank_account'),
						'credit_t_account_id' => $entity->transaction_t_account_id,
						'amount' => $request->input('amount_received'),
						'currency_id' => ChartAccount::find($request->input('bank_account'))->currency_id,
						'book_date' => DateHelper::guiToDbDate($request->input('inputdate')),
						'src' => 'cash_receipt',
						'src_id' => 0,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => $request->input('reference'),
					]);
				}

				// process payment applied.
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
						}
						event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('finance.Payment received/applied'));
	}

	public function receivePaymentPostAjax(Request $request, $id)
	{
		// array:7 [
		//   "_token" => "ynRSew4HL8eLaqgKICSqRI5HE1byXIaV6SCX6eWT"
		//   "bank_account" => "7"
		//   "inputdate" => "4/1/2020"
		//   "reference" => ""
		//   "amount_received" => "0"
		//   "line" => array:2 [
		//     0 => "186"
		//     1 => "373"
		//   ]
		//   "transactable" => array:2 [
		//     0 => "0.01"
		//     1 => "0"
		//   ]
		// ]

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(),
			[
				'bank_account' => 'required|numeric',
				'amount_received' => 'required|numeric',
				'inputdate' => 'required',
				'reference' => 'required',
				'transactable.*' => "required|numeric",
			]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$entity = TaxableEntity::find($id);
		$receivables = TransactableHeader::find($request->input('line'));

		try {
			DB::transaction(function() use ($request, $entity, $receivables) {
				// process payment received.
				if (floatval($request->input('amount_received')) > 0) {
					TaccountTransaction::create([
						'debit_t_account_id' => $request->input('bank_account'),
						'credit_t_account_id' => $entity->transaction_t_account_id,
						'amount' => $request->input('amount_received'),
						'currency_id' => ChartAccount::find($request->input('bank_account'))->currency_id,
						'book_date' => DateHelper::guiToDbDate($request->input('inputdate')),
						'src' => 'cash_receipt',
						'src_id' => 0,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => $request->input('reference'),
					]);
				}

				$appliedAmount = array_combine($request->input('line'), $request->input('transactable'));

				// process payment applied.
				if ($receivables->count()) {
					// apply amount of each invoice
					foreach ( $receivables as $transactable ) {
						$amountProcessing = floatval($appliedAmount[$transactable->id]);
						if ($amountProcessing) {
							$transactable->balance -= $amountProcessing;
							$transactable->save();
							TransactableHistory::create([
								'src' => 'transactable_headers',
								'src_id' => $transactable->id,
								'amount' => $appliedAmount[$transactable->id],
								'staff_id' => Auth::user()->id,
								'machine' => $request->ip(),
								'process_status' => $transactable->isCredit() ? 'debited' : 'credited',
								'notes' => $request->input('reference'),
							]);
							event(new \App\Events\TransactableUpsertEvent($transactable));
						}
						if ($transactable->balance == 0) {
							$transactable->close($request);
						}
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [
				'entity' => new TaxableEntityResource($entity),
				'receivables' => TransactableHeaderResource::collection($receivables)
			]
		]);
	}

}
