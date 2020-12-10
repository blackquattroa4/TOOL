<?php
namespace App\Helpers;

use App\ChartAccount;
use App\Currency;
use App\TaccountTransaction;
use App\TaxableEntity;
use DB;

class FinanceHelper
{
	public static function generateStatement($year, $month, $currency_id = null)
	{
		$beginDate = $year . '-' . sprintf("%02d", ($month == 0) ? 1 : $month) . '-01';
		$endDate = date("Y-m-t", strtotime($year . '-' . sprintf("%02d", ($month == 0) ? 12 : $month) . '-01'));

		// use company's default currency as reporting-currency
		$baseCurrency = ($currency_id == null) ? TaxableEntity::theCompany()->currency : Currency::find($currency_id);
		$fmtr = new \NumberFormatter( $baseCurrency->getFormat()['regex'], \NumberFormatter::CURRENCY);

		// Gross sales
		$sales = [
			'total' => 0,
			'cost' => 0,
		];
		foreach (TaccountTransaction::select('credit_t_account_id', 'currency_id', DB::raw('sum(amount) as total'))
								->whereIn('credit_t_account_id', ChartAccount::where('type', 'revenue')->pluck('id')->toArray())
								->whereBetween('book_date', [$beginDate, $endDate])
								->where('valid', 1)->groupBy('credit_t_account_id', 'currency_id')->get() as $result) {
			$sales['total'] += $result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
		}
		foreach (TaccountTransaction::select('debit_t_account_id', 'currency_id', DB::raw('sum(amount) as total'))
								->whereIn('debit_t_account_id', ChartAccount::where('type', 'cogs')->pluck('id')->toArray())
								->whereBetween('book_date', [$beginDate, $endDate])
								->where('valid', 1)->groupBy('debit_t_account_id', 'currency_id')->get() as $result) {
			$sales['cost'] += $result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
		}

		// return
		$return = [
			'total' => 0,
			'cost' => 0,
		];
		foreach (TaccountTransaction::select('debit_t_account_id', 'currency_id', DB::raw('sum(amount) as total'))
								->whereIn('debit_t_account_id', ChartAccount::where('type', 'revenue')->pluck('id')->toArray())
								->whereBetween('book_date', [$beginDate, $endDate])
								->where('valid', 1)->groupBy('debit_t_account_id', 'currency_id')->get() as $result) {
			$sales['total'] += $result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
		}
		foreach (TaccountTransaction::select('credit_t_account_id', 'currency_id', DB::raw('sum(amount) as total'))
								->whereIn('credit_t_account_id', ChartAccount::where('type', 'cogs')->pluck('id')->toArray())
								->whereBetween('book_date', [$beginDate, $endDate])
								->where('valid', 1)->groupBy('credit_t_account_id', 'currency_id')->get() as $result) {
				$sales['cost'] += $result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
		}

		// expense
		$expenses = [];
		foreach (TaccountTransaction::select('debit_t_account_id', 'chart_accounts.currency_id', 'description', DB::raw('sum(amount) as total'))
						->leftjoin('chart_accounts', 'chart_accounts.id', '=', 'debit_t_account_id')
						->whereIn('debit_t_account_id', ChartAccount::where('type', 'expense')->pluck('id')->toArray())
						->whereBetween('book_date', [$beginDate, $endDate])
						->where('valid', 1)->groupBy('debit_t_account_id', 'currency_id', 'description')->get() as $result) {
			$expenses[$result->debit_t_account_id] = [
					'title' => $result->description,
					'amount' => $result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency),
				];
		}
		// expense-credit
		foreach (TaccountTransaction::select('credit_t_account_id', 'chart_accounts.currency_id', 'description', DB::raw('sum(amount) as total'))
						->leftjoin('chart_accounts', 'chart_accounts.id', '=', 'credit_t_account_id')
						->whereIn('credit_t_account_id', ChartAccount::where('type', 'expense')->pluck('id')->toArray())
						->whereBetween('book_date', [$beginDate, $endDate])
						->where('valid', 1)->groupBy('credit_t_account_id', 'currency_id', 'description')->get() as $result) {
			if (isset($expenses[$result->credit_t_account_id])) {
				$expenses[$result->credit_t_account_id]['amount'] -= $result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
			} else {
				$expenses[$result->credit_t_account_id] = [
						'title' => $result->description,
						'amount' => -$result->total * Currency::find($result->currency_id)->getConversionRatio($baseCurrency),
					];
			}
		}
		$expenseTotal = 0;
		foreach ($expenses as $key => $content) {
			$expenseTotal += $content['amount'];
			$expenses[$key]['amount'] = $fmtr->format($content['amount']);
		}

