<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\UniqueTradable;
use App\User;
use App\Currency;
use App\ExpenseHeader;

class ChargeEntryView
{
	/*
	 *  when creating order, $id = supplier-id, $createMode = true
	 *  when updating/viewing/approving/processing order, $id = order-id, $createMode = false
	 */
	public static function generateOptionArrayForTemplate($id, $createMode = false)
	{
		$supplierId = $createMode ? $id : ExpenseHeader::find($id)->entity_id;

		$entity = array();
		foreach (TaxableEntity::getActiveEntities('code', 'asc') as $oneResult) {
			$entity[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
		}

		$item_option = array();
		foreach (UniqueTradable::getActiveExpenditures('sku', 'asc') as $oneResult) {
			$item_option[$oneResult->id] = $oneResult->sku;
		}

		$staff = array();
		foreach (User::getActiveStaff('name', 'asc') as $oneResult) {
			$staff[$oneResult->id] = $oneResult->name;
		}
		
		$currency = array();
		foreach (Currency::getActiveCurrencies('symbol', 'asc') as $oneResult) {
			$currency[$oneResult->id] = $oneResult->symbol . '&emsp;' . $oneResult->description;
		}

		return [
				'entity' => $entity,
				'staff' => $staff,
				'currency' => $currency,
				'item_option' => $item_option,
			];
	}
}

?>