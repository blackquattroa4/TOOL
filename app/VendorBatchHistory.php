<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorBatchHistory extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'batch_id', 'user_id', 'machine', 'process_status',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function staff()
	{
		return $this->belongsTo('\App\User', 'user_id');
	}

	public function batch()
	{
		return $this->belongsTo('\App\VendorBatch', 'batch_id');
	}
}
