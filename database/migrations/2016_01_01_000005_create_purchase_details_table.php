<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_details', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('header_id')->unsigned();
			$table->foreign('header_id')->references('id')->on('purchase_headers');
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->string('manufacture_model');
			$table->string('manufacture_reference');
			$table->decimal('ordered_quantity', 15, 5);
			$table->decimal('shipped_quantity', 15, 5);
			$table->decimal('shipped_amount', 15, 5);
			$table->string('description');
			$table->decimal('unit_price', 10, 5);
			$table->decimal('inventory_cost', 10, 5);
			$table->boolean('taxable');
			$table->enum('status', array('open', 'void', 'closed'));
			$table->date('delivery_date');
			$table->integer('receiving_location_id')->unsigned();
			$table->foreign('receiving_location_id')->references('id')->on('locations');
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
		Schema::dropIfExists('purchase_details');
	}
}
