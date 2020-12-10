<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class TaxableEntity extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code', 'name', 'type', 'region_id', 'tax_id', 'payment_term_id', 'currency_id', 'transaction_t_account_id', 'revenue_t_account_id', 'active',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function theCompany()
	{
		return TaxableEntity::where('type', 'self')->first();
	}

	public static function getActiveCustomers($order, $direction)
	{
		return TaxableEntity::where('type', 'customer')->where('active', 1)->orderBy($order, $direction)->get();
	}

	public static function getActiveSuppliers($order, $direction)
	{
		return TaxableEntity::where('type', 'supplier')->where('active', 1)->orderBy($order, $direction)->get();
	}

	public static function getCustomers($order, $direction)
	{
		return TaxableEntity::where('type', 'customer')->orderBy($order, $direction)->get();
	}

	public static function getSuppliers($order, $direction)
	{
		return TaxableEntity::where('type', 'supplier')->orderBy($order, $direction)->get();
	}

	public static function getSuppliersWithProduct($order, $direction)
	{
		$suppliers = Tradable::select('supplier_entity_id')->distinct()->pluck('supplier_entity_id')->toArray();
		return TaxableEntity::where('type', 'supplier')->whereIn('id', $suppliers)->orderBy($order, $direction)->get();
	}

	public static function getCustomersWithProduct($order, $direction)
	{
		// select customer that we can sell at least one product to
		// go through all possible combination of unique_tradables & taxable_entities (derived-table y1),
		// and then use unique_tradable_restrictions to filter out 'exclude' (derived-table exclusion)
		$entityIds = array_column(\DB::select("
		SELECT DISTINCT
		    z1.taxable_entity_id
		FROM
		    (SELECT
		        y1.unique_tradable_id, y1.taxable_entity_id
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
		    LEFT OUTER JOIN (SELECT
		        unique_tradable_id, t1.id AS taxable_entity_id
		    FROM
		        unique_tradable_restrictions
		    CROSS JOIN taxable_entities t1
		    WHERE
		        `action` = 'exclude'
		            AND associated_attribute = 'entity'
		            AND associated_id = 0
								AND enforce = 1 UNION SELECT
		        unique_tradable_id, associated_id AS taxable_entity_id
		    FROM
		        unique_tradable_restrictions
		    WHERE
		        `action` = 'exclude'
		            AND associated_attribute = 'entity'
		            AND associated_id <> 0
								AND enforce = 1) exclusion ON y1.taxable_entity_id = exclusion.taxable_entity_id
		        AND y1.unique_tradable_id = exclusion.unique_tradable_id
		    WHERE
		        exclusion.unique_tradable_id IS NULL
		            AND exclusion.taxable_entity_id IS NULL) z1"), 'taxable_entity_id');

		return TaxableEntity::whereIn('type', ['supplier', 'customer', 'employee'])->whereIn('id', $entityIds)->orderBy($order, $direction)->get();
	}

	public static function getNonSuppliers($order, $direction)
	{
		return TaxableEntity::where('type', '!=', 'supplier')->orderBy($order, $direction)->get();
	}

	public static function getReceivableEntities($order, $direction)
	{
		return TaxableEntity::where('type', '<>', 'self')->orderBy($order, $direction)->get();
	}

	public static function getPayableEntities($order, $direction)
	{
		return TaxableEntity::whereIn('type', ['employee', 'supplier'])->orderBy($order, $direction)->get();
	}

	public static function getActiveEntities($order, $direction)
	{
		return TaxableEntity::where('type', '<>', 'self')->where('active', 1)->orderBy($order, $direction)->get();
	}

	public static function getExternalEntities($order, $direction)
	{
		return TaxableEntity::whereNotIn('type', ['self','employee'])->where('active', 1)->orderBy($order, $direction)->get();
	}

	public static function getJsonShippingAddressesIndexedByEntity()
	{
		$ids = TaxableEntity::whereNotIn('type', ['self','employee'])->where('active', 1)->pluck('id')->toArray();

		$result = array_column(DB::select("
			SELECT
				t1.entity_id, concat('[',group_concat(t1.binding),']') AS binding
			FROM
				(SELECT entity_id,
					concat('{\"id\":',id,',\"default\":',is_default,',\"name\":\"',`name`,'\",\"unit\":\"',unit,'\",\"street\":\"',street,'\",\"district\":\"',district,'\",\"city\":\"',city,'\",\"state\":\"',state,'\",\"country\":\"',country,'\",\"zipcode\":\"',zipcode,'\"}') AS binding
				FROM addresses
				WHERE purpose = 'shipping') t1
			WHERE t1.entity_id in (" . implode(",", $ids) . ")
			GROUP BY entity_id
		"), "binding", "entity_id");

		array_walk($result, function(&$element, $key) {
			$element = is_null($element) ? null : json_decode($element, true);
		});

		return $result;
	}

	public static function getSupplyChainEntities($order, $direction)
	{
		return TaxableEntity::whereIn('type', ['supplier','customer'])->where('active', 1)->orderBy($order, $direction)->get();
	}

	public static function getNonSupplyChainEntities($order, $direction)
	{
		return TaxableEntity::whereNotIn('type', ['self','supplier','customer'])->where('active', 1)->orderBy($order, $direction)->get();
	}

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
	}

	public function isSupplier()
	{
		return ($this->type=='supplier');
	}

	public function isActiveSupplier()
	{
		return ($this->active) && ($this->type=='supplier');
	}

	public function isNotActiveSupplier()
	{
		return (!$this->active) || ($this->type!='supplier');
	}

	public function isCustomer()
	{
		return ($this->type=='customer');
	}

	public function isActiveCustomer()
	{
		return ($this->active) && ($this->type=='customer');
	}

	public function isEmployee()
	{
		return ($this->type=='employee');
	}

	public function isActiveEmployee()
	{
		return ($this->active) && ($this->type=='employee');
	}

	public function isNotActiveCustomer()
	{
		return (!$this->active) || ($this->type!='customer');
	}

	public function isActiveEntity()
	{
		return ($this->active) && ($this->type != 'self');
	}

	public function isNotActiveEntity()
	{
		return (!$this->active) && ($this->type != 'self');
	}

	public function contact()
	{
		return $this->hasMany('App\User', 'entity_id');
	}

	public function address()
	{
		return $this->hasMany('App\Address', 'entity_id');
	}

	public function billingAddress()
	{
		return $this->hasMany('App\Address', 'entity_id')->where('purpose', 'billing');
	}

	public function defaultBillingAddress()
	{
		return $this->hasMany('App\Address', 'entity_id')->where([['purpose', '=', 'billing'], ['is_default', '=', 1]]);
	}

	public function shippingAddress()
	{
		return $this->hasMany('App\Address', 'entity_id')->where('purpose', 'shipping');
	}

	public function defaultShippingAddress()
	{
		return $this->hasMany('App\Address', 'entity_id')->where([['purpose', '=', 'shipping'], ['is_default', '=', 1]]);
	}

	public function revenueChartAccount()
	{
		return $this->hasOne('App\ChartAccount', 'id', 'revenue_t_account_id');
	}

	public function transactionChartAccount()
	{
		return $this->hasOne('App\ChartAccount', 'id', 'transaction_t_account_id');
	}

	public function hasConsignmentInventory()
	{
		return count(DB::select("select 1 from tradable_transactions where owner_entity_id = " . $this->id . " and valid = 1")) > 0;
	}

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];

		array_push($result, $this->type);
		array_push($result, $this->active ? 'active' : 'passive');
		array_push($result, $this->tax_id);

		return $result;
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
		return implode($delimiter, [
				str_pad('supplier', 15) . trans('tool.Search supplier entity'),
				str_pad('employee', 15) . trans('tool.Search employee entity'),
				str_pad('individual', 15) . trans('tool.Search individual entity'),
				str_pad('bank', 15) . trans('tool.Search bank entity'),
				str_pad('creditcard', 15) . trans('tool.Search creditcard entity'),
				str_pad('customer', 15) . trans('tool.Search customer entity'),
				str_pad('active', 15) . trans('tool.Search active entity'),
				str_pad('passive', 15) . trans('tool.Search passive entity'),
			]);
	}

	public static function initialize($inputData) {
		// add to database
		$revenueAccount = ChartAccount::create([
			'account' => '30000-XXXX',
			'type' => 'revenue',
			'currency_id' => $inputData['currency'],
			'description' => 'revenue account of ' . $inputData['code'],
			'active' => in_array($inputData['active'], [1, "1", true, "true"], true),
		]);
		$transactionAccount = ChartAccount::create([
			'account' => '21000-XXXX',
			'type' => ($inputData['type'] == 'customer') ? 'asset' : 'liability',
			'currency_id' => $inputData['currency'],
			'description' => 'transactable account of ' . $inputData['code'],
			'active' => in_array($inputData['active'], [1, "1", true, "true"], true),
		]);
		$taxableEntity = TaxableEntity::create([
			'code' => $inputData['code'],
			'name' => $inputData['name'],
			'type' => $inputData['type'],
			'tax_id' => '',
			'region_id' => 0,
			'payment_term_id' => $inputData['payment'],
			'currency_id' => $inputData['currency'],
			'transaction_t_account_id' => $transactionAccount->id,
			'revenue_t_account_id' => $revenueAccount->id,
			'active' => in_array($inputData['active'], [1, "1", true, "true"], true),
		]);
		$user = User::create([
			'name' => $inputData['contact'],
			'email' => $inputData['email'],
			'password' => bcrypt(time()),
			'phone' => $inputData['phone'],
			'entity_id' => $taxableEntity->id,
			'landing_page' => '',
			'active' => in_array($inputData['type'], ['employee']),
			'permission' => '',
			'failure_count' => 0,
			'imap_endpoint' => '',
			'smtp_endpoint' => '',
			'email_password' => '',
			'last_failure' => date('Y-m-d H:i:s'),
		]);
		$billingAddress = Address::create([
			'entity_id' => $taxableEntity->id,
			'purpose' => 'billing',
			'is_default' => 1,
			'name' => $inputData['contact'],
			'unit' => empty($inputData['bunit']) ? '' : $inputData['bunit'],
			'street' => $inputData['bstreet'],
			'district' => empty($inputData['bdistrict']) ? '' : $inputData['bdistrict'],
			'city' => $inputData['bcity'],
			'state' => $inputData['bstate'],
			'country' => $inputData['bcountry'],
			'zipcode' => $inputData['bzipcode'],
		]);
		$shippingAddress = Address::create([
			'entity_id' => $taxableEntity->id,
			'purpose' => 'shipping',
			'is_default' => 1,
			'name' => $inputData['contact'],
			'unit' => empty($inputData['sunit']) ? '' : $inputData['sunit'],
			'street' => $inputData['sstreet'],
			'district' => empty($inputData['sdistrict']) ? '' : $inputData['sdistrict'],
			'city' => $inputData['scity'],
			'state' => $inputData['sstate'],
			'country' => $inputData['scountry'],
			'zipcode' => $inputData['szipcode'],
		]);
		$revenueAccount->update([
			'account' => '30000-' . sprintf('%04u' , $taxableEntity->id),
		]);
		$transactionAccount->update([
			'account' => '21000-' . sprintf('%04u', $taxableEntity->id),
		]);
		$taxableEntity->update([
			'tax_id' => sprintf('%010u', $taxableEntity->id),
		]);

		event(new \App\Events\AccountUpsertEvent($revenueAccount));
		event(new \App\Events\AccountUpsertEvent($transactionAccount));
		event(new \App\Events\EntityUpsertEvent($taxableEntity));

		return $taxableEntity;
	}

	public function synchronize($inputData) {
		// edit database
		$this->update([
			//'code' => $inputData['code'],
			'name' => $inputData['name'],
			'active' => in_array($inputData['active'], [1, "1", true, "true"], true),
			'payment_term_id' => $inputData['payment'],
			'currency_id' => $inputData['currency'],
		]);

		$this->revenueChartAccount->update([
			'active' => in_array($inputData['active'], [1, "1", true, "true"], true),
		]);

		$this->transactionChartAccount->update([
			'active' => in_array($inputData['active'], [1, "1", true, "true"], true),
		]);

		$user = User::where('entity_id', $this->id)->orderBy('id', 'desc')->first();
		if ($user->name == $inputData['contact']) {
			$user->update([
				'email' => $inputData['email'],
				'phone' => $inputData['phone'],
			]);
		} else {
			$user = User::create([
				'name' => $inputData['contact'],
				'email' => $inputData['email'],
				'password' => bcrypt(time()),
				'phone' => $inputData['phone'],
				'entity_id' => $this->id,
				'landing_page' => '',
				'active' => 0,
				'permission' => '',
				'failure_count' => 0,
				'imap_endpoint' => '',
				'smtp_endpoint' => '',
				'email_password' => '',
				'last_failure' => date('Y-m-d H:i:s'),
			]);
		}

		$billingAddress = $this->defaultBillingAddress[0];
		if (($billingAddress->name != $inputData['contact']) ||
			($billingAddress->unit != (empty($inputData['bunit']) ? '' : $inputData['bunit'])) ||
			($billingAddress->street != $inputData['bstreet']) ||
			($billingAddress->district != (empty($inputData['bdistrict']) ? '' : $inputData['bdistrict'])) ||
			($billingAddress->city != $inputData['bcity']) ||
			($billingAddress->state != $inputData['bstate']) ||
			($billingAddress->country != $inputData['bcountry']) ||
			($billingAddress->zipcode != $inputData['bzipcode'])) {
			// if any field is changed, create a new record
			$billingAddress->update([
				'is_default' => 0,
			]);
			$billingAddress = Address::create([
				'entity_id' => $this->id,
				'purpose' => 'billing',
				'is_default' => 1,
				'name' => $inputData['contact'],
				'unit' => empty($inputData['bunit']) ? '' : $inputData['bunit'],
				'street' => $inputData['bstreet'],
				'district' => empty($inputData['bdistrict']) ? '' : $inputData['bdistrict'],
				'city' => $inputData['bcity'],
				'state' => $inputData['bstate'],
				'country' => $inputData['bcountry'],
				'zipcode' => $inputData['bzipcode'],
			]);
		}

		$shippingAddress = $this->defaultShippingAddress[0];
		if (($shippingAddress->name != $inputData['contact']) ||
			($shippingAddress->unit != (empty($inputData['sunit']) ? '' : $inputData['sunit'])) ||
			($shippingAddress->street != $inputData['sstreet']) ||
			($shippingAddress->district != (empty($inputData['sdistrict']) ? '' : $inputData['sdistrict'])) ||
			($shippingAddress->city != $inputData['scity']) ||
			($shippingAddress->state != $inputData['sstate']) ||
			($shippingAddress->country != $inputData['scountry']) ||
			($shippingAddress->zipcode != $inputData['szipcode'])) {
			// if any field is changed, create a new record
			$shippingAddress->update([
				'is_default' => 0,
			]);
			$shippingAddress = Address::create([
				'entity_id' => $this->id,
				'purpose' => 'shipping',
				'is_default' => 1,
				'name' => $inputData['contact'],
				'unit' => empty($inputData['sunit']) ? '' : $inputData['sunit'],
				'street' => $inputData['sstreet'],
				'district' => empty($inputData['sdistrict']) ? '' : $inputData['sdistrict'],
				'city' => $inputData['scity'],
				'state' => $inputData['sstate'],
				'country' => $inputData['scountry'],
				'zipcode' => $inputData['szipcode'],
			]);
		}

		event(new \App\Events\EntityUpsertEvent($this));

		return $this;
	}

	public function outstandingOrder() {
		if ($this->type == 'supplier') {
			return $this->hasMany('\App\PurchaseHeader', 'entity_id')->withoutGlobalScope('currentFiscal')->whereIn('type', ['order', 'return'])->where('status', 'open');
		}
		return $this->hasMany('\App\SalesHeader', 'entity_id')->withoutGlobalScope('currentFiscal')->whereIn('type', ['order', 'return'])->where('status', 'open');
	}

	public function outstandingTransactable() {
		return $this->hasMany('\App\TransactableHeader', 'entity_id')->withoutGlobalScope('currentFiscal')->where('status', 'open');
	}

}
