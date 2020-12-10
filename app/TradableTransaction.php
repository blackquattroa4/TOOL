<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class TradableTransaction extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'unique_tradable_id', 'location_id', 'owner_entity_id', 'quantity', 'unit_cost', 'src_table', 'src_id', 'valid', 'notes',
		'created_at'  // this value can be changed based on invoice date
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function uniqueTradable()
	{
		return $this->belongsTo('\App\UniqueTradable', 'unique_tradable_id');
	}

	public function location()
	{
		return $this->belongsTo('\App\Location', 'location_id');
	}

	public function owner()
	{
		return $this->belongsTo('\App\TaxableEntity', 'owner_entity_id');
	}

	/**
	 * get runrate of supplier/customer or company(0)
	 */
	public static function getRunrate($labels, $entityId)
	{
		$entity = TaxableEntity::find($entityId);

		$template = array_fill_keys($labels, [ 'in' => 0, 'out' => 0 ]);

		$lastLabel = end($labels);
		reset($labels);

		$runrateRawResult = DB::select("SELECT
				    t1.sku,
				    CONCAT('{',
				            GROUP_CONCAT('\"',
				                period,
				                '\":{\"in\":',
				                in_quantity,
				                ',\"out\":',
				                out_quantity,
				                '}'),
				            '}') AS `data`
				FROM
				    (SELECT
				        unique_tradables.sku,
				            DATE_FORMAT(tradable_transactions.created_at, '%Y-%m') AS period,
				            SUM(IF(quantity > 0, quantity, 0)) AS in_quantity,
				            SUM(IF(quantity < 0, - quantity, 0)) AS out_quantity
				    FROM
				        tradable_transactions
				    LEFT JOIN unique_tradables ON unique_tradables.id = tradable_transactions.unique_tradable_id
				    WHERE
				        valid = 1
				            AND src_table = 'transactable_details'
				            AND src_id IN (SELECT
				                id
				            FROM
				                transactable_details
				            WHERE
				                transactable_header_id IN (SELECT
				                        id
				                    FROM
				                        transactable_headers
				                    WHERE
				                        " . ($entityId ? ("entity_id = " . $entityId . " AND ") : "") . "status <> 'void'
																AND incur_date BETWEEN '" . $labels[0] . "-01' AND '" . date("Y-m-t", strtotime($lastLabel . "-01")) . "'))
				            AND stockable = 1
				    GROUP BY sku , period) t1
				GROUP BY t1.sku");

		$runrateData = [ ];

		switch($entity->type) {
			case 'customer':
				foreach ($runrateRawResult as $item) {
					$runrateData[] = [
						'label' => $item->sku,
						'data' => array_column(array_replace($template, json_decode($item->data, true)), 'out')
					];
				}
				break;
			case 'supplier':
			default:
				foreach ($runrateRawResult as $item) {
					$runrateData[] = [
						'label' => $item->sku,
						'data' => array_column(array_replace($template, json_decode($item->data, true)), 'in')
					];
				}
				break;
		}

		return $runrateData;
	}
}
