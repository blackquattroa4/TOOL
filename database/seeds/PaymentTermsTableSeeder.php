<?php

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;

class PaymentTermsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('payment_terms')->insert([
			'symbol' => 'Advance',
			'description' => 'Advance payment',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 0,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('payment_terms')->insert([
			'symbol' => 'Net 15',
			'description' => 'net 15 days',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 15,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('payment_terms')->insert([
			'symbol' => 'Net 30',
			'description' => 'net 30 days',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 30,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('payment_terms')->insert([
			'symbol' => 'Net 45',
			'description' => 'net 45 days',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 45,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('payment_terms')->insert([
			'symbol' => 'Net 60',
			'description' => 'net 60 days',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 60,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('payment_terms')->insert([
			'symbol' => 'Net 75',
			'description' => 'net 75 days',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 75,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('payment_terms')->insert([
			'symbol' => 'Net 90',
			'description' => 'net 90 days',
			'active' => 1,
			'cutoff_date' => 'last',
			'grace_days' => 90,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}
}
