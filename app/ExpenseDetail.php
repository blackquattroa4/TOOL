<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UniqueTradable;

class ExpenseDetail extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'expense_header_id', 'unique_tradable_id', 'unit_price', 'quantity', 'subtotal', 'incur_date', 'notes', 'attachment_id',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function uniqueTradable()
	{
		return $this->hasOne('App\UniqueTradable', 'id', 'unique_tradable_id');
	}

	public function downloadable()
	{
		return $this->hasOne('App\Downloadable', 'id', 'attachment_id');
	}

	public function header()
	{
		return $this->belongsTo('\App\ExpenseHeader', 'expense_header_id')->withoutGlobalScope('currentFiscal');
	}

}
