<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\PaymentTerm;
use App\UniqueTradable;
use App\User;
use App\Currency;
use App\Location;
use DB;
use App\PurchaseHeader;

class PurchaseProcessView
{
	/*
	 *  when creating order, $id = supplier-id, $createMode = true
	 *  when updating/viewing/approving/processing order, $id = order-id, $createMode = false
	 */
	public static function generateOptionArrayForTemplate($id, $createMode = false)
	{
		$supplierId = $createMode ? $id : PurchaseHeader::find($id)->entity_id;

		$supplier = array();
		foreach (TaxableEntity::getActiveSuppliers('code', 'asc') as $oneResult) {
			$supplier[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
		}

		$payment = array();
		foreach (PaymentTerm::getActivePaymentTerms('symbol', 'asc') as $oneResult) {
			$payment[$oneResult->id] = $oneResult->symbol . "&emsp;" . $oneResult->description;
		}

		$product_option = array();
		foreach (DB::select("select unique_tradables.id, unique_tradables.sku from unique_tradables, tradables where (unique_tradables.stockable and unique_tradables.id = tradables.unique_tradable_id and tradables.supplier_entity_id = " . $supplierId . ") or (unique_tradables.expendable and unique_tradables.id = tradables.unique_tradable_id)") as $oneResult) {
			$product_option[$oneResult->id] = $oneResult->sku;
		}

		$contact = array();
		foreach (User::getEntityContacts($supplierId, 'name', 'asc') as $oneResult) {
			$contact[$oneResult->id] = $oneResult->name;
		}

		$staff = array();
		foreach (User::getAllStaff('name', 'asc') as $oneResult) {
			$staff[$oneResult->id] = $oneResult->name;
		}

		$currency = array();
		foreach (Currency::getActiveCurrencies('symbol', 'asc') as $oneResult) {
			$currency[$oneResult->id] = $oneResult->symbol . '&emsp;' . $oneResult->description;
		}

		$warehouse = array();
		foreach (Location::getActiveWarehouses('name', 'asc') as $oneResult) {
			$warehouse[$oneResult->id] = $oneResult->symbol . '&emsp;' . $oneResult->name;
		}

		return [
				'supplier' => $supplier,
				'payment' => $payment,
				'contact' => $contact,
				'staff' => $staff,
				'currency' => $currency,
				'warehouse' => $warehouse,
				'product_option' => $product_option,
			];
	}
}

?>
