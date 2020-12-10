<?php

namespace App\Http\Controllers;

use App;
use Auth;
use App\ChartAccount;
use App\Currency;
use App\LoanHeader;
use App\Parameter;
use App\TaccountTransaction;
use App\User;
use App\Helpers\ChartAccountHelper;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Http\Requests;
use App\Http\Resources\ChartAccount as ChartAccountResource;
use DB;
use Illuminate\Http\Request;
use NumberFormatter;

class ChartAccountController extends Controller
{
	public function create(Request $request)
	{
		// no need to check error-redirect since POST does not validate
		$request->session()->flashInput([ 'active' => 1 ]);

		return view()->first(generateTemplateCandidates('form.chart_account'), [
							'readonly' => false,
							'title' => trans('forms.Create chart-account'),
							'postUrl' => "/" . $request->path(),
							'allAccountTypes' => ChartAccountHelper::allTypes(),
							'allCurrencies' => Currency::getActiveCurrencies('description', 'asc'),
							'action' => trans('forms.Create'),
						]);
	}

	public function createPost(Request $request)
	{
		try {
			DB::transaction(function() use ($request) {
				$account = ChartAccount::create([
						'account' => $request->input('account'),
						'type' => $request->input('type'),
						'currency_id' => $request->input('currency_id'),
						'description' => $request->input('description'),
						'active' => !is_null($request->input('active')),
					]);
				event(new \App\Events\AccountUpsertEvent($account));
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('forms.Chart-account created.'));
	}

	public function createPostAjax(Request $request) {
		$account = null;
		try {
			DB::transaction(function() use ($request, &$account) {
				$account = ChartAccount::create([
						'account' => $request->input('account'),
						'type' => $request->input('type'),
						'currency_id' => $request->input('currency'),
						'description' => $request->input('description'),
						'active' => in_array($request->input('active'), [1, "1", true, "true"], true),
					]);
				event(new \App\Events\AccountUpsertEvent($account));
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'taccount' => new ChartAccountResource($account) ]]);
	}

	public function edit($id, Request $request)
	{
		$account = ChartAccount::find($id);
		// no need to check error-redirect since POST does not validate
		$request->session()->flashInput([
						'account' => $account->account,
						'type' => $account->type,
						'currency_id' => $account->currency_id,
						'description' => $account->description,
						'active' => $account->active
					]);

		return view()->first(generateTemplateCandidates('form.chart_account'), [
							'readonly' => false,
							'title' => trans('forms.Edit chart-account'),
							'postUrl' => "/" . $request->path(),
							'allAccountTypes' => ChartAccountHelper::allTypes(),
							'allCurrencies' => Currency::getActiveCurrencies('description', 'asc'),
							'action' => trans('forms.Update'),
						]);
	}

	public function editPost($id, Request $request)
	{
		try {
			DB::transaction(function() use ($request, $id) {
				$account = ChartAccount::find($id);
				$account->update([
						'account' => $request->input('account'),
						'type' => $request->input('type'),
						'currency_id' => $request->input('currency_id'),
						'description' => $request->input('description'),
						'active' => !is_null($request->input('active')),
					]);
				event(new \App\Events\AccountUpsertEvent($account));
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('forms.Chart-account updated.'));
	}

	public function updatePostAjax(Request $request, $aid)
	{
		$account = ChartAccount::find($aid);
		try {
			DB::transaction(function() use ($request, $account) {
				$account->update([
						'account' => $request->input('account'),
						'type' => $request->input('type'),
						'currency_id' => $request->input('currency'),
						'description' => $request->input('description'),
						'active' => in_array($request->input('active'), [1, "1", true, "true"], true),
					]);
				event(new \App\Events\AccountUpsertEvent($account));
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'taccount' => new ChartAccountResource($account) ]]);
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view($aid)
	{
		$account = ChartAccount::find($aid);
		$currency = Currency::find($account->currency_id);
		$trx = array();
		$balance = 0;
		$fmtr = new NumberFormatter($currency->regex,  NumberFormatter::CURRENCY);
		$result = DB::select("select debit_t_account_id, credit_t_account_id, book_date, src, amount, notes from taccount_transactions where (debit_t_account_id = " . $aid . " or credit_t_account_id = " . $aid . ") and valid order by book_date asc");
		foreach ($result as $oneTrx) {
			$amount =  $oneTrx->amount * ChartAccountHelper::convertDCtoPN($account->type, ($oneTrx->debit_t_account_id == $aid));
			$balance += $amount;
			$lineBreakPos = strpos($oneTrx->notes, "\r\n");
			if ($lineBreakPos !== FALSE) {
				$notes = substr($oneTrx->notes, 0, $lineBreakPos) . "...";
				$tip = $oneTrx->notes;
			} else {
				if (strlen($oneTrx->notes) > 25) {
					$notes = mb_strimwidth($oneTrx->notes, 0, 25, "...");
					$tip = $oneTrx->notes;
				} else {
					$notes = $oneTrx->notes;
					$tip = null;
				}
			}
			$tmp = array(
							'date' => $oneTrx->book_date,
							'summary' => $oneTrx->src,
							'amount' => $fmtr->format($amount),
							'balance' => $fmtr->format($balance),
							'notes' => $notes
						);
			if (isset($tip)) {
				$tmp['tip'] = $tip;
			}
			$trx[] = $tmp;
		}

		return view()->first(generateTemplateCandidates('chart_account.list'), [
					'id' => $account->id,
					'account' => $account->description,
					'currency' => $currency->description,
					'transactions' => $trx,
				]);
	}

	/**
	 * load t-account
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function loadAjax($aid)
	{
		if ($aid) {
			$account = ChartAccount::find($aid);

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $account->id,
					'csrf' => csrf_token(),
					'account' => $account->account,
					'type' => $account->type,
					'currency' => $account->currency_id,
					'description' => $account->description,
					'active' => $account->active
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'account' => '',
				'type' => 'unknown',
				'currency' => 0,
				'description' => '',
				'active' => 0
			]
		]);
	}

	public function retrieveAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$accountId = $request->input('id');
		$offset = $request->input('offset');
		$count = $request->input('count');

		$account = ChartAccount::find($accountId);

		$transactions = array();

		// use an anonymous function for title display
		$displayFunction = function($input) {
			$determinant = explode(';', $input);
			switch ($determinant[0]) {
				case 'transactable_headers':
					return "#" . $determinant[2];
				case 'expense_headers':
					return "#" . $determinant[2];
				case 'cash_receipt':
					return trans('finance.Cash receipt');
				case 'cash_expenditure':
					return trans('finance.Cash expenditure');
				case 'ap_transfer':
					return trans('finance.A/P transfer');
				case 'cash_transfer':
					return trans('finance.Cash transfer');
				case 'loan_headers':
					switch (LoanHeader::find($determinant[1])->role) {
						case 'lender':
							return trans('finance.Lending');
						case 'borrower':
							return trans('finance.Borrowing');
					}
				default:
					break;
			}
			return "";
		};

		$isCashAccount = in_array($accountId, ParameterHelper::getValue('bank_cash_t_account_ids'));

		foreach (DB::select("select * from (SELECT @id:=@id + 1 AS idx, book_date, notes, source, amount, is_debit, is_credit, other_account, (@sum := convert(@sum + t0.amount * IF(rows = is_debit," . ChartAccountHelper::convertDCToPN($account->type, true) . "," . ChartAccountHelper::convertDCToPN($account->type, false) . "), decimal(15,3))) AS sum, currency_id FROM (SELECT book_date, notes, (case src when 'transactable_details' then (select concat('transactable_headers;',transactable_header_id,';',title) from transactable_details, transactable_headers where transactable_details.transactable_header_id = transactable_headers.id and transactable_details.id = taccount_transactions.src_id) when 'transactable_headers' then (select concat('transactable_headers;',transactable_headers.id,';',title) from transactable_headers where transactable_headers.id = taccount_transactions.src_id) when 'expense_headers' then (select concat('expense_headers;',taccount_transactions.src_id,';',title) from expense_headers where expense_headers.id = taccount_transactions.src_id) else concat(if(credit_t_account_id = " . ($isCashAccount ? -1 : $accountId) . ", 'ap_transfer', taccount_transactions.src),';',taccount_transactions.id,';') end) AS source, SUM(amount) AS amount, COUNT(1) AS rows, IF(debit_t_account_id = $accountId, 1, 0) AS is_debit, IF(credit_t_account_id = $accountId, 1, 0) AS is_credit, IF(debit_t_account_id = $accountId, credit_t_account_id, debit_t_account_id) AS other_account, currency_id FROM taccount_transactions WHERE valid = 1 and (debit_t_account_id = $accountId or credit_t_account_id = $accountId) GROUP BY notes, book_date, source, is_debit, is_credit, other_account, currency_id ORDER BY book_date ASC) t0 CROSS JOIN (SELECT @id:=0) t1 CROSS JOIN (SELECT @sum:=0) t2) tx order by tx.book_date desc, tx.idx desc limit $count offset $offset") as $transaction) {
			$currency = Currency::find($transaction->currency_id);
			$fmtr = new \NumberFormatter( $currency->regex, \NumberFormatter::CURRENCY );
			$otherAccount = ChartAccount::find($transaction->other_account);
			$transactions[] = [
					//'id' => $transaction->id,
					'debit' => ($transaction->is_debit) ? $fmtr->format($transaction->amount) : "",
					'debit_title' => ($transaction->is_debit) ? '' : $otherAccount->account . " - " . $otherAccount->description,
					'credit' => ($transaction->is_credit) ? $fmtr->format($transaction->amount) : "",
					'credit_title' => ($transaction->is_credit) ? '' : $otherAccount->account . " - " . $otherAccount->description,
					'date' => $transaction->book_date,
					'date_display' => DateHelper::dbToGuiDate($transaction->book_date),
					'source' => $displayFunction($transaction->source),
					'notes' => $transaction->notes,
					'balance' => $fmtr->format($transaction->sum),
				];
		}
		return $transactions;
	}

	/**
	 * Reconcile the account.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function reconcile(Request $request, $aid)
	{
		// only bank account can be reconciled.
		$accounts = unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value);
		if (!in_array($aid, $accounts)) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('finance.Only bank accounts can be reconciled.'));
		}

		$account = ChartAccount::find($aid);
		$currency = Currency::find($account->currency_id);
		$fmtr = new \NumberFormatter( $currency->regex, \NumberFormatter::CURRENCY );

		// get total
		$result = DB::select("select ifnull(sum(if(debit_t_account_id=$aid, amount, -amount)), 0) as balance from taccount_transactions where valid=1 and reconciled=1 and (debit_t_account_id = $aid or credit_t_account_id = $aid)");
		$balance_raw = $result[0]->balance;
		$balance = $fmtr->format($balance_raw);

		$transactions = array();
		foreach (DB::select("select * from taccount_transactions where valid = 1 and reconciled = 0 and (debit_t_account_id = $aid or credit_t_account_id = $aid)") as $oneTransaction) {
			$currency = Currency::find($oneTransaction->currency_id);
			$fmtr = new \NumberFormatter( $currency->regex, \NumberFormatter::CURRENCY );
			$transactions[] = [
				'id' => $oneTransaction->id,
				'book_date' => $oneTransaction->book_date,
				'source_display' => TaccountTransaction::find($oneTransaction->id)->displaySource(),
				'debit' => ($oneTransaction->debit_t_account_id == $aid) ? $fmtr->format($oneTransaction->amount) : "",
				'debit_title' => ($oneTransaction->debit_t_account_id == $aid) ? "" : ChartAccount::find($oneTransaction->debit_t_account_id)->account . " - " . ChartAccount::find($oneTransaction->debit_t_account_id)->description,
				'credit' => ($oneTransaction->credit_t_account_id == $aid) ? $fmtr->format($oneTransaction->amount) : "",
				'credit_title' => ($oneTransaction->credit_t_account_id == $aid) ? "" : ChartAccount::find($oneTransaction->credit_t_account_id)->account . " - " . ChartAccount::find($oneTransaction->credit_t_account_id)->description,
				'amount' => ChartAccountHelper::convertDCToPN($account->type, ($oneTransaction->debit_t_account_id == $aid)) * $oneTransaction->amount,
			];
		}

		return view ('chart_account.reconciliation', compact('transactions'), [
							'url' => '/' . $request->path(),
							'account' => $account->description,
							'currency' => $currency->description,
							'currencyFormat' => $currency->getFormat(true),
							'balance_raw' => $balance_raw,
							'balance' => $balance
						]);
	}

	/**
	 * load un-reconcile transaction for the account.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function loadUnreconciledTransactionAjax(Request $request, $aid)
	{
		// only bank account can be reconciled.
		$accounts = unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value);
		if (!in_array($aid, $accounts)) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('finance.Only bank accounts can be reconciled.') ]]]);
		}

		$account = ChartAccount::find($aid);
		$currency = Currency::find($account->currency_id);
		$fmtr = new \NumberFormatter( $currency->regex, \NumberFormatter::CURRENCY );

		// get total
		$result = DB::select("select ifnull(sum(if(debit_t_account_id=$aid, amount, -amount)), 0) as balance from taccount_transactions where valid=1 and reconciled=1 and (debit_t_account_id = $aid or credit_t_account_id = $aid)");
		$balance_raw = $result[0]->balance;
		$balance = $fmtr->format($balance_raw);

		$trx = TaccountTransaction::where([
				[ 'valid', '=', 1 ],
				[ 'reconciled', '=', 0 ],
			])->where(function ($query) use ($aid) {
				$query->orWhere('debit_t_account_id', $aid)->orWhere('credit_t_account_id', $aid);
			})->get();

		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'account' => $account->description,
				'currency' => $currency->symbol,
				'regex' => $currency->getFormat(true)['regex'],
				'display_value' => $balance,
				'original_value' => $balance_raw,
				'line' => $trx->pluck('id'),
				'date' => $trx->map(function ($item) { return DateHelper::dbToGuiDate($item->book_date); }),
				'source' => $trx->map(function ($item) { return $item->displaySource(); }),
				'debit' => $trx->map(function ($item) use ($aid, $fmtr) { return ($item->debit_t_account_id == $aid) ? $fmtr->format($item->amount) : ""; }),
				'debit_title' => $trx->map(function ($item) use ($aid) { return ($item->debit_t_account_id == $aid) ? "" : ChartAccount::find($item->debit_t_account_id)->account . " - " . ChartAccount::find($item->debit_t_account_id)->description; }),
				'credit' => $trx->map(function ($item) use ($aid, $fmtr) { return ($item->credit_t_account_id == $aid) ? $fmtr->format($item->amount) : ""; }),
				'credit_title' => $trx->map(function ($item) use ($aid) { return ($item->credit_t_account_id == $aid) ? "" : ChartAccount::find($item->credit_t_account_id)->account . " - " . ChartAccount::find($item->credit_t_account_id)->description; }),
				'amount' => $trx->map(function ($item) use ($account, $aid) { return ChartAccountHelper::convertDCToPN($account->type, ($item->debit_t_account_id == $aid)) * $item->amount; }),
			]
		]);
	}

	/**
	 * Reconcile the account. (POST)
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function reconcilePost(Request $request, $aid)
	{
		// Go through all check and update database accordingly?
		$accounts = unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value);
		if (!in_array($aid, $accounts)) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('finance.Only bank accounts can be reconciled.'));
		}

		if ($request->input('reconciliation')) {
			try {
				DB::transaction(function() use ($request) {
					TaccountTransaction::find(array_keys($request->input('reconciliation')))->each(function($item, $key) {
						$item->update([ 'reconciled' => 1 ]);
					});
				});
			} catch (\Exception $e) {
				$registration = recordAndReportProblem($e);
				return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
			}
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", ChartAccount::find($aid)->description, trans('finance.### reconciled.')));
	}

	/**
	 * update un-reconcile transaction for the account.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function updateUnreconciledTransactionAjax(Request $request, $aid)
	{
		try {
			DB::transaction(function() use ($request) {
				$checkboxes = $request->input('reconciliation');
				foreach ($request->input('line') as $line => $index) {
					if (in_array($checkboxes[$line], [1, "1", true, "true"], true)) {
						TaccountTransaction::find($index)->update([ 'reconciled' => 1 ]);
					}
				};
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, errors => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'account' => new ChartAccountResource(ChartAccount::find($aid)) ]);
	}

	public function getDashboardAccountAjax($type)
	{
		switch ($type) {
			case 'bank':
				$allAccountIds = unserialize(Parameter::where('key', 'bank_cash_t_account_ids')->first()->value);
				$accounts = ChartAccount::whereIn('id', $allAccountIds)->get();
				break;
			case 'all':
				$accounts = ChartAccount::all();
				break;
			default:
				$accounts = collect([]);
				break;
		}

		return response()->json([ 'success' => true, 'data' => ChartAccountResource::collection($accounts) ]);
	}

}
