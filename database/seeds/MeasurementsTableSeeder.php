<?php

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;

class MeasurementsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('measurements')->insert([
			'symbol' => 'in', 
			'type' => 'length', 
			'description' => 'length in inch', 
			'conversion_ratio' => 1.000000, 
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('measurements')->insert([
			'symbol' => 'cm', 
			'type' => 'length', 
			'description' => 'length in centi-meter', 
			'conversion_ratio' => 0.393701, 
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('measurements')->insert([
			'symbol' => 'lb', 
			'type' => 'weight', 
			'description' => 'weight in pound', 
			'conversion_ratio' => 1.000000, 
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		DB::table('measurements')->insert([
			'symbol' => 'kg', 
			'type' => 'weight', 
			'description' => 'weight in kilogram', 
			'conversion_ratio' => 2.204620, 
			'active' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}
}
