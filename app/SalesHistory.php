<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'src', 'src_id', 'staff_id', 'machine', 'process_status', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function staff()
	{
		return $this->belongsTo('\App\User', 'staff_id');
	}

	public function header()
	{
		return $this->belongsTo('\App\SalesHeader', ($this->src == 'sales_headers') ? 'src_id' : '')->withoutGlobalScope('currentFiscal')->withDefault();
	}
}
