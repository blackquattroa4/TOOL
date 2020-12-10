<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Downloadable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'uploader_id', 'title', 'description', 'original_name', 'file_size', 'mime_type', 'hash', 'valid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function getUploader()
    {
      return $this->hasOne('\App\User', 'id', 'uploader_id');
    }
}
