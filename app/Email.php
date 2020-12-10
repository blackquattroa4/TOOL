<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'sent_at', 'folder', 'from', 'subject', 'uid', 'recent', 'seen', 'flagged', 'answered', 'deleted', 'draft',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function user()
    {
      $this->belongsTo('App\User', 'user_id');
    }
}
