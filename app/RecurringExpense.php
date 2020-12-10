<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecurringExpense extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'entity_id', 'unique_tradable_id', 'quantity', 'notes', 'frequency_numeral', 'frequency_unit', 'valid',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function entity()
	{
		return $this->belongsTo('\App\TaxableEntity', 'entity_id');
	}

	public function uniqueTradable()
	{
		return $this->belongsTo('\App\UniqueTradable', 'unique_tradable_id');
	}
}
