<?php

namespace App;

use Log;
use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'header_id', 'unique_tradable_id', 'display_as', 'description', 'unit_price', 'ordered_quantity', 'allocated_quantity', 'shipped_quantity', 'shipped_amount', 'discount', 'discount_type', 'taxable', 'status', 'delivery', 'notes',
    ];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function getDiscountTypeSymbol()
	{
		switch ($this->discount_type) {
		case 'percent':
			return "%";
		case 'amount':
			$fmtr = new \NumberFormatter($this->header->currency->regex, \NumberFormatter::CURRENCY);
			return $fmtr->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
		default;
			return "";
		}
		return "";
	}

	public function header()
	{
		return $this->belongsTo('App\SalesHeader', 'header_id')->withoutGlobalScope('currentFiscal');
	}

	public function getFontawesomeClass()
	{
		switch ($this->discount_type) {
		case 'percent':
			return "fa-percent";
		case 'amount':
			return "fa-usd";
		default;
			return "";
		}
		return "";
	}

	public function getDiscountPerUnit()
	{
		$unitCost = 0;
		switch ($this->discount_type) {
			case 'percent':
				$unitCost = $this->unit_price * $this->discount / 100;
				break;
			case 'amount':
			default:
				$unitCost = $this->discount;
				break;
		}
		return $unitCost;
	}

	public function getTaxPerUnit()
	{
		$unitPrice = $this->unit_price - $this->getDiscountPerUnit();
		return $this->taxable ? ($unitPrice * $this->header->tax_rate / 100) : 0;
	}

	public function uniqueTradable()
	{
		return $this->belongsTo('\App\UniqueTradable', 'unique_tradable_id');
	}

	public function getSubtotal() {
		switch ($this->discount_type) {
		case 'percent':
			return $this->unit_price * ($this->ordered_quantity - $this->shipped_quantity) * (100 - $this->discount) / 100;
		case 'amount':
			return ($this->unit_price - $this->discount) * ($this->ordered_quantity - $this->shipped_quantity);
		}
		return $this->unit_price * ($this->ordered_quantity - $this->shipped_quantity);
	}

	public function getTotalVolume()
	{
			$tradable = $this->uniqueTradable->tradables()->orderBy('updated_at', 'desc')->first();
			return $tradable->unit_length * $tradable->unit_width * $tradable->unit_height * $this->ordered_quantity;
	}

	public function getTotalWeight()
	{
		$tradable = $this->uniqueTradable->tradables()->orderBy('updated_at', 'desc')->first();
		return $tradable->unit_weight * $this->ordered_quantity;
	}

}
