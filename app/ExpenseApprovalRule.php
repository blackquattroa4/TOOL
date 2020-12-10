<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpenseApprovalRule extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'approver_id', 'src_table', 'src_entity_id', 'threshold', 'valid',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function approver()
	{
		return $this->hasOne('\App\User', 'id', 'approver_id');
	}
}
