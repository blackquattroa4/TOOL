<?php

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;

class CurrenciesTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('currencies')->insert([
			'symbol' => 'USD',
			'regex' => 'en_US',
			'description' => 'U.S. Dollar',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('currencies')->insert([
			'symbol' => 'CAD',
			'regex' => 'en_CA',
			'description' => 'Canadian Dollar',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('currencies')->insert([
			'symbol' => 'MXN',
			'regex' => 'es_MX',
			'description' => 'Mexican Peso',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('currencies')->insert([
			'symbol' => 'RMB',
			'regex' => 'zh_CN',
			'description' => 'China Renminbi',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('currencies')->insert([
			'symbol' => 'TWD',
			'regex' => 'zh_TW',
			'description' => 'New Taiwan Dollar',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('currencies')->insert([
			'symbol' => 'HKD',
			'regex' => 'zh_HK',
			'description' => 'Hong Kong Dollar',
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}
}
