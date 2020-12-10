<?php

namespace App;

use App\PurchaseHeader;
use App\VendorBatch;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'header_id', 'unique_tradable_id', 'manufacture_model', 'manufacture_reference', 'ordered_quantity', 'shipped_quantity', 'shipped_amount', 'description', 'unit_price', 'inventory_cost', 'taxable', 'status', 'delivery_date', 'receiving_location_id', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function getTaxPerUnit()
	{
		$unitPrice = $this->unit_price;
		return $this->taxable ? ($unitPrice * $this->header->tax_rate / 100) : 0;
	}

	public function uniqueTradable()
	{
		return $this->hasOne('\App\UniqueTradable', 'id', 'unique_tradable_id');
	}

	public function header()
	{
		return $this->belongsTo('\App\PurchaseHeader', 'header_id')->withoutGlobalScope('currentFiscal');
	}

	public function getVendorBatches($unshippedOnly = false)
	{
		$result = $this->hasMany('\App\VendorBatch', 'purchase_detail_id', 'id');
		if ($unshippedOnly) {
			$result->whereNull('shipment_reference');
		}
		return $result;
	}
}
