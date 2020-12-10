<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesApprovalRule extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'applied_to_quote', 'applied_to_order', 'applied_to_return', 'approver_id', 'src_table', 'src_entity_id', 'threshold', 'valid',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function approver()
	{
		return $this->belongsTo('\App\User', 'approver_id');
	}
}
