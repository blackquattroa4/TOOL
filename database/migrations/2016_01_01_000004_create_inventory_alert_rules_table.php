<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInventoryAlertRulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_alert_rules', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('unique_tradable_id')->unsigned();
			// we'll use 0 to indicate all location
			//$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->integer('location_id')->unsigned();
			// we'll use 0 to indicate all location
			//$table->foreign('location_id')->references('id')->on('addresses');
			$table->decimal('min', 15, 5);
			$table->decimal('max', 15, 5);
			$table->boolean('valid');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('inventory_alert_rules');
	}
}
