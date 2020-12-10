<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'staff_id', 'subject', 'description', 'from_date', 'to_date', 'repeat_type', 'from_time', 'to_time', 'active'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function getParticipants($reply = null)
  {
    $query = $this->belongsToMany('App\User', 'calendar_event_participants', 'calendar_event_id', 'staff_id');

    if (!empty($reply)) {
      if (gettype($reply) == 'string') {
        $query->where('action', $reply);
      } else if (gettype($reply) == 'array') {
        $query->whereIn('action', $reply);
      }
    }

    return $query;
  }

  public function getReply($user = null)
  {
    if (is_null($user)) {
      $userId = auth()->user()->id;
    } else {
      $userId = $user;
    }

    $result = DB::select("select action from calendar_event_participants where calendar_event_id = " . $this->id . " and staff_id = " . $userId);
    if (!empty($result[0])) {
      return $result[0]->action;
    }
    return "";
  }

  public function getReplyColor($user = null)
  {
    switch ($this->getReply($user)) {
      case 'declined':
        return '#eaeaea';
      case 'accepted':
        return '#0095ff';
    }
    return '#f48024';
  }

  public function addParticipants($participants = [])
  {
    foreach ($participants as $participant) {
      DB::insert("insert into calendar_event_participants (calendar_event_id, staff_id, action, created_at, updated_at) values (" . $this->id . ", " . $participant . ", 'invited', utc_timestamp(), utc_timestamp())");
    }
  }

  public function removeParticipants($participants = [])
  {
    foreach ($participants as $participant) {
      DB::delete("delete from calendar_event_participants where calendar_event_id = " . $this->id . " and staff_id = " . $participant);
    }
  }

  public function isHost($userId)
  {
    return ($this->staff_id == $userId);
  }
}
