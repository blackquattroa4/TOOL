<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\PaymentTerm;
use App\UniqueTradable;
use App\User;
use App\Currency;
use App\Location;
use App\Address;
use DB;
use App\PurchaseHeader;

class PurchaseOrderView
{
	/*
	 *  when creating order, $id = supplier-id, $createMode = true
	 *  when updating/viewing/approving/processing order, $id = order-id, $createMode = false
	 */
	public static function generateOptionArrayForTemplate($id, $createMode = false)
	{
		$supplierId = $createMode ? $id : PurchaseHeader::find($id)->entity_id;

		$fmtr =  $createMode
							? TaxableEntity::find($id)->currency->getFormat()
							: PurchaseHeader::find($id)->currency->getFormat();

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
		foreach (DB::select("select unique_tradables.id, unique_tradables.sku, ifnull((select purchase_details.manufacture_model from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id=purchase_headers.id and find_in_set('order', purchase_headers.type) and purchase_headers.entity_id=" . $supplierId . " order by purchase_details.updated_at desc limit 1), unique_tradables.sku) as display, ifnull((select purchase_details.description from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id=purchase_headers.id and find_in_set('order', purchase_headers.type) and purchase_headers.entity_id=" . $supplierId . " order by purchase_details.updated_at desc limit 1), unique_tradables.description) as description, ifnull((select purchase_details.unit_price from purchase_details, purchase_headers where purchase_details.unit_price > 0 and purchase_details.unique_tradable_id = unique_tradables.id and purchase_details.header_id=purchase_headers.id and find_in_set('order', purchase_headers.type) and purchase_headers.entity_id=" . $supplierId . " order by purchase_details.updated_at desc limit 1), 0.000) as 'unit_price' from unique_tradables, tradables where (unique_tradables.stockable and unique_tradables.id = tradables.unique_tradable_id and tradables.supplier_entity_id = " . $supplierId . ") or (unique_tradables.expendable AND unique_tradables.id = tradables.unique_tradable_id and (select count(1) > 0 from unique_tradable_restrictions where unique_tradable_id = unique_tradables.id and enforce = 1 and action = 'include' and associated_attribute = 'entity' and (associated_id = 0  or associated_id = " . $supplierId . ")))") as $oneResult) {
			$product_option[$oneResult->id] = $oneResult->sku;
			$extended_product_option[$oneResult->id] = [
					'display' => $oneResult->display,
					'description' => $oneResult->description,
					'unit_price' => sprintf("%0." . $fmtr['fdigit'] . "f" , $oneResult->unit_price),
				];
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

		$billingAddresses = array();
		$shippingAddresses = array();
		$result = Address::where('entity_id', $supplierId)->get();
		foreach ($result as $oneResult) {
			switch ($oneResult->purpose) {
			case 'billing':
				$billingAddresses[$oneResult->id] = [
							'name' => $oneResult->name,
							'street' => $oneResult->street,
							'unit' => $oneResult->unit,
							'district' => $oneResult->district,
							'city' => $oneResult->city,
							'state' => $oneResult->state,
							'country' => $oneResult->country,
							'zipcode' => $oneResult->zipcode,
						];
				break;
			case 'shipping':
				$shippingAddresses[$oneResult->id] = [
							'name' => $oneResult->name,
							'street' => $oneResult->street,
							'unit' => $oneResult->unit,
							'district' => $oneResult->district,
							'city' => $oneResult->city,
							'state' => $oneResult->state,
							'country' => $oneResult->country,
							'zipcode' => $oneResult->zipcode,
						];
				break;
			default:
				break;
			}
		}

		return [
				'supplier' => $supplier,
				'payment' => $payment,
				'contact' => $contact,
				'staff' => $staff,
				'currency' => $currency,
				'warehouse' => $warehouse,
				'product_option' => $product_option,
				'billing_address' => $billingAddresses,
				'shipping_address' => $shippingAddresses,
				'extended_product_option' => $extended_product_option,
			];
	}
}

?>
