<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\TradableTransaction;
use DB;

class InventoryHelper
{
	public static function getAccountingInventory($date, $location = null, $owner = null)
	{
		$query = "select t1.location_id, locations.name as location, t1.owner_entity_id as entity_id, taxable_entities.code as entity, t1.unique_tradable_id as sku_id, unique_tradables.sku, t1.total as balance from (select location_id, owner_entity_id, unique_tradable_id, sum(quantity) as total from tradable_transactions where valid = 1 and created_at < '" . $date . " 23:59:59'";

		if ($location) {
			$query .= " and tradable_transactions.location_id = " . $location->id;
		}

		if ($owner) {
			$query .= " and tradable_transactions.owner_entity_id = " . $owner->id;
		}

		$query .= " group by location_id, unique_tradable_id, owner_entity_id) t1 left join locations on t1.location_id = locations.id left join taxable_entities on t1.owner_entity_id = taxable_entities.id left join unique_tradables on t1.unique_tradable_id = unique_tradables.id";

		return array_map(function($item) { return (array)$item; }, DB::select($query));
	}

	public static function getWarehouseInventory($date, $location = null)
	{
		$query = "select t1.location_id, locations.name as location, t1.unique_tradable_id as sku_id, unique_tradables.sku, t1.total as balance from (select location_id, unique_tradable_id, sum(quantity) as total from warehouse_bin_transactions left join warehouse_bins on warehouse_bin_transactions.bin_id = warehouse_bins.id left join tradables on warehouse_bin_transactions.tradable_id = tradables.id where warehouse_bin_transactions.valid = 1 and warehouse_bin_transactions.created_at < '" . $date . " 23:59:59'";

		if ($location) {
			$query .= " and warehouse_bins.location_id = " . $location->id;
		}

		$query .= " group by location_id, unique_tradable_id) t1 left join locations on t1.location_id = locations.id left join unique_tradables on unique_tradables.id = t1.unique_tradable_id";

		return array_map(function($item) { return (array)$item; }, DB::select($query));
	}

	public static function getInventoryOwners($location = null)
	{
		$result = TradableTransaction::distinct()->select('owner_entity_id');

		if ($location) {
			$result = $result->where('location_id', $location->id);
		}

		$ids = $result->pluck('owner_entity_id')->toArray();

		return TaxableEntity::whereIn('id', $ids)->get();
	}
}
?>
