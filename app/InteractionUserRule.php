<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InteractionUserRule extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'originator_id', 'participant_id', 'role', 'valid',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function participant()
	{
		return $this->hasOne('\App\User', 'id', 'participant_id');
	}

	public static function getInitialParticipants($originator_id)
	{
		return self::whereIn('originator_id', [0, $originator_id])->where('valid', 1)->get();
	}

}
