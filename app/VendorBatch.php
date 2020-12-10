<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorBatch extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'reference', 'quantity', 'ready_date', 'shipment_reference', 'purchase_detail_id', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function purchaseDetail()
	{
		return $this->belongsTo('\App\PurchaseDetail', 'purchase_detail_id');
	}
}
