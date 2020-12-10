<?php
namespace App\Helpers;

use App\TaxableEntity;
use App\PaymentTerm;
use App\UniqueTradable;
use App\User;
use App\Currency;
use App\Address;
use App\TransactableHeader;

class TransactableView
{
	/*
	 *  when creating transactable, $id = supplier-id, $createMode = true
	 *  when updating/viewing/approving/processing transactable, $id = order-id, $createMode = false
	 */
	public static function generateOptionArrayForTemplate($id, $createMode = false)
	{
		$entityId = $createMode ? $id : TransactableHeader::find($id)->entity_id;

		$entity = array();
		foreach (TaxableEntity::getActiveEntities('code', 'asc') as $oneResult) {
			$entity[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
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
		foreach (User::getEntityContacts($entityId, 'name', 'asc') as $oneResult) {
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
		$result = Address::where('entity_id', $entityId)->get();
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
				'entity' => $entity,
				'payment' => $payment,
				'contact' => $contact,
				'staff' => $staff,
				'currency' => $currency,
				'billing_address' => $billingAddresses,
				'shipping_address' => $shippingAddresses,
				'product_option' => $product_option,
			];
	}
}

?>