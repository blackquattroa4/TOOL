<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'type', 'description', 'status', 'requestor_machine',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function responsibleRole($type)
	{
		return [ 'request' => 'requestee', 'assignment' => 'assignee' ][$type];
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
		return implode($delimiter, [
				str_pad('request', 15) . trans('tool.Search request'),
				str_pad('assignment', 15) . trans('tool.Search assignment'),
				str_pad('requested', 15) . trans('tool.Search entry requested'),
				str_pad('evaluating', 15) . trans('tool.Search entry under evaluating'),
				str_pad('in-progress', 15) . trans('tool.Search entry in-progress'),
				str_pad('closed', 15) . trans('tool.Search entry closed'),
				str_pad('thisyear', 15) . trans('tool.Search from this year'),
				str_pad('thismonth', 15) . trans('tool.Search from this month'),
				str_pad('lastmonth', 15) . trans('tool.Search from last month'),
			]);
	}

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];

		array_push($result, $this->type);
		array_push($result, $this->status);
		if (substr($this->order_date, 0, 4) == date("Y")) {
			array_push($result, 'thisyear');
		}
		if (substr($this->order_date, 0, 7) == date("Y-m")) {
			array_push($result, 'thismonth');
		}
		if (substr($this->order_date, 0, 7) == date("Y-m", strtotime("-1 month"))) {
			array_push($result, 'lastmonth');
		}

		return $result;		
	}

	public function users($role = null)
	{
		if (is_null($role)) {
			return $this->belongsToMany('App\User', 'interaction_user', 'interaction_id', 'staff_id');
		}
		if (is_array($role)) {
			return $this->belongsToMany('App\User', 'interaction_user', 'interaction_id', 'staff_id')->withPivot('role')->whereIn('role', $role);
		}

		return $this->belongsToMany('App\User', 'interaction_user', 'interaction_id', 'staff_id')->withPivot('role')->where('role', $role);
	}

	public function logs()
	{
		return $this->hasMany('App\InteractionLog', 'interaction_id');
	}

	public function groupLogs()
	{
		$groups = [];

		$logs = $this->logs()->orderBy('created_at')->get();

		$participant = 0;
		$threshold = "1970-01-01 00:00:00";

		foreach ($logs as $log) {
			if (($participant != $log->staff_id) || ($log->created_at > $threshold)) {
				$participant = $log->staff_id;
				$threshold = date("Y-m-d H:i:s", strtotime('+1 minutes', strtotime($log->created_at)));
				$groups[] = [
					'is_self' => $participant == auth()->user()->id,
					'user' => $log->creator->name,
					'logs' => [],
					'downloads' => [],
					'time' => \App\Helpers\DateHelper::dbToGuiDate($log->created_at) . " " . date_create($log->created_at)->format("g:i A"),
				];
			}
			$index = count($groups) - 1;
			if (!is_null($log->log)) {
				$groups[$index]['logs'][] = $log->log;
			}
			if (!is_null($log->downloadable_id)) {
				$groups[$index]['downloads'][] = [
					'name' => $log->downloadable->original_name,
					'hash' => $log->downloadable->hash,
				];
			}
		}

		return array_reverse($groups);
	}

}
