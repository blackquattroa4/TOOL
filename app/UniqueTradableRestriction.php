<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UniqueTradableRestriction extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'unique_tradable_id', 'action', 'associated_attribute', 'associated_id', 'enforce',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function uniqueTradalbe()
	{
		return $this->hasOne('\App\UniqueTradable', 'id', 'unique_tradable_id');
	}
}
