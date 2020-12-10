<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryAlertRule extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'unique_tradable_id', 'location_id', 'min', 'max', 'valid',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function uniqueTradable()
	{
		return $this->hasOne('\App\UniqueTradable', 'id', 'unique_tradable_id');
	}

	public function location()
	{
		return $this->hasOne('\App\Location', 'id', 'location_id');
	}

}
