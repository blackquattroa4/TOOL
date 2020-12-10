<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class UniqueTradable extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'sku', 'description', 'product_id', 'current', 'phasing_out', 'stockable', 'expendable', 'forecastable', 'replacing_unique_tradable_id', 'replaced_by_unique_tradable_id', 'expense_t_account_id', 'cogs_t_account_id',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function getActiveOnes($order, $direction)
	{
		return UniqueTradable::where('current', 1)->orderBy($order, $direction)->get();
	}

	public static function getProducts($order, $direction)
	{
		return UniqueTradable::where('stockable', 1)->orderBy($order, $direction)->get();
	}

	public static function getProductsOfSupplier($supplierId, $order, $direction)
	{
		return UniqueTradable::select('unique_tradables.*')
			->leftjoin('tradables', 'tradables.unique_tradable_id', '=', 'unique_tradables.id')
			->where('stockable', 1)->where('supplier_entity_id', $supplierId)->orderBy($order, $direction)->get();
	}

	public static function getActiveProducts($order, $direction)
	{
		return UniqueTradable::where('current', 1)->where('stockable', 1)->orderBy($order, $direction)->get();
	}

	public static function getActiveExpenditures($order, $direction)
	{
		return UniqueTradable::where('current', 1)->where('expendable', 1)->orderBy($order, $direction)->get();
	}

	public static function getApTransferItem()
	{
		return UniqueTradable::where([
					['sku', '=', 'A/P transfer'],
					['current', '=', 1],
					['expendable', '=', 1]
				])->first();
	}

	public function tradables()
	{
		return $this->hasMany('App\Tradable', 'unique_tradable_id');
	}

	public function tradableByEntity($entityId)
	{
		$tradables = $this->tradables()->where('supplier_entity_id', $entityId)->first();

		return $tradables ?? $this->tradables->last();
	}

	public function getInventory($date, $location, $owner=-1)
	{
		$tx = TradableTransaction::where('unique_tradable_id', $this->id)
					->where('location_id', $location)
					->where('created_at', '<=', $date.' 23:59:59')
					->where('valid', 1);
		if ($owner != -1) {
			$tx = $tx->where('owner_entity_id', $owner);
		}
		return $tx->sum('quantity');
	}

	public function getWarehouseInventory($date, $location)
	{
		$tx = WarehouseBinTransaction::select('warehouse_bin_transactions.quantity')
					->leftjoin('tradables', 'tradables.id', '=', 'warehouse_bin_transactions.tradable_id')
					->leftjoin('warehouse_bins', 'warehouse_bins.id', '=', 'warehouse_bin_transactions.bin_id')
					->where('tradables.unique_tradable_id', $this->id)
					->where('warehouse_bins.location_id', $location)
					->where('warehouse_bin_transactions.created_at', '<=', $date.' 23:59:59')
					->where('warehouse_bin_transactions.valid', 1);

		return $tx->sum('quantity');
	}

	public function getWarehouseBins($location_id)
	{
		$tx = DB::select("select warehouse_bins.id, warehouse_bins.name, sum(warehouse_bin_transactions.quantity) as quantity from warehouse_bin_transactions left join warehouse_bins on warehouse_bins.id = warehouse_bin_transactions.bin_id left join tradables on tradables.id = warehouse_bin_transactions.tradable_id where warehouse_bin_transactions.valid = 1 and tradables.unique_tradable_id = " . $this->id . " and warehouse_bins.location_id = " . $location_id . " group by id, name having sum(warehouse_bin_transactions.quantity) > 0 order by id");

		return array_column($tx, "name");
	}

	public function getUnitCost($quantity, $location, $owner=-1)
	{
		$param = Parameter::where('key', 'cost_of_good_sold_method')->first();
		$costCalculationMethod = unserialize($param->value);

		// find all outgoing quantity
		$outqty = DB::select(DB::raw("SELECT unique_tradable_id, sum(quantity) as qty FROM tradable_transactions x1 where unique_tradable_id = " . $this->id . " and quantity < 0 and location_id = " . $location . " and valid = 1" . (($owner != -1) ? " and owner_entity_id = " . $owner : "") . " group by unique_tradable_id"));
		$outqty = count($outqty) ? $outqty[0]->qty : 0;

		// inner query that sort inventory batch, after subtracting all outgoing quantity
		$subSubQuery = "SELECT id, quantity, unit_cost, if(@sum < 0, 0, @sum) as prev_balance, (@sum := @sum + x1.quantity) AS acc_qty, (@ttl := @ttl + x1.quantity * x1.unit_cost) AS acc_ttl FROM tradable_transactions x1 CROSS JOIN (SELECT @sum := " . $outqty . ") t1	CROSS JOIN (SELECT @ttl := 0) t2 CROSS JOIN (SELECT @req := " . $quantity . ") t3	WHERE unique_tradable_id = " . $this->id . " AND quantity > 0 AND location_id = " . $location . " AND valid = 1 AND owner_entity_id = " . $owner . " ORDER BY created_at " . (($costCalculationMethod == 'lifo') ? "DESC" : "ASC");

		if ($costCalculationMethod == 'average') {
			$query = "select sum(acc_qty - prev_balance) final_qty, sum(unit_cost * (acc_qty - prev_balance)) final_ttl from (" . $subSubQuery . ") v1 where acc_qty > 0";
		} else {
			$subQuery = "SELECT id,	unit_cost, (acc_qty - prev_balance) AS available,	if(@req > (acc_qty - prev_balance), (acc_qty - prev_balance), @req) as take, @req := @req - if(@req > (acc_qty - prev_balance), (acc_qty - prev_balance), @req) as balance FROM (" . $subSubQuery . ") v1 WHERE acc_qty > 0";
			$query = "SELECT sum(unit_cost*take) AS final_ttl, sum(take) AS final_qty FROM (" . $subQuery . ") v2 WHERE take > 0";
		}

		$result = DB::select(DB::raw($query));

		return $result[0]->final_ttl / $result[0]->final_qty;
	}

	public function getConsignedBatch($quantity, $location, $date)
	{
		$ownerId = TaxableEntity::theCompany()->id;

		// consignment picking is FIFO only.
		$outqty = DB::select(DB::raw("SELECT unique_tradable_id, sum(quantity) as qty FROM tradable_transactions x1 where unique_tradable_id = " . $this->id . " and quantity < 0 and location_id = " . $location . " and valid = 1 and owner_entity_id <> " . $ownerId . " group by unique_tradable_id"));
		$outqty = count($outqty) ? $outqty[0]->qty : 0;

		$subSubQuery = "SELECT id, owner_entity_id, quantity, unit_cost, if(@sum < 0, 0, @sum) as prev_balance, (@sum := @sum + x1.quantity) AS acc_qty, (@ttl := @ttl + x1.quantity * x1.unit_cost) AS acc_ttl FROM tradable_transactions x1 CROSS JOIN (SELECT @sum := " . $outqty . ") t1	CROSS JOIN (SELECT @ttl := 0) t2 CROSS JOIN (SELECT @req := " . $quantity . ") t3	WHERE unique_tradable_id = " . $this->id . " AND quantity > 0 AND location_id = " . $location . " AND valid = 1 AND owner_entity_id <> " . $ownerId . " ORDER BY created_at ASC";
		$subQuery = "SELECT id,	owner_entity_id, unit_cost, (acc_qty - prev_balance) AS available,	if(@req > (acc_qty - prev_balance), (acc_qty - prev_balance), @req) as take, @req := @req - if(@req > (acc_qty - prev_balance), (acc_qty - prev_balance), @req) as balance FROM (" . $subSubQuery . ") v1 WHERE acc_qty > 0";
		// first N entry that fulfill 'quantity'
		$query = "SELECT owner_entity_id, " . $this->id . " AS unique_tradable_id, take AS quantity, unit_cost FROM (" . $subQuery . ") v2 WHERE take > 0";

		$result = DB::select(DB::raw($query));

		return array_map(function($val) { return (array)$val; }, $result);
	}

	// calculate aging using data from tradable_transactions
	public function getAging($quantity, $location, $date, $owner=-1)
	{
		$param = Parameter::where('key', 'cost_of_good_sold_method')->first();
		$costCalculationMethod = unserialize($param->value);

		$aging = [
			'days' => 0.00,
			'quantity' => 0,
			'batches' => [],
		];

		switch($costCalculationMethod) {
			case 'lifo':
				$result = DB::select(DB::raw("SELECT quantity, datediff('" . $date . "', created_at) as age FROM tradable_transactions x1 where unique_tradable_id = " . $this->id . " and quantity > 0 and location_id = " . $location . " and valid = 1" . (($owner != -1) ? " and owner_entity_id = " . $owner : "") . " order by created_at"));
				// first N entry that fulfill 'quantity'
				$countDown = $quantity;
				while (($countDown > 0) && (current($result))) {
					$batch = current($result);
					if (($countDown) <= ($batch->quantity)) {
						$aging['days'] += $batch->age * $countDown;
						$aging['quantity'] += $countDown;
						$aging['batches'][] = [
								'description' => '',  // placeholder for future use
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $countDown),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown = 0;
					} else {
						$aging['days'] += $batch->age * $batch->quantity;
						$aging['quantity'] += $batch->quantity;
						$aging['batches'][] = [
								'description' => '',  // placeholder for future use
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $batch->quantity),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown -= $batch->quantity;
					}
					next($result);
				}
				break;
			case 'average':
			case 'fifo':
			default:
				$result = DB::select(DB::raw("SELECT quantity, datediff('" . $date . "', created_at) as age FROM tradable_transactions x1 where unique_tradable_id = " . $this->id . " and quantity > 0 and location_id = " . $location . " and valid = 1" . (($owner != -1) ? " and owner_entity_id = " . $owner : "") . " order by created_at desc"));
				// last N entry that fulfill 'quantity'
				$countDown = $quantity;
				while (($countDown > 0) && (current($result))) {
					$batch = current($result);
					if (($countDown) <= ($batch->quantity)) {
						$aging['days'] += $batch->age * $countDown;
						$aging['quantity'] += $countDown;
						$aging['batches'][] = [
								'description' => '',  // placeholder for future use
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $countDown),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown = 0;
					} else {
						$aging['days'] += $batch->age * $batch->quantity;
						$aging['quantity'] += $batch->quantity;
						$aging['batches'][] = [
								'description' => '',  // placeholder for future use
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $batch->quantity),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown -= $batch->quantity;
					}
					next($result);
				}
				break;
		}
		$aging['days'] = sprintf("%0.1f", ($aging['quantity'] == 0) ? 0 : ($aging['days'] / $aging['quantity']));
		$aging['quantity'] = sprintf(env('APP_QUANTITY_FORMAT'), $aging['quantity']);

		return $aging;
	}

	// calculate aging using data from warehouse_bin_transactions
	public function getWarehouseAging($quantity, $location, $date)
	{
		$param = Parameter::where('key', 'cost_of_good_sold_method')->first();
		$costCalculationMethod = unserialize($param->value);

		$tradable_ids = $this->tradables->pluck('id')->toArray();
		$bin_ids = Location::find($location)->bins->pluck('id')->toArray();

		$aging = [
			'days' => 0.00,
			'quantity' => 0,
			'batches' => [],
		];

		switch($costCalculationMethod) {
			case 'lifo':
				$result = DB::select(DB::raw("SELECT name, quantity, datediff('" . $date . "', x1.created_at) AS age FROM warehouse_bin_transactions x1 LEFT JOIN warehouse_bins ON x1.bin_id = warehouse_bins.id WHERE tradable_id IN (" . implode(",", $tradable_ids) . ") AND quantity > 0 AND bin_id IN (" . implode(",", $bin_ids) . ") AND x1.valid = 1 ORDER BY x1.created_at"));
				// first N entry that fulfill 'quantity'
				$countDown = $quantity;
				while (($countDown > 0) && (current($result))) {
					$batch = current($result);
					if (($countDown) <= ($batch->quantity)) {
						$aging['days'] += $batch->age * $countDown;
						$aging['quantity'] += $countDown;
						$aging['batches'][] = [
								'description' => $batch->name,
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $countDown),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown = 0;
					} else {
						$aging['days'] += $batch->age * $batch->quantity;
						$aging['quantity'] += $batch->quantity;
						$aging['batches'][] = [
								'description' => $batch->name,
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $batch->quantity),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown -= $batch->quantity;
					}
					next($result);
				}
				break;
			case 'average':
			case 'fifo':
			default:
				$result = DB::select(DB::raw("SELECT name, quantity, datediff('" . $date . "', x1.created_at) AS age FROM warehouse_bin_transactions x1 LEFT JOIN warehouse_bins ON x1.bin_id = warehouse_bins.id WHERE tradable_id IN (" . implode(",", $tradable_ids) . ") AND quantity > 0 AND bin_id IN (" . implode(",", $bin_ids) . ") AND x1.valid = 1 ORDER BY x1.created_at DESC"));
				// last N entry that fulfill 'quantity'
				$countDown = $quantity;
				while (($countDown > 0) && (current($result))) {
					$batch = current($result);
					if (($countDown) <= ($batch->quantity)) {
						$aging['days'] += $batch->age * $countDown;
						$aging['quantity'] += $countDown;
						$aging['batches'][] = [
								'description' => $batch->name,
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $countDown),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown = 0;
					} else {
						$aging['days'] += $batch->age * $batch->quantity;
						$aging['quantity'] += $batch->quantity;
						$aging['batches'][] = [
								'description' => $batch->name,
								'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $batch->quantity),
								'days' => sprintf("%0.1f", $batch->age),
							];
						$countDown -= $batch->quantity;
					}
					next($result);
				}
				break;
		}
		$aging['days'] = sprintf("%0.1f", ($aging['quantity'] == 0) ? 0 : ($aging['days'] / $aging['quantity']));
		$aging['quantity'] = sprintf(env('APP_QUANTITY_FORMAT'), $aging['quantity']);

		return $aging;
	}

	public function getUnitReturnCost($quantity, $location, $customer)
	{
		// use last-sold-price
		$record = DB::select("select * from tradable_transactions join transactable_details on tradable_transactions.src_id = transactable_details.id join transactable_headers on transactable_headers.id = transactable_details.transactable_header_id where tradable_transactions.unique_tradable_id = " . $this->id . " and tradable_transactions.location_id = " . $location . " and tradable_transactions.valid = 1 and tradable_transactions.src_table = 'transactable_details' and transactable_headers.status <> 'void' and transactable_headers.entity_id = " . $customer . " and tradable_transactions.quantity < 0 order by tradable_transactions.created_at desc");

		return (count($record) > 0) ? $record[0]->unit_cost : 0.00;
	}

	public function getLastEntry($entityId, $table, $type) {
		switch ($table) {
			case 'purchase_details':
				$result = DB::select("select unique_tradables.id, unique_tradables.sku, ifnull((select purchase_details.manufacture_model from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id = purchase_headers.id and find_in_set('" . $type . "', purchase_headers.type) and purchase_headers.entity_id = " . $entityId . " order by purchase_details.updated_at desc limit 1), unique_tradables.sku) as display, ifnull((select purchase_details.description from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id=purchase_headers.id and find_in_set('" . $type . "', purchase_headers.type) and purchase_headers.entity_id = " . $entityId . " order by purchase_details.updated_at desc limit 1), unique_tradables.description) as description, ifnull((select purchase_details.unit_price from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id = purchase_headers.id and find_in_set('" . $type . "', purchase_headers.type) and purchase_headers.entity_id = " . $entityId . " order by purchase_details.updated_at desc limit 1), 0.000) as 'unit_price' from unique_tradables, tradables where unique_tradables.stockable and unique_tradables.id = tradables.unique_tradable_id and tradables.supplier_entity_id = " . $entityId . " and unique_tradables.id = " . $this->id)[0];
				break;
			case 'sales_details':
				$result = DB::select("SELECT unique_tradables.id,  unique_tradables.sku, ifnull((select sales_details.display_as from sales_details, sales_headers where sales_details.unit_price > 0 and sales_details.unique_tradable_id = unique_tradables.id and sales_details.header_id=sales_headers.id and find_in_set('" . $type . "', sales_headers.type) and sales_headers.entity_id = " . $entityId . " order by sales_details.updated_at desc limit 1), unique_tradables.sku) as display, ifnull((select sales_details.description from sales_details, sales_headers where sales_details.unit_price > 0 and sales_details.unique_tradable_id = unique_tradables.id and sales_details.header_id = sales_headers.id and find_in_set('" . $type . "', sales_headers.type) and sales_headers.entity_id = " . $entityId . " order by sales_details.updated_at desc limit 1), unique_tradables.description) as description, ifnull((select sales_details.unit_price from sales_details, sales_headers where sales_details.unit_price > 0 and sales_details.unique_tradable_id = unique_tradables.id and sales_details.header_id = sales_headers.id and find_in_set('" . $type . "', sales_headers.type) and sales_headers.entity_id=" . $entityId . " order by sales_details.updated_at desc limit 1), 0.000) as 'unit_price' FROM unique_tradables, unique_tradable_restrictions where current = 1 and enforce = 1 and unique_tradables.id = unique_tradable_restrictions.unique_tradable_id and find_in_set('include', `action`) and ((find_in_set('region', associated_attribute) and (associated_id = 0 or associated_id = (select region_id from taxable_entities where id = " . $entityId . "))) or (find_in_set('entity', associated_attribute) and (associated_id = 0 or associated_id = " . $entityId . "))) and unique_tradables.id = " . $this->id)[0];
				break;
			default:
				$result = new \stdClass();
				$result->id = $this->id;
				$result->sku = $this->sku;
				$result->display = $this->sku;
				$result->description = $this->description;
				$result->unit_price = '0.00';
				break;
			}

		return $result;
	}

	public static function getUniqueTradableIndexedBySupplierEntity() {
		// DB::statement('SET GLOBAL group_concat_max_len = 1000000');
		return json_decode(DB::select("
			SELECT
				concat('{',group_concat(t3.binding),'}') as binding
			FROM
				(SELECT
					concat('\"',t2.entity_id,'\":{',group_concat(t2.binding),'}') as binding
				FROM
					(SELECT
						t1.entity_id, concat('\"',t1.id,'\":{',
							'\"sku\":\"',t1.sku,
							'\",\"unit_price\":',t1.unit_price,
							',\"display\":\"',replace(t1.display, '\"', '\\\\\"'),
							'\",\"description\":\"',replace(t1.description, '\"', '\\\\\"'),
							'\"}') as binding
					FROM
						(SELECT
							unique_tradables.id,
							unique_tradables.sku,
							IFNULL((SELECT
									purchase_details.unit_price
								FROM
									purchase_details,
									purchase_headers
								WHERE
									purchase_details.unit_price > 0
										AND purchase_details.unique_tradable_id = tradables.unique_tradable_id
										AND purchase_details.header_id = purchase_headers.id
										AND FIND_IN_SET('order', purchase_headers.type)
										AND purchase_headers.entity_id = tradables.supplier_entity_id
								ORDER BY purchase_details.updated_at DESC
								LIMIT 1),
					            0.000) AS 'unit_price',
					    IFNULL((SELECT
									purchase_details.manufacture_model
								FROM
									purchase_details,
									purchase_headers
								WHERE
									purchase_details.unit_price > 0
										AND purchase_details.unique_tradable_id = tradables.unique_tradable_id
					                        AND purchase_details.header_id = purchase_headers.id
					                        AND FIND_IN_SET('order', purchase_headers.type)
					                        AND purchase_headers.entity_id = tradables.supplier_entity_id
					                ORDER BY purchase_details.updated_at DESC
					                LIMIT 1),
					            unique_tradables.sku) AS display,
					    IFNULL((SELECT
									purchase_details.description
								FROM
									purchase_details,
									purchase_headers
								WHERE
									purchase_details.unit_price > 0
										AND purchase_details.unique_tradable_id = tradables.unique_tradable_id
										AND purchase_details.header_id = purchase_headers.id
										AND FIND_IN_SET('order', purchase_headers.type)
										AND purchase_headers.entity_id = tradables.supplier_entity_id
								ORDER BY purchase_details.updated_at DESC
								LIMIT 1),
							unique_tradables.description) AS description,
							tradables.supplier_entity_id as entity_id
						FROM
							tradables
						LEFT JOIN unique_tradables ON unique_tradables.id = tradables.unique_tradable_id
						) t1) t2
					GROUP BY t2.entity_id) t3")[0]->binding);
	}

	public static function getUniqueTradableIndexedByCustomerEntity() {
		// DB::statement('SET GLOBAL group_concat_max_len = 1000000');

		// the having-clause is to eliminate any product restricted by
		// rules in unique_tradable_restrictions
		return json_decode(DB::select("
			SELECT
				concat('{',group_concat(t3.binding),'}') as binding
			FROM
				(SELECT
					concat('\"',t2.entity_id,'\":{',group_concat(t2.binding),'}') as binding
				FROM
					(SELECT
						t1.entity_id, concat('\"',t1.id,'\":{',
							'\"sku\":\"',t1.sku,
							'\",\"unit_price\":',t1.unit_price,
							',\"display\":\"',replace(t1.display, '\"', '\\\\\"'),
							'\",\"description\":\"',replace(t1.description, '\"', '\\\\\"'),
							'\"}') as binding
					FROM
						(SELECT
    					y1.unique_tradable_id AS id,
    					y2.sku,
    					IFNULL((SELECT
                    sales_details.unit_price
                FROM
                    sales_details,
                    sales_headers
                WHERE
                    sales_details.unit_price > 0
                        AND sales_details.unique_tradable_id = y1.unique_tradable_id
                        AND sales_details.header_id = sales_headers.id
                        AND FIND_IN_SET('order', sales_headers.type)
                        AND sales_headers.entity_id = y1.taxable_entity_id
                ORDER BY sales_details.updated_at DESC
                LIMIT 1), 0.000) AS 'unit_price',
    					IFNULL((SELECT
                    sales_details.display_as
                FROM
                    sales_details,
                    sales_headers
                WHERE
                    sales_details.unit_price > 0
                        AND sales_details.unique_tradable_id = y1.unique_tradable_id
                        AND sales_details.header_id = sales_headers.id
                        AND FIND_IN_SET('order', sales_headers.type)
                        AND sales_headers.entity_id = y1.taxable_entity_id
                ORDER BY sales_details.updated_at DESC
                LIMIT 1), y2.sku) AS display,
    					IFNULL((SELECT
                    sales_details.description
                FROM
                    sales_details,
                    sales_headers
                WHERE
                    sales_details.unit_price > 0
                        AND sales_details.unique_tradable_id = y1.unique_tradable_id
                        AND sales_details.header_id = sales_headers.id
                        AND FIND_IN_SET('order', sales_headers.type)
                        AND sales_headers.entity_id = y1.taxable_entity_id
                ORDER BY sales_details.updated_at DESC
                LIMIT 1), y2.description) AS description,
    					y1.taxable_entity_id AS entity_id
						FROM
    					(SELECT
        				x1.unique_tradable_id, x1.taxable_entity_id
    					FROM
        				(SELECT
        					unique_tradables.id AS unique_tradable_id,
            			taxable_entities.id AS taxable_entity_id,
            			taxable_entities.type AS taxable_entity_type
    						FROM
        					unique_tradables
  							CROSS JOIN taxable_entities) x1
    						WHERE
      						x1.taxable_entity_type IN ('employee' , 'supplier', 'customer')) y1
    				LEFT OUTER JOIN
    					(SELECT
        				unique_tradable_id, z1.id AS taxable_entity_id
  						FROM
        				unique_tradable_restrictions
  						CROSS JOIN taxable_entities z1
    						WHERE
        					`action` = 'exclude'
          						AND associated_attribute = 'entity'
            					AND associated_id = 0
            					AND enforce = 1
							UNION
							SELECT
        				unique_tradable_id, associated_id AS taxable_entity_id
    					FROM
        				unique_tradable_restrictions
    					WHERE
        				`action` = 'exclude'
            				AND associated_attribute = 'entity'
            				AND associated_id <> 0
            				AND enforce = 1) exclusion ON y1.taxable_entity_id = exclusion.taxable_entity_id
        						AND y1.unique_tradable_id = exclusion.unique_tradable_id
    			LEFT JOIN
						unique_tradables y2 ON y2.id = y1.unique_tradable_id
					WHERE
    				exclusion.unique_tradable_id IS NULL
        				AND exclusion.taxable_entity_id IS NULL) t1) t2
					GROUP BY t2.entity_id) t3")[0]->binding);
	}
}
