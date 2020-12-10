<?php

namespace App;

use App\Currency;
use Illuminate\Database\Eloquent\Model;

class TransactableDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'transactable_header_id', 'src_table', 'src_id', 'unique_tradable_id', 'display_as', 'description', 'unit_price', 'discount', 'discount_type', 'transacted_quantity', 'transacted_amount', 'discount_amount', 'tax_amount', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

	public function getLineSubtotal()
	{
		return $this->transacted_amount - $this->discount_amount;
	}

  public function header()
  {
    return $this->belongsTo('App\TransactableHeader', 'transactable_header_id')->withoutGlobalScope('currentFiscal');
  }

  public function uniqueTradable()
	{
		return $this->belongsTo('\App\UniqueTradable', 'unique_tradable_id');
	}

	public function getDiscountTypeSymbol()
	{
		switch ($this->discount_type) {
		case 'percent':
			return "%";
		case 'amount':
		default;
      $fmtr = new \NumberFormatter($this->header->currency->regex, \NumberFormatter::CURRENCY);
			return $fmtr->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
		}
		return "";
	}
}
