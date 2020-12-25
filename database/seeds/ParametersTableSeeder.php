<?php

use Illuminate\Database\Seeder;

class ParametersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('parameters')->insertGetId([
			'key' => 'sales_order_number',
			'value' => serialize('200000'),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'purchase_order_number',
			'value' => serialize('100000'),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'warehouse_order_number',
			'value' => serialize('300000'),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'expense_number',
			'value' => serialize('400000'),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'transaction_number',
			'value' => serialize('500000'),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'sales_tax_account_id',
			'value' => serialize(0),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'misc_expense_account_id',
			'value' => serialize(0),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$bankAccountId = DB::table('chart_accounts')->insertGetId([    // default bank account
			'account' => '10001-001',
			'type' => 'asset',
			'currency_id' => 1,
			'description' => 'checking account (x????)',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'bank_cash_t_account_ids',
			'value' => serialize(array($bankAccountId)),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'cost_of_good_sold_method',
			'value' => serialize("fifo"),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'credit_card_t_account_ids',
			'value' => serialize(array()),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('parameters')->insertGetId([
			'key' => 'charge_ocr',
			'value' => serialize(false),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}
}
