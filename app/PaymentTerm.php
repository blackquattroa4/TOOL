<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'symbol', 'description', 'active', 'cutoff_date', 'grace_days',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

	public static function getActivePaymentTerms($order, $direction)
	{
		return PaymentTerm::where("active", 1)->orderBy($order, $direction)->get();
	}
}