		$incomeStatement = [
			'title' => trans('finance.Income statement'),
			'date' => DateHelper::dbToGuiDate($beginDate) . " ~ " . DateHelper::dbToGuiDate($endDate),
			'items' => [
				'revenue' => [
					'title' => trans('finance.Revenues'),
					'amount' => $fmtr->format($sales['total'] - $return['total']),
					'items' => [
						'sales' => [
							'title' => trans('finance.Gross sales'),
							'amount' => $fmtr->format($sales['total']),
						],
						'return' => [
							'title' => trans('finance.Less sales return'),
							'amount' => $fmtr->format($return['total']),
						],
					],
				],
				'cogs' => [
					'title' => trans('finance.Gross profit'),
					'amount' => $fmtr->format($sales['total'] - $return['total'] - $sales['cost'] + $return['cost']),
					'items' => [
						'cogs' => [
							'title' => trans('finance.Cost of good sold'),
							'amount' => $fmtr->format($sales['cost'] - $return['cost']),
						],
					],
				],
				'expense' => [
					'title' => trans('finance.Expenses'),
					'amount' => $fmtr->format($expenseTotal),
					'items' => $expenses,
				],
				'profit' => [
					'title' => trans('finance.Net profit'),
					'amount' => $fmtr->format($sales['total'] - $return['total'] - $sales['cost'] + $return['cost'] - $expenseTotal),
					'items' => [],
				],
			],
		];

		$template = "select chart_accounts.currency_id, chart_accounts.description, sum(if(chart_accounts.id = taccount_transactions.#T#A#C#C#O#U#N#T#,amount,-amount)) as total from taccount_transactions left join chart_accounts on chart_accounts.id = taccount_transactions.debit_t_account_id or chart_accounts.id = taccount_transactions.credit_t_account_id where valid = 1 and chart_accounts.type = '#T#Y#P#E#' and taccount_transactions.book_date <= '" . $endDate . "' group by chart_accounts.description, chart_accounts.currency_id order by chart_accounts.description";

		$assets = [];
		$totalAssets = 0;
		foreach (DB::select(str_replace(["#T#Y#P#E#", "#T#A#C#C#O#U#N#T#"], ["asset", "debit_t_account_id"], $template)) as $result) {
			$conversionRatio = Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
			$assets[] = [
				'title' => $result->description,
				'amount' => $fmtr->format($result->total * $conversionRatio),
			];
			$totalAssets += $result->total * $conversionRatio;
		}

		$liabilities = [];
		$totalLiabilities = 0;
		foreach (DB::select(str_replace(["#T#Y#P#E#", "#T#A#C#C#O#U#N#T#"], ["liability", "credit_t_account_id"], $template)) as $result) {
			$conversionRatio = Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
			$liabilities[] = [
				'title' => $result->description,
				'amount' => $fmtr->format($result->total * $conversionRatio),
			];
			$totalLiabilities += $result->total * $conversionRatio;
		}

		$equities = [];
		$totalEquities = 0;
		foreach (DB::select(str_replace(["#T#Y#P#E#", "#T#A#C#C#O#U#N#T#"], ["equity", "credit_t_account_id"], $template)) as $result) {
			$conversionRatio = Currency::find($result->currency_id)->getConversionRatio($baseCurrency);
			$equities[] = [
				'title' => $result->description,
				'amount' => $fmtr->format($result->total * $conversionRatio),
			];
			$totalEquities += $result->total * $conversionRatio;
		}

		$balanceStatement = [
			'title' => trans('finance.Balance sheet'),
			'date' => trans('finance.Ending') . " " . DateHelper::dbToGuiDate($endDate),
			'items' => [
				'asset' => [
					'title' => trans('finance.Assets'),
					'amount' => $fmtr->format($totalAssets),
					'items' => $assets,
				],
				'liability' => [
					'title' => trans('finance.Liabilities'),
					'amount' => $fmtr->format($totalLiabilities),
					'items' => $liabilities,
				],
				'equity' => [
					'title' => trans('finance.Equities'),
					'amount' => $fmtr->format($totalEquities),
					'items' => $equities,
				],
			],
		];

		$statements = [
			'income' => $incomeStatement,
			'balance' => $balanceStatement,
		];

