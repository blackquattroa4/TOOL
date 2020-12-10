<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseBinTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_detail_id', 'bin_id', 'tradable_id', 'quantity', 'valid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function warehouseDetail()
    {
      return $this->belongsTo('App\WarehouseDetail', 'warehouse_detail_id');
    }

    public function bin()
    {
      return $this->belongsTo('App\WarehouseBin', 'bin_id');
    }

    public function tradable()
    {
      return $this->belongsTo('App\Tradable', 'tradable_id');
    }
}
