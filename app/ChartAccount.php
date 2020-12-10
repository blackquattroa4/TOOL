<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartAccount extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'account', 'type', 'currency_id', 'description', 'active',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function getActiveExpenseAccount($sortCol, $direction)
	{
		return self::where([
				['type', '=', 'expense'],
				['active', '=', 1],
			])->orderBy($sortCol, $direction);
	}

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
	}
}