		return $statements;
	}

	public static function generateOutstandingTransactable($endDate, $type)
	{
		$fmtr = new \NumberFormatter(TaxableEntity::theCompany()->currency->getFormat()['regex'], \NumberFormatter::CURRENCY );

		$title = [
			'payable' => trans('finance.Outstanding A/P'),
			'receivable' => trans('finance.Outstanding A/R'),
		];

		//  pull transactable data
		$result = [
			$type => [
				'title' => $title[$type],
				'items' => [],
			],
		];

		switch ($type) {
			case 'receivable':
				$query = DB::select("SELECT transactable_headers.id, transactable_headers.title, taxable_entities.name, src_table, incur_date, t2.balance, DATEDIFF('" . $endDate . "', ADDDATE(incur_date, (SELECT grace_days FROM payment_terms WHERE payment_terms.id = transactable_headers.payment_term_id))) AS past_due FROM transactable_headers LEFT JOIN (SELECT txhd.id, SUM(CASE process_status WHEN 'credited' THEN - amount WHEN 'debited' THEN amount WHEN 'created' THEN (CASE src_table WHEN 'sales_headers' THEN (CASE WHEN txhd.src_id IN (SELECT id FROM sales_headers WHERE type = 'order') THEN amount WHEN txhd.src_id IN (SELECT id FROM sales_headers WHERE type = 'return') THEN - amount ELSE 0 END) WHEN 'loan_headers' THEN (CASE WHEN txhd.src_id IN (SELECT id FROM loan_headers WHERE role = 'lender') THEN amount ELSE - amount END) WHEN 'expense_headers' THEN - amount END) ELSE 0 END) AS balance FROM transactable_histories LEFT JOIN transactable_headers txhd on txhd.id = transactable_histories.src_id WHERE transactable_histories.src = 'transactable_headers' AND transactable_histories.created_at < '" . $endDate . " 23:59:59' AND transactable_histories.process_status IN ('created', 'credited', 'debited') GROUP BY txhd.id) t2 on t2.id = transactable_headers.id LEFT JOIN taxable_entities ON taxable_entities.id = transactable_headers.entity_id WHERE status != 'void' AND taxable_entities.type IN ('customer') AND src_table IN ('sales_headers', 'expense_headers', 'loan_headers') AND (incur_date < '" . $endDate . " 23:59:59') AND (t2.balance != 0) ORDER BY name, incur_date");
				break;
			case 'payable':
				$query = DB::select("SELECT transactable_headers.id, transactable_headers.title, taxable_entities.name, src_table, incur_date, t2.balance, DATEDIFF('" . $endDate . "', ADDDATE(incur_date, (SELECT grace_days FROM payment_terms WHERE payment_terms.id = transactable_headers.payment_term_id))) AS past_due FROM transactable_headers LEFT JOIN (SELECT txhd.id, SUM(CASE process_status WHEN 'debited' THEN - amount WHEN 'credited' THEN amount WHEN 'created' THEN (CASE src_table WHEN 'expense_headers' THEN amount WHEN 'purchase_headers' THEN (CASE WHEN txhd.src_id IN (SELECT id FROM purchase_headers WHERE type = 'order') THEN amount WHEN txhd.src_id IN (SELECT id FROM purchase_headers WHERE type = 'return') THEN - amount ELSE 0 END) WHEN 'loan_headers' THEN (CASE WHEN txhd.src_id IN (SELECT id FROM loan_headers WHERE role = 'borrower') THEN amount ELSE - amount END) END) ELSE 0 END) AS balance FROM transactable_histories LEFT JOIN transactable_headers txhd ON txhd.id = transactable_histories.src_id WHERE transactable_histories.src = 'transactable_headers' AND transactable_histories.created_at < '" . $endDate . " 23:59:59' AND transactable_histories.process_status IN ('created', 'credited', 'debited') GROUP BY txhd.id) t2 on t2.id = transactable_headers.id LEFT JOIN taxable_entities ON taxable_entities.id = transactable_headers.entity_id WHERE status != 'void' AND taxable_entities.type IN ('supplier', 'employee') AND src_table IN ('purchase_headers', 'expense_headers', 'loan_headers') AND (incur_date < '" . $endDate . " 23:59:59') AND (t2.balance != 0) ORDER BY name, incur_date");
				break;
			default:
				return "";
				break;
		}
		foreach ($query as $record) {
			if (!isset($result[$type]['items'][$record->name])) {
				$result[$type]['items'][$record->name] = [
					'title' => $record->name,
					'amount' => 0,
					'slug' => str_slug($record->name),
					'items' => [],
				];
			}
			$result[$type]['items'][$record->name]['amount'] += $record->balance;
			$result[$type]['items'][$record->name]['items'][] = [
				'title' => $record->title,
				'amount' => $fmtr->format($record->balance),
			];
		}

		$total = 0;
		foreach ($result[$type]['items'] as $idx => $useless) {
			$total += $result[$type]['items'][$idx]['amount'];
			$result[$type]['items'][$idx]['amount'] = $fmtr->format($result[$type]['items'][$idx]['amount']);
		}
		$result[$type]['total'] = $fmtr->format($total);

		return $result;
	}
}

?>
