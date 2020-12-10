<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('warehouse_headers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->enum('type', array('receive', 'relocate', 'transfer', 'deliver'));
			$table->boolean('palletized');
			$table->integer('internal_contact_id')->unsigned();
			$table->foreign('internal_contact_id')->references('id')->on('users');
			$table->integer('shipping_location_id')->unsigned();
			$table->foreign('shipping_location_id')->references('id')->on('locations');
			$table->string('reference');
			$table->integer('external_entity_id')->unsigned();
			$table->foreign('external_entity_id')->references('id')->on('taxable_entities');
			$table->integer('external_address_id')->unsigned();
			$table->foreign('external_address_id')->references('id')->on('addresses');
			$table->enum('status', array('open', 'void', 'closed'));
			$table->string('via');
			$table->date('order_date');
			$table->enum('src', array('na', 'purchase_headers', 'sales_headers'));
			$table->integer('src_id')->unsigned();
			$table->text('notes');
			$table->text('internal_notes');
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
		Schema::dropIfExists('warehouse_headers');
	}
}
