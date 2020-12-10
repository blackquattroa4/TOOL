<?php

namespace App;

use App\CommissionProfile;
use Illuminate\Database\Eloquent\Model;

class CommissionSource extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'profile_id', 'source_product_id', 'source_entity_id', 'per_piece_rate', 'per_piece_method',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function profile()
    {
      return $this->belongsTo('App\CommissionProfile', 'profile_id');
    }
}
