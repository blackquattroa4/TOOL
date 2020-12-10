<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\PaymentTerm;
use App\UniqueTradable;
use App\User;
use App\Currency;
use DB;
use App\PurchaseHeader;

class PurchaseQuoteView
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
		$extended_product_option = array();
		foreach (DB::select("select unique_tradables.id, unique_tradables.sku, ifnull((select purchase_details.manufacture_model from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id=purchase_headers.id and find_in_set('quote', purchase_headers.type) and purchase_headers.entity_id=" . $supplierId . " order by purchase_details.updated_at desc limit 1), unique_tradables.sku) as display, ifnull((select purchase_details.description from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id=purchase_headers.id and find_in_set('quote', purchase_headers.type) and purchase_headers.entity_id=" . $supplierId . " order by purchase_details.updated_at desc limit 1), unique_tradables.description) as description from unique_tradables, tradables where unique_tradables.stockable and unique_tradables.id = tradables.unique_tradable_id and tradables.supplier_entity_id = " . $supplierId) as $oneResult) {
			$product_option[$oneResult->id] = $oneResult->sku;
			$extended_product_option[$oneResult->id] = [
					'display' => $oneResult->display,
					'description' => $oneResult->description,
				];
		}

		foreach (DB::select("select unique_tradables.id, unique_tradables.sku from unique_tradables, tradables where unique_tradables.stockable and unique_tradables.id = tradables.unique_tradable_id and tradables.supplier_entity_id = " . $supplierId) as $oneResult) {
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

		return [
				'supplier' => $supplier,
				'payment' => $payment,
				'contact' => $contact,
				'staff' => $staff,
				'currency' => $currency,
				'product_option' => $product_option,
				'extended_product_option' => $extended_product_option,
			];
	}
}

?>
