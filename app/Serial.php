<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'serial',
		'src_table',
		'src_id',
		'tradable_id',
		'pallet_id',
		'carton_id',
		'warranty_from',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function tradable()
	{
		return $this->belongsTo('\App\Tradable', 'tradable_id');
	}

	public function source()
	{
		switch ($this->src_table) {
			case 'warehouse_details':
				return $this->belongsTo('\App\WarehouseDetail', 'src_id');
				break;
			case 'warehouse_headers':
				return $this->belongsTo('\App\WarehouseHeader', 'src_id')->withoutGlobalScope('currentFiscal');
				break;
			default:
				return null;
		}
		return null;
	}
}
