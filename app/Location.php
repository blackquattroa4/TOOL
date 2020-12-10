<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'type', 'owner_entity_id', 'address_id', 'contact_id', 'inventory_t_account_id', 'active', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function getWarehouses($order, $direction)
	{
		return Location::where('type', 'warehouse')->orderBy($order, $direction)->get();
	}

	public static function getActiveWarehouses($order, $direction)
	{
		return Location::where('active', 1)->where('type', 'warehouse')->orderBy($order, $direction)->get();
	}

	public function owner()
	{
		return $this->belongsTo('\App\TaxableEntity', 'owner_entity_id');
	}

	public function address()
	{
		return $this->hasOne('\App\Address', 'id', 'address_id');
	}

	public function contact()
	{
		return $this->hasOne('\App\User', 'id', 'contact_id');
	}

	public function chartAccount()
	{
		return $this->hasOne('\App\ChartAccount', 'id', 'inventory_t_account_id');
	}

	public function bins()
	{
		return $this->hasMany('\App\WarehouseBin', 'location_id', 'id');
	}
}
