<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locations', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->enum('type', array('unknown', 'factory', 'warehouse', 'rma'));
			$table->integer('owner_entity_id')->unsigned();
			$table->foreign('owner_entity_id')->references('id')->on('taxable_entities');
			$table->integer('address_id')->unsigned();
			$table->foreign('address_id')->references('id')->on('addresses');
			$table->integer('contact_id')->unsigned();
			$table->foreign('contact_id')->references('id')->on('users');
			$table->integer('inventory_t_account_id')->unsigned();
			$table->foreign('inventory_t_account_id')->references('id')->on('chart_accounts');
			$table->boolean('active');
			$table->text('notes');
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
		Schema::dropIfExists('locations');
	}
}
