<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InteractionLog extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'interaction_id', 'staff_id', 'log', 'downloadable_id',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function setUpdatedAtAttribute($value)
	{
	    // to Disable updated_at
	}

	public function creator()
	{
		return $this->belongsTo('App\User', 'staff_id');
	}

	public function downloadable()
	{
		return $this->belongsTo('App\Downloadable', 'downloadable_id');
	}
}
