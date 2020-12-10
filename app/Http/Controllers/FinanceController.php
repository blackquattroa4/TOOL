<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Downloadable;
use App\ExpenseHeader;
use App\ExpenseDetail;
use App\FinancialStatementPdf;
use App\Helpers\DateHelper;
use App\Helpers\FinanceHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ParameterHelper;
use App\Http\Requests;
use App\Http\Resources\ChartAccount as ChartAccountResource;
use App\Http\Resources\ExpenseHeader as ExpenseHeaderResource;
use App\Http\Resources\Investment as InvestmentResource;
use App\Http\Resources\LoanHeader as LoanHeaderResource;
use App\Http\Resources\PurchaseHeader as PurchaseHeaderResource;
use App\Http\Resources\RecurringExpense as RecurringExpenseResource;
use App\Http\Resources\SalesHeader as SalesHeaderResource;
use App\Http\Resources\TaxableEntity as TaxableEntityResource;
use App\Http\Resources\TransactableHeader as TransactableHeaderResource;
use App\Investment;
use App\Location;
use App\LoanHeader;
use App\OutstandingTransactablePdf;
use App\Parameter;
use App\PurchaseHeader;
use App\RecurringExpense;
use App\SalesHeader;
use App\TaccountTransaction;
use App\TaxableEntity;
use App\TradableTransaction;
use App\TransactableHeader;
use App\UniqueTradable;
use App\User;
use Auth;
use DB;
use NumberFormatter;
use Storage;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class FinanceController extends Controller
{
	public function index()
	{
		$switch = [
			// modal related
			'transactable-modal' => auth()->user()->can(['ar-view', 'ar-edit', 'rar-view', 'rar-edit', 'ap-view', 'ap-edit', 'rap-view', 'rap-edit']),
			'purchase-process-modal' => auth()->user()->can(['ap-create', 'rap-create']),
			'sales-process-modal' => auth()->user()->can(['ar-create', 'rar-create']),
			'taxable-entity-modal' => auth()->user()->can(['customer-create', 'customer-edit', 'supplier-create', 'supplier-edit']),
			'taccount-transaction-modal' => auth()->user()->can(['acct-view']),
			'taccount-reconciliation-modal' => auth()->user()->can(['ar-process', 'ap-process', 'rar-process', 'rap-process']),
			'charge-entry-modal' => auth()->user()->can(['ex-create', 'ex-edit', 'ex-view']),
			'income-receipt-modal' => auth()->user()->can(['ar-process', 'rar-process']),
			'payment-disbursement-modal' => auth()->user()->can(['ap-process', 'rap-process']),
			'loan-modal' => auth()->user()->can(['ar-create', 'ar-edit', 'ar-view', 'ar-process', 'rar-create', 'rar-edit', 'rar-view', 'rar-process', 'ap-create', 'ap-edit', 'ap-view', 'ap-process', 'rap-create', 'rap-edit', 'rap-view', 'rap-process']),
			'transfer-cash-modal' => auth()->user()->can('ap-process'),
			'dividend-income-modal' => auth()->user()->can('ar-process'),
			'loan-interest-modal' => auth()->user()->can(['ar-create', 'ar-process', 'ap-create', 'ap-process']),
			'investment-holding-modal' => auth()->user()->can('iv-manage'),
			'supplier-stats-modal' => auth()->user()->can('supplier-report'),
			'customer-stats-modal' => auth()->user()->can('customer-report'),
			'investment-history-modal' => auth()->user()->can('iv-view'),
			// window/table related
			'receivable-table' => auth()->user()->can(['ar-list', 'rar-list']),
			'create-customer-button' => auth()->user()->can('customer-create'),
			'customer-table' => auth()->user()->can('customer-list'),
			'payable-table' => auth()->user()->can(['ap-list', 'rap-list']),
			'create-supplier-button' => auth()->user()->can('supplier-create'),
			'supplier-table' => auth()->user()->can('supplier-list'),
			'create-expense-button' => auth()->user()->can('ex-create'),
			'expense-table' => auth()->user()->can('ex-list'),
			'recurring-expense-table' => auth()->user()->can('ex-list'),
			'bank-account-table' => auth()->user()->can('acct-list'),
			'transfer-cash-button' => auth()->user()->can('ap-process'),
			'loan-table' => auth()->user()->can(['ar-list', 'ap-list'], true),
			'create-loan-button' => auth()->user()->can(['ar-create', 'ap-create'], true),
			'investment-table' => auth()->user()->can('iv-view'),
			'create-investment-button' => auth()->user()->can('iv-manage'),
			'ar-report' => auth()->user()->can(['ar-list', 'rar-list'], true),
			'ap-report' => auth()->user()->can(['ap-list', 'rap-list'], true),
			'finance-report' => auth()->user()->can(['acct-list', 'acct-view'], true),
			'adjust-inventory-button' => auth()->user()->can(['ex-create', 'ar-create'], true),
			'research-investment' => auth()->user()->can(['iv-view', 'iv-manage']),
			// other actions
			'customer-upsert' => auth()->user()->can(['customer-create', 'customer-edit']),
			'supplier-upsert' => auth()->user()->can(['supplier-create', 'supplier-edit']),
			'expense-upsert' => auth()->user()->can(['ex-create', 'ex-edit', 'ex-process']),
		];

		$level2Switch = [
			'receivable-window' => $switch['sales-process-modal'] || $swtich['receivable-table'],
			'customer-window' => $switch['create-customer-button'] || $switch['customer-table'],
			'payable-window' => $switch['purchase-process-modal'] || $switch['payable-table'],
			'supplier-window' => $switch['create-supplier-button'] || $switch['supplier-table'],
			'expense-window' => $switch['create-expense-button'] || $switch['expense-table'],
			'recurring-expense-window' => $switch['create-expense-button'] || $switch['recurring-expense-table'],
			'bank-account-window' => $switch['bank-account-table'],
			'loan-window' => $switch['create-loan-button'] || $switch['loan-table'],
			'investment-window' => $switch['investment-table'] || $switch['create-investment-button'],
			'report-window' => $switch['finance-report'] || $switch['ar-report'] || $switch['ap-report'],
			'tool-window' => $switch['adjust-inventory-button'] || $switch['create-expense-button'],
			'entity-template' => $switch['customer-table'] || $switch['upsert-customer'] || $switch['supplier-table'] || $switch['supplier-upsert'],
			'transactable-template' => $switch['receivable-table'] || $switch['sales-process-modal'] || $switch['payable-table'] || $switch['purchase-process-modal'],
			'expense-template' => $switch['expense-table'] || $switch['expense-upsert'],
			'recurring-expense-template' => $switch['expense-table'] || $switch['create-expense-button'],
			'bank-account-template' => $switch['bank-account-table'] || $switch['taccount-reconciliation-modal'],
			'loan-template' => $switch['loan-table'] || $switch['loan-modal'],
			'investment-template' => $switch['investment-table'] || $switch['investment-holding-modal'],
		];

		return view()->first(generateTemplateCandidates('finance.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch),
			]);
	}

	public function recordDividendAjax(Request $request)
	{
		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token()
			]
		]);
	}

	public function recordDividendPostAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$error = [];

		$incurDate = $request->input('date');
		if (!$incurDate) {
			$error['date'] = [ trans('messages.Date not specified') ];
		} else {
			$incurDate = DateHelper::guiToDbDate($request->input('date'));
		}
		$amount = $request->input('amount');
		if (!$amount) {
			$error['amount'] = [ trans('messages.Amount not specified') ];
		}
		$revenueAccount = ChartAccount::find($request->input('revenue'));
		if (!$revenueAccount) {
			$error['revenue'] = [ trans('messages.Account not specified') ];
		}
		$bankAccount = ChartAccount::find($request->input('bank'));
		if (!$bankAccount) {
			$error['bank'] = [ trans('messages.Account not specified') ];
		}
		// check from/to account has same currency
		if ($revenueAccount['currency_id'] != $bankAccount['currency_id']) {
			if (!isset($error['revenue'])) {
				$error['revenue'] = [ trans('messages.Currency mismatch') ];
			}
			if (!isset($error['bank'])) {
				$error['bank'] = [ trans('messages.Currency mismatch') ];
			}
		}

		if (count($error)) {
			return response()->json([ 'success' => false, 'errors' => $error ]);
		}

		try {
			DB::transaction(function() use ($request, $bankAccount, $revenueAccount, $amount, $incurDate) {
				TaccountTransaction::create([
					'debit_t_account_id' => $bankAccount->id,
					'credit_t_account_id' => $revenueAccount->id,
					'amount' => $amount,
					'currency_id' => $bankAccount->currency_id,
					'book_date' => $incurDate,
					'src' => 'cash_receipt',
					'src_id' => 0,
					'valid' => 1,
					'reconciled' => 0,
					'notes' => $request->input('notes'),
				]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new ChartAccountResource($bankAccount) ]);
	}

	public function transferCashAjax(Request $request)
	{
		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token()
			]
		]);
	}

	public function transferCashPostAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$error = [];

		$fromAccount = ChartAccount::find($request->input('from'));
		if (!$fromAccount) {
			$error['from'] = [ trans('messages.Account not specified') ];
		}
		$toAccount = ChartAccount::find($request->input('to'));
		if (!$toAccount) {
			$error['to'] = [ trans('messages.Account not specified') ];
		}
		$transferDate = $request->input('date');
		if (!$transferDate) {
			$error['date'] = [ trans('messages.Date not specified') ];
		} else {
			$transferDate = DateHelper::guiToDbDate($request->input('date'));
		}
		$amount = $request->input('amount');
		if (!$amount) {
			$error['amount'] = [ trans('messages.Amount not specified') ];
		}

		// check from/to account the same?
		if ($fromAccount['id'] == $toAccount['id']) {
			if (!isset($error['from'])) {
				$error['from'] = [ trans('messages.Same account') ];
			}
			if (!isset($error['to'])) {
				$error['to'] = [ trans('messages.Same account') ];
			}
		}

		// check from/to account has same currency
		if ($fromAccount['currency_id'] != $toAccount['currency_id']) {
			if (!isset($error['from'])) {
				$error['from'] = [ trans('messages.Currency mismatch') ];
			}
			if (!isset($error['to'])) {
				$error['to'] = [ trans('messages.Currency mismatch') ];
			}
		}

		// check from-account has at least amount to be transferred
		if ($request->input('from')) {
			$sum = DB::select("select sum(if(debit_t_account_id, amount, -amount)) as sum from taccount_transactions where (debit_t_account_id = " . $fromAccount['id'] . " or credit_t_account_id = " . $fromAccount['id'] . ") and valid = 1")[0]->sum;
			if ($amount > $sum) {
				if (!isset($error['amount'])) {
					$error['amount'] = [ trans('messages.insufficient fund') ];
				}
			}
		}

		if (count($error)) {
			return response()->json([ 'success' => false, 'errors' => $error ]);
		}

		$accountUpdates = [];

		try {
			DB::transaction(function() use ($fromAccount, $toAccount, $amount, $transferDate) {
				TaccountTransaction::create([
						'debit_t_account_id' => $toAccount['id'],
						'credit_t_account_id' => $fromAccount['id'],
						'amount' => $amount,
						'currency_id' => $fromAccount['currency_id'],
						'book_date' => $transferDate,
						'src' => 'cash_transfer',
						'src_id' => 0,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => '',
					]);
			});
			foreach (DB::select("select id, currency_id, (select sum(amount) from taccount_transactions where valid=1 and reconciled=0 and debit_t_account_id = chart_accounts.id) as debit, (select sum(amount) from taccount_transactions where valid=1 and reconciled=0 and credit_t_account_id = chart_accounts.id) as credit from chart_accounts where id in (" . $fromAccount['id'] . ", " . $toAccount['id'] . ")") as $unreconciled) {
				$currency = Currency::find($unreconciled->currency_id);
				$fmtr = new \NumberFormatter( $currency->regex, \NumberFormatter::CURRENCY );
				$accountUpdates['bank-account-' . $unreconciled->id . '-debit'] = $fmtr->format($unreconciled->debit);
				$accountUpdates['bank-account-' . $unreconciled->id . '-credit'] = $fmtr->format($unreconciled->credit);
			}
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => ChartAccountResource::collection(collect([ $fromAccount, $toAccount ])) ]);
	}

	public function statement(Request $request)
	{
		$min = TaccountTransaction::min('created_at');

		$currency = TaxableEntity::theCompany()->currency->getFormat();

		return view()->first(generateTemplateCandidates('finance.statement'), [ 'currency' => $currency ]);
	}

	public function getStatementAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$currencyId =  $request->input('currency');
		$currencyId = empty($currencyId) ? null : $currencyId;

		return FinanceHelper::generateStatement($request->input('year'), $request->input('month'), $currencyId);
	}

	public function printStatement(Request $request)
	{
		// file-download related controller does not register with session-history
		$this->removeFromHistory();

		$endDate = date("Y-m-t", strtotime($request->input('year') . "-" . (($request->input('month') == 0) ? "12" : $request->input('month')) . "-01"));

		$currencyId =  $request->input('currency');
		$currencyId = empty($currencyId) ? null : $currencyId;

		App::setLocale('en');
		$result = FinanceHelper::generateStatement($request->input('year'), $request->input('month'), $currencyId);

		$pdf = new FinancialStatementPdf([
					'data' => $result,
				]);

		$pdf->Output("Financial Statement as of ".$endDate.".pdf", "D");
	}

	public function finalizeEarningAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$year = $request->input('year');
		$month = $request->input('month');
		$amount = $request->input('amount');
		$company = TaxableEntity::theCompany();
		// validate input
		if (is_numeric($amount)) {
			// warn if this period already received data
			$retainEarningAccountId = TaxableEntity::theCompany()['revenue_t_account_id'];
			$resultExists = TaccountTransaction::where(function($query) use ($retainEarningAccountId) {
				$query->where('debit_t_account_id', '=', $retainEarningAccountId);
				$query->orWhere('credit_t_account_id', '=', $retainEarningAccountId);
			})->where('valid', '=', 1)->whereBetween('book_date', [$year . '-' . $month . '-1', date("Y-m-t", strtotime($year . '-' . $month . '-1'))])->count();
			if (!$resultExists) {
				try {
					DB::transaction(function() use ($amount, $company, $year, $month) {
						$isNegative = ($amount[0] == "-");
						$uselessAccount = ChartAccount::where('active', 0)->first()->id;
						TaccountTransaction::create([
								'debit_t_account_id' => $isNegative ? $company['revenue_t_account_id'] : $uselessAccount,
								'credit_t_account_id' => $isNegative ? $uselessAccount : $company['revenue_t_account_id'],
								'amount' => $isNegative ? substr($amount, 1) : $amount,
								'currency_id' => $company['currency_id'],
								'book_date' => date("Y-m-t", strtotime($year . '-' . $month . '-1')),
								'src' => 'retain earning',
								'src_id' => 0,
								'valid' => 1,
								'reconciled' => 0,
								'notes' => '',
							]);
					});
				} catch (\Exception $e) {
					$registration = recordAndReportProblem($e);
					return json_encode(['result' => false, 'errors' => [ 're_amount' => $e->getMessage() ]]);
				}
				return json_encode(['result' => true ]);
			}
			return json_encode(['result' => false, 'errors' => [ 're_amount' => trans('finance.Data already entered') ]]);
		}
		return json_encode(['result' => false, 'errors' => [ 're_amount' => trans('finance.Incorrect amount') ]]);
	}

	public function inventory(Request $request)
	{
		// flus end date; no need to check error-redirect
		$request->session()->flashInput([
			'enddate' => date('m/d/Y'),
		]);

		return view()->first(generateTemplateCandidates('finance.inventory'));
	}

	public function outstandingTransactable($type, Request $request)
	{
		$title = [
						'payable' => trans('finance.Outstanding A/P'),
						'receivable' => trans('finance.Outstanding A/R'),
					];

		// flus end date; no need to check error-redirect
		$request->session()->flashInput([
			'enddate' => date('m/d/Y'),
		]);

		return view()->first(generateTemplateCandidates('finance.outstanding'), [ 'type' => $type, 'title' => $title[$type] ]);
	}

	public function getTransactableAjax(Request $request, $type)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		return json_encode(FinanceHelper::generateOutstandingTransactable(DateHelper::guiToDbDate($request->input('date')), $type));
	}

	public function printOutstandingTransactable(Request $request, $type)
	{
		// file-download related controller does not register with session-history
		$this->removeFromHistory();

		$endDate = DateHelper::guiToDbDate($request->input('date'));

		$result = FinanceHelper::generateOutstandingTransactable($endDate, $type);

		$pdf = new OutstandingTransactablePdf([
				'title' => "Outstanding " . $type . " as of ".$endDate,
				'data' => $result,
			]);

		$pdf->Output("Outstanding " . $type . " as of ".$endDate.".pdf", "D");
	}

	public function adjustInventory(Request $request)
	{
		$total = 0;
		$inventory = [];
		$currency = TaxableEntity::theCompany()->currency->getFormat();
		$fmtr = new \NumberFormatter($currency['regex'], \NumberFormatter::CURRENCY);

		foreach (DB::select("select locations.id as location_id, locations.name, unique_tradables.id as sku_id, unique_tradables.sku, sum(quantity) as quantity, sum(quantity*unit_cost) as amount from tradable_transactions left join locations on locations.id = tradable_transactions.location_id left join unique_tradables on unique_tradables.id = tradable_transactions.unique_tradable_id where tradable_transactions.valid = 1 and tradable_transactions.owner_entity_id = " . TaxableEntity::theCompany()->id . " and tradable_transactions.created_at < '" . date("Y-m-d H:i:s") . "' group by locations.id, locations.name, sku_id, unique_tradables.sku") as $record) {
			$inventory[] = [
					'sku_id' => $record->sku_id,
					'sku' => $record->sku,
					'location_id' => $record->location_id,
					'location' => $record->name,
					'unit_price' => sprintf("%0.".$currency['fdigit']."f", (($record->quantity > 0) ? ($record->amount / $record->quantity) : 0)),
					'quantity' => $record->quantity,
					//'amount' => sprintf("%0.".$currency['fdigit']."f", $record->amount),
					'amount' => $fmtr->format($record->amount),
				];
			$total += $record->amount;
		}

		$accounts = ChartAccount::getActiveExpenseAccount('description', 'asc')->get();

		return view()->first(generateTemplateCandidates('finance.adjust_inventory'), [
				'readonly' => false,
				'expense_account' => $accounts,
				'grant_total' => $fmtr->format($total),
				'adjusted_amount' => $fmtr->format(0),
				'currency' => TaxableEntity::theCompany()->currency->getFormat(true),
				'inventory' => $inventory,
			]);
	}

	public function adjustInventoryPost(Request $request)
	{
		//dd($request->input());
		try {
			DB::transaction(function() use ($request) {
				$theCompany = TaxableEntity::theCompany();
				foreach ($request->input('quantity') as $location_id => $content) {
					$total = 0;
					foreach ($content as $sku_id => $unit_price) {
						$oldPrice = $request->input('old_cost')[$location_id][$sku_id];
						$newPrice = $request->input('unit_price')[$location_id][$sku_id];
						if ($newPrice != $oldPrice) {
							$quantity = $request->input('quantity')[$location_id][$sku_id];
							$oldAmount = TradableTransaction::where([
									['unique_tradable_id', '=', $sku_id],
									['location_id', '=', $location_id],
									['owner_entity_id', '=', $theCompany->id],
									['valid', '=', 1]
								])->sum(DB::raw('quantity*unit_cost'));
							// issue out inventory at old price
							$transaction = TradableTransaction::create([
									'unique_tradable_id' => $sku_id,
									'location_id' => $location_id,
									'owner_entity_id' => $theCompany->id,
									'quantity' => -$quantity,
									'unit_cost' => $oldAmount / $quantity,
									'src_table' => '',
									'src_id' => 0,
									'valid' => 1,
									'notes' => 'inventory adjustment at ' . date("Y-m-d g:iA"),
								]);
							event(new \App\Events\InventoryUpdateEvent($transaction));
							// receive in inventory at new price
							$transaction = TradableTransaction::create([
									'unique_tradable_id' => $sku_id,
									'location_id' => $location_id,
									'owner_entity_id' => $theCompany->id,
									'quantity' => $quantity,
									'unit_cost' => $newPrice,
									'src_table' => '',
									'src_id' => 0,
									'valid' => 1,
									'notes' => 'inventory adjustment at ' . date("Y-m-d g:iA"),
								]);
							event(new \App\Events\InventoryUpdateEvent($transaction));
							$total += $newPrice * $quantity - $oldAmount;
						}
					}
					if ($total != 0) {
						// create expense_headers/expense_details object,
						$expenseHeaderObj = ExpenseHeader::create([
								'title' => ParameterHelper::getNextSequence('expense_number'),
								'staff_id' => auth()->user()->id,
								'entity_id' => $theCompany->id,
								'booking_date' => date("Y-m-d"),
								'reference' => '',
								'status' => 'approved',
								'currency_id' => $theCompany->currency_id,
								'notes' => 'inventory adjustment at ' . date("Y-m-d g:iA"),
							]);
						$expenseAccountId = $request->input('expense_t_account_id');
						$uniqueTradable = UniqueTradable::where('expense_t_account_id', $expenseAccountId)->first();
						$hashValue = md5(date("YmdHis"));
						//$fileSize = file_put_contents(Storage::getDriver()->getAdapter()->getPathPrefix() . $hashValue, json_encode(['user' => auth()->user()->name, 'time' => date("Y-m-d H:i:s"), 'location' => $request->ip(), 'old' => $request->input('old_cost'), 'new' => $request->input('unit_price'), 'quantity' => $request->input('quantity')]));
						$fileSize = Storage::disk('s3')->put($hashValue, json_encode(['user' => auth()->user()->name, 'time' => date("Y-m-d H:i:s"), 'location' => $request->ip(), 'old' => $request->input('old_cost'), 'new' => $request->input('unit_price'), 'quantity' => $request->input('quantity')]), 'public');
						$attachment = Downloadable::create([
							'uploader_id' => auth()->user()->id,
							'title' => '',
							'description' => 'supplemental attachment created by ' . auth()->user()->name . ' at ' . date('Y-m-d H:i:s'),
							'original_name' => 'inventory adjustment at ' . date('Y-m-d H:i:s') . '.txt',
							'file_size' => $fileSize,
							'mime_type' => 'text/plain',
							'hash' => $hashValue,
							'valid' => 1,
						]);
						$expenseDetailObj = ExpenseDetail::create([
								'expense_header_id' => $expenseHeaderObj->id,
								'unique_tradable_id' => $uniqueTradable->id,
								'unit_price' => abs($total),
								'quantity' => 1,
								'subtotal' => abs($total),
								'incur_date' => date("Y-m-d"),
								'notes' => 'inventory adjustment at ' . date("Y-m-d g:iA"),
								'attachment_id' => $attachment->id,
							]);
						// do chart-account adjustment (expense, inventory)
						$warehouseAccountId = Location::find($location_id)->inventory_t_account_id;
						TaccountTransaction::create([
								'debit_t_account_id' => ($total < 0) ? $expenseAccountId : $warehouseAccountId,
								'credit_t_account_id' => ($total < 0) ? $warehouseAccountId : $expenseAccountId,
								'amount' => abs($total),
								'currency_id' => $theCompany->currency_id,
								'book_date' => date("Y-m-d"),
								'src' => 'expense_headers',
								'src_id' => $expenseHeaderObj->id,
								'valid' => 1,
								'reconciled' => 0,
								'notes' => 'inventory adjustment at ' . date("Y-m-d g:iA"),
							]);
						event(new \App\Events\ExpenseUpsertEvent($expenseHeaderObj));
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Inventory adjusted'));
	}
}
