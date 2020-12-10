<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('warehouse_details', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('header_id')->unsigned();
			$table->foreign('header_id')->references('id')->on('warehouse_headers');
			$table->enum('src_table', array('na', 'purchase_details', 'sales_details'));
			$table->integer('src_id')->unsigned();
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->decimal('expected_quantity', 15, 5);
			$table->decimal('processed_quantity', 15, 5);
			$table->string('description');
			$table->enum('status', array('open', 'void', 'closed'));
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
		Schema::dropIfExists('warehouse_details');
	}
}
