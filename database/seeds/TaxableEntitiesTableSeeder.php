<?php

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;

use App\ChartAccount;

class TaxableEntitiesTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$pseudoId = DB::table('chart_accounts')->insertGetId([  // pseudo-account; significance of this account is context dependent
			'account' => '00000',
			'type' => 'unknown',
			'currency_id' => 1,
			'description' => 'pseudo T account created for linking',
			'active' => 0,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		//$pseudoAccount = ChartAccount::find($pseudoId);
		//$pseudoAccount->id = 0;
		//$pseudoAccount->save();
		$salesTaxLiabilityId = DB::table('chart_accounts')->insertGetId([  // sales-tax account
			'account' => '20000',
			'type' => 'liability',
			'currency_id' => 1,
			'description' => 'sales-tax liability',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->where('key', 'sales_tax_account_id')->update(array('value' => serialize($salesTaxLiabilityId)));
		$miscExpenseId = DB::table('chart_accounts')->insertGetId([  // miscellaneous-expense account
			'account' => '79010',
			'type' => 'expense',
			'currency_id' => 1,
			'description' => 'Miscellaneous expense',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->where('key', 'misc_expense_account_id')->update(array('value' => serialize($miscExpenseId)));
		$transactionAccountId = DB::table('chart_accounts')->insertGetId([   // Equity account
			'account' => '30000',
			'type' => 'equity',
			'currency_id' => 1,
			'description' => 'Shareholder equity',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$revenueAccountId = DB::table('chart_accounts')->insertGetId([	// retained-earning
			'account' => '30001',
			'type' => 'equity',
			'currency_id' => 1,
			'description' => 'Retained earning',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$entityId = DB::table('taxable_entities')->insertGetId([
			'code' => 'CODE',
			'name' => 'name of the company',
			'type' => 'self',
			'region_id' => 0,
			'tax_id' => 'tax id of the company',
			'payment_term_id' => 1,
			'currency_id' => 1,
			'transaction_t_account_id' => $transactionAccountId,
			'revenue_t_account_id' => $revenueAccountId,
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$userId = DB::table('users')->insertGetId([
			'name' => 'System user',
			'email' => 'noone@nowhere.com',
			'password' => '',
			'phone' => '',
			'entity_id' => $entityId,
			'landing_page' => '',
			'active' => 0,
			'failure_count' => 0,
			'imap_endpoint' => '',
			'smtp_endpoint' => '',
			'email_password' => '',
			'last_failure' => date('Y-m-d H:i:s'),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$billingAddressId = DB::table('addresses')->insertGetId([
			'entity_id' => $entityId,
			'purpose' => 'billing',
			'is_default' => 1,
			'name' => '',
			'unit' => '',
			'street' => 'street number',
			'district' => '',
			'city' => 'city',
			'state' => '',
			'country' => 'US',
			'zipcode' => '',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$shippingAddressId = DB::table('addresses')->insertGetId([
			'entity_id' => $entityId,
			'purpose' => 'shipping',
			'is_default' => 1,
			'name' => '',
			'unit' => '',
			'street' => 'street number',
			'district' => '',
			'city' => 'city',
			'state' => '',
			'country' => 'US',
			'zipcode' => '',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$inventoryAccountId = DB::table('chart_accounts')->insertGetId([   // inventory
			'account' => '10003-XXX',
			'type' => 'asset',
			'currency_id' => 1,
			'description' => 'inventory of main warehouse',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$locationId = DB::table('locations')->insert([
			'name' => 'main warehouse',
			'type' => 'warehouse',
			'owner_entity_id' => $entityId,
			'address_id' => $shippingAddressId,
			'contact_id' => $userId,
			'inventory_t_account_id' => $inventoryAccountId,
			'active' => 1,
			'notes' => '',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		// location should have a default Bin
		DB::table('warehouse_bins')->insert([
			'location_id' => $locationId,
			'name' => 'default bin of main warehouse',
			'valid' => 1,
			'created_at' => date("Y-m-d H:i:s"),
			'updated_at' => date("Y-m-d H:i:s"),
		]);
		DB::table('chart_accounts')->where('id', $inventoryAccountId)->update(array('account' => '100003-' . sprintf("%03d", $inventoryAccountId)));
	}
}
