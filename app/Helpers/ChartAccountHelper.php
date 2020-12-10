<?php
namespace App\Helpers;

class ChartAccountHelper
{
	/*
	 * convert debit/credit to +/- based on type of account
	 * return 1 to indicate positive, -1 to indicate negative
	 */
	public static function convertDCToPN($type, $isDebit)
	{
		// depends on type of account, need to figure out debit is + or credit is +
		// per wikipedia
		//              debit  credit
		//      asset     +      -
		//      liability -      +
		//      revenue   -      +
		//      expense   +      -
		//      equity    -      +
		switch ($type) {
		case 'asset':
			return $isDebit ? 1 : -1;
			break;
		case 'liability':
			return $isDebit ? -1 : 1;
			break;
		case 'revenue':
			return $isDebit ? -1 : 1;
			break;
		case 'expense':
			return $isDebit ? 1 : -1;
			break;
		case 'equity':
			return $isDebit ? -1 : 1;
			break;
		case 'cogs':
			return $isDebit ? 1 : -1;
			break;
		default:
			break;
		}
		throw new \Exception("Can't determine debit/credit of transaction");
	}

	public static function allTypes()
	{
		return [
				'unknown' => trans('forms.Unknown'),
				'asset' => trans('finance.Asset'),
				'expense' => trans('finance.Expense'),
				'liability' => trans('finance.Liability'),
				'equity' => trans('finance.Equity'),
				'revenue' => trans('finance.Revenue'),
				'cogs' => trans('finance.Cost-of-good-sold'),
			];
	}
}
