<?php

namespace App;

use App\Notifications\ResetPasswordNotification;
use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;

    use EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'entity_id', 'landing_page', 'active', 'language', 'failure_count', 'imap_endpoint',  'smtp_endpoint', 'email_password', 'last_failure', 'last_login',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

	public static function getEntityContacts($entity_id, $order, $direction)
	{
		return User::where('entity_id', $entity_id)->orderBy($order, $direction)->get();
	}

	public static function getAllStaff($order, $direction)
	{
		$ids = TaxableEntity::where('type', 'employee')->get()->pluck('id')->toArray();
		return User::whereIn('entity_id', $ids)->orderBy($order, $direction)->get();
	}

	public static function getActiveStaff($order, $direction)
	{
		$ids = TaxableEntity::where('type', 'employee')->where('active', 1)->get()->pluck('id')->toArray();
		return User::whereIn('entity_id', $ids)->orderBy($order, $direction)->get();
	}

  public static function getUsersIndexedByEntity($entity_type)
  {
    //DB::statement('SET GLOBAL group_concat_max_len = 1000000');
    return json_decode(DB::select("
      SELECT
        concat('{',group_concat(t3.binding),'}') as binding
      FROM
        (SELECT
          concat('\"',t2.entity_id,'\":[',group_concat(t2.binding),']') as binding
        FROM
          (SELECT
            t1.entity_id, concat('{\"id\":',t1.id,',\"active\":',t1.active,
              ',\"name\":\"',t1.name,'\"}') as binding
          FROM
            (SELECT
              users.*
            FROM
              users
            LEFT JOIN taxable_entities ON taxable_entities.id = users.entity_id
            WHERE taxable_entities.type in ('" . implode("','", $entity_type) . "')) t1) t2
          GROUP BY t2.entity_id) t3")[0]->binding);
  }

	public function entity()
	{
		return $this->belongsTo('App\TaxableEntity', 'entity_id');
	}

	public function isEmailSetup()
	{
		return	!empty($this->imap_endpoint) &&
				!empty($this->email) &&
				!empty($this->email_password) ;
	}

	public function getEvents($reply = null)
	{
		$query = $this->belongsToMany('App\CalendarEvent', 'calendar_event_participants', 'staff_id', 'calendar_event_id');

		if (!empty($reply)) {
			if (gettype($reply) == 'string') {
				$query->where([['action', '=', $reply], ['active', '=', 1]]);
			} else if (gettype($reply) == 'array') {
				$query->whereIn([['action', '=', $reply], ['active', '=', 1]]);
			}
		}

		return $query;
	}

	public function updateEventReply($eventId, $reply)
	{
		DB::update("update calendar_event_participants set action = '" . $reply . "', updated_at = utc_timestamp() where calendar_event_id = " . $eventId . " and staff_id = " . auth()->user()->id);
	}

	public static function getSystemUser()
	{
		return User::where('entity_id', TaxableEntity::theCompany()->id)->first();
	}

	public function hrDocuments()
	{
		return $this->belongsToMany('App\Document', 'hr_user_document', 'staff_id', 'document_id');
	}

	public function interactions()
	{
		return $this->belongsToMany('App\Interaction', 'interaction_user', 'staff_id', 'interaction_id');
	}

	/**
	 * Send the password reset notification.
	 *
	 * @param  string  $token
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
	    $this->notify(new ResetPasswordNotification($token));
	}
}
