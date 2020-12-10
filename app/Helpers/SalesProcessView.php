<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\PaymentTerm;
use App\UniqueTradable;
use App\User;
use App\Currency;
use App\SalesHeader;

class SalesProcessView
{
	/*
	 *  when creating order, $id = customer-id, $createMode = true
	 *  when updating/viewing/approving/processing order, $id = order-id, $createMode = false
	 */
	public static function generateOptionArrayForTemplate($id, $createMode = false)
	{
		$customerId = $createMode ? $id : SalesHeader::find($id)->entity_id;

		$customer = array();
		foreach (TaxableEntity::getActiveCustomers('code', 'asc') as $oneResult) {
			$customer[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
		}

		$payment = array();
		foreach (PaymentTerm::getActivePaymentTerms('symbol', 'asc') as $oneResult) {
			$payment[$oneResult->id] = $oneResult->symbol . "&emsp;" . $oneResult->description;
		}

		$product_option = array();
		foreach (UniqueTradable::getActiveOnes('sku', 'asc') as $oneResult) {
			$product_option[$oneResult->id] = $oneResult->sku;
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

		return [
				'customer' => $customer,
				'payment' => $payment,
				'contact' => $contact,
				'staff' => $staff,
				'currency' => $currency,
				'product_option' => $product_option,
			];
	}
}

?>
