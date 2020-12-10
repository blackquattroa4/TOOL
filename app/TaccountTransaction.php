<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaccountTransaction extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'debit_t_account_id', 'credit_t_account_id', 'amount', 'currency_id', 'book_date', 'src', 'src_id', 'valid', 'reconciled', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function debitAccount()
	{
		return $this->belongsTo('\App\ChartAccount', 'debit_t_account_id');
	}

	public function creditAccount()
	{
		return $this->belongsTo('\App\ChartAccount', 'credit_t_account_id');
	}

	public function source()
	{
		switch ($this->src) {
			case 'transactable_headers':
				return $this->belongsTo('\App\TransactableHeader', 'src_id')->withoutGlobalScope('currentFiscal');
			case 'transactable_details':
				return $this->belongsTo('\App\TransactableDetail', 'src_id');
			case 'cash_receipt':
			case 'cash_expenditure':
			case 'cash_transfer':
			default:
				return null;
		}
		return null;
	}

	public function displaySource()
	{
		switch ($this->src) {
			case 'transactable_headers':
				return "#" . $this->source->title;
			case 'transactable_details':
				return '#'. $this->source->header->title;
			case 'cash_receipt':
				return trans('finance.Cash receipt');
			case 'cash_expenditure':
				return trans('finance.Cash expenditure');
			case 'cash_transfer':
				return trans('finance.Cash transfer');
			default:
				break;
		}
		return "";
	}

}
