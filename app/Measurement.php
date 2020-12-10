<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'symbol', 'type', 'description', 'conversion_ratio', 'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}
