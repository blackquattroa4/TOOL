<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class WarehouseBin extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'location_id', 'name', 'valid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getBinIndexedByUniqueTradableAndLocation($order_type)
    {
      switch ($order_type) {
        case 'receive':
          $result = array_flip(UniqueTradable::pluck('id')->toArray());
          $bins = DB::select("
          SELECT
          	concat('{', group_concat(concat('\"', t1.location_id, '\":{', t1.binding, '}')), '}') AS bin_binding
          FROM(
            SELECT
              locations.id as location_id,
              IFNULL((
                SELECT
                  GROUP_CONCAT(CONCAT('\"',warehouse_bins.id,'\":\"',warehouse_bins.name,'\"'))
                FROM
                  warehouse_bins
                WHERE
                  warehouse_bins.valid = 1
                    AND warehouse_bins.location_id = locations.id),
              '\"0\":\"\"') AS binding
            FROM
              locations
          ) AS t1
          ")[0]->bin_binding;
          $bins = is_null($bins) ? null : json_decode($bins, true);
          array_walk($result, function(&$element) use ($bins) {
            $element = $bins;
          });
          break;
        case 'deliver':
          $result = array_column(DB::select("
          select
          	t4.unique_tradable_id,
            concat('{',t4.binding,'}') as binding
          from
            (select
            	t3.unique_tradable_id,
              group_concat('\"',t3.location_id,'\":{',t3.binding,'}') as binding
            from
              (select
                unique_tradables.id as unique_tradable_id,
            	  locations.id as location_id,
                ifnull(t2.binding, '\"0\":\"\"') as binding
              from unique_tradables
              cross join locations on 1
              left join
                (select
            	    t1.unique_tradable_id,
                  t1.location_id,
                  group_concat('\"',t1.bin_id,'\":\"',t1.name,'\"') as binding
                from (
                  select unique_tradable_id, location_id, bin_id, name, sum(quantity) as quantity
                  from warehouse_bin_transactions
                  left join tradables on tradables.id = warehouse_bin_transactions.tradable_id
                  left join warehouse_bins on warehouse_bin_transactions.bin_id = warehouse_bins.id
                  where warehouse_bin_transactions.valid = 1 and
                    warehouse_bin_transactions.created_at <= utc_timestamp()
                  group by unique_tradable_id, location_id, bin_id, name
                ) as t1
              where quantity > 0
              group by unique_tradable_id, location_id
              ) as t2 on t2.unique_tradable_id = unique_tradables.id and t2.location_id = locations.id
            ) as t3 group by t3.unique_tradable_id
          ) as t4
          "), "binding", "unique_tradable_id");

          array_walk($result, function(&$element, $key) {
            $element = is_null($element) ? null : json_decode($element, true);
          });
          break;
        default:
          $result = null;
          break;
      }

      return $result;
    }

    public function hasHowManyTradable($tradableId)
    {
      return DB::select("SELECT sum(quantity) AS quantity FROM warehouse_bin_transactions WHERE tradable_id = " . $tradableId . " AND bin_id = " . $this->id . " AND valid = 1 AND created_at <= utc_timestamp()")[0]->quantity;
    }

    public function hasHowManyUniqueTradable($uniqueTradableId)
    {
      return DB::select("SELECT sum(quantity) AS quantity FROM warehouse_bin_transactions WHERE tradable_id IN (SELECT id FROM tradables WHERE unique_tradable_id = " . $uniqueTradableId . ") AND bin_id = " . $this->id . " AND valid = 1 AND created_at <= utc_timestamp()")[0]->quantity;
    }

    // return array of tradable & quantity.
    public function getTradableBatches($uniqueTradable, $quantity)
    {
      $param = Parameter::where('key', 'cost_of_good_sold_method')->first();
      $costCalculationMethod = unserialize($param->value);

      $tradableIds = $uniqueTradable->tradables->pluck('id')->toArray();

      $query = WarehouseBinTransaction::where('bin_id', $this->id)->whereIn('tradable_id', $tradableIds)->where('valid', 1);

      $outgoingQuantities = array_map(function ($item) { return array_sum($item); },
        (clone $query)->where('quantity', '<', 0)->orderBy('created_at')->get()->mapToGroups(function ($item, $key) {
          return [ $item->tradable_id => - $item->quantity ];
        })->toArray());

      $incomingBatches = $query->where('quantity', '>', 0)->orderBy('created_at', ($costCalculationMethod == 'lifo') ? 'desc' : 'asc')->get()->map(function ($item, $key) {
        return [
          'id' => $item->id,
          'tradable_id' => $item->tradable_id,
          'quantity' => $item->quantity,
          'balance' => $item->quantity
        ];
      })->toArray();

      foreach ($incomingBatches as $key => $batch) {
        if (array_key_exists($batch['tradable_id'], $outgoingQuantities)) {
          if ($batch['balance'] >= $outgoingQuantities[$batch['tradable_id']]) {
            $incomingBatches[$key]['balance'] -= $outgoingQuantities[$batch['tradable_id']];
            unset($outgoingQuantities[$batch['tradable_id']]);
            if ($incomingBatches[$key]['balance'] == 0) {
              unset($incomingBatches[$key]);
            }
          } else {
            $outgoingQuantities[$batch['tradable_id']] -= $incomingBatches[$key]['balance'];
            $incomingBatches[$key]['balance'] = 0;
          }
        }
      }

      $incomingBatches = array_filter( $incomingBatches, function ($item) { return $item['balance'] > 0; });

      $result = [];

      foreach ($incomingBatches as $content) {
        if ($quantity > 0) {
          if ($quantity > $content['balance']) {
            array_push($result, [
              'id' => $content['id'],
              'tradable_id' => $content['tradable_id'],
              'quantity' => $content['balance']
            ]);
            $quantity -= $content['balance'];
          } else {
            array_push($result, [
              'id' => $content['id'],
              'tradable_id' => $content['tradable_id'],
              'quantity' => $quantity
            ]);
            $quantity = 0;
          }
        } else {
          break;
        }
      }

      return $result;
    }

}
