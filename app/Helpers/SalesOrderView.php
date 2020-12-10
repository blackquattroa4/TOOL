<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\PaymentTerm;
use App\UniqueTradable;
use App\User;
use App\Currency;
use App\Address;
use App\SalesHeader;
use DB;

class SalesOrderView
{
	/*
	 *  when creating order, $id = supplier-id, $createMode = true
	 *  when updating/viewing/approving/processing order, $id = order-id, $createMode = false
	 */
	public static function generateOptionArrayForTemplate($id, $createMode = false)
	{
		$customerId = $createMode ? $id : SalesHeader::find($id)->entity_id;

		$fmtr =  $createMode
							? TaxableEntity::find($id)->currency->getFormat()
							: SalesHeader::find($id)->currency->getFormat();

		$customer = array();
		foreach (TaxableEntity::getActiveCustomers('code', 'asc') as $oneResult) {
			$customer[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
		}

		$payment = array();
		foreach (PaymentTerm::getActivePaymentTerms('symbol', 'asc') as $oneResult) {
			$payment[$oneResult->id] = $oneResult->symbol . "&emsp;" . $oneResult->description;
		}

		$product_option = array();
		$extended_product_option = array();
		// foreach (UniqueTradable::getActiveProducts('sku', 'asc') as $oneResult) {
		foreach (DB::select("SELECT unique_tradables.id,  unique_tradables.sku, ifnull((select sales_details.display_as from sales_details, sales_headers where sales_details.unit_price > 0 and sales_details.unique_tradable_id = unique_tradables.id and sales_details.header_id=sales_headers.id and find_in_set('order', sales_headers.type) and sales_headers.entity_id=" . $customerId . " order by sales_details.updated_at desc limit 1), unique_tradables.sku) as display, ifnull((select sales_details.description from sales_details, sales_headers where sales_details.unit_price > 0 and sales_details.unique_tradable_id = unique_tradables.id and sales_details.header_id=sales_headers.id and find_in_set('order', sales_headers.type) and sales_headers.entity_id=" . $customerId . " order by sales_details.updated_at desc limit 1), unique_tradables.description) as description, ifnull((select sales_details.unit_price from sales_details, sales_headers where sales_details.unit_price > 0 and sales_details.unique_tradable_id = unique_tradables.id and sales_details.header_id=sales_headers.id and find_in_set('order', sales_headers.type) and sales_headers.entity_id=" . $customerId . " order by sales_details.updated_at desc limit 1), 0.000) as 'unit_price' FROM unique_tradables, unique_tradable_restrictions where current=1 and enforce=1 and unique_tradables.id = unique_tradable_restrictions.unique_tradable_id and find_in_set('include', `action`) and ((find_in_set('region', associated_attribute) and (associated_id = 0 or associated_id = (select region_id from taxable_entities where id=" . $customerId . "))) or (find_in_set('entity', associated_attribute) and (associated_id = 0 or associated_id = " . $customerId . "))) order by unique_tradables.sku asc") as $oneResult) {
			$product_option[$oneResult->id] = $oneResult->sku;
			$extended_product_option[$oneResult->id] = [
					'display' => $oneResult->display,
					'description' => $oneResult->description,
					'unit_price' => sprintf("%0." . $fmtr['fdigit'] . "f" , $oneResult->unit_price),
				];
		}

		$contact = array();
		foreach (User::getEntityContacts($customerId, 'name', 'asc') as $oneResult) {
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

		$billingAddresses = array();
		$shippingAddresses = array();
		$result = Address::where('entity_id', $customerId)->get();
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
				'customer' => $customer,
				'payment' => $payment,
				'contact' => $contact,
				'staff' => $staff,
				'currency' => $currency,
				'billing_address' => $billingAddresses,
				'shipping_address' => $shippingAddresses,
				'product_option' => $product_option,
				'extended_product_option' => $extended_product_option,
			];
	}
}

?>
