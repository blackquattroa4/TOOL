<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSalesDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sales_details', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('header_id')->unsigned();
			$table->foreign('header_id')->references('id')->on('sales_headers');
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->string('display_as');
			$table->string('description');
			$table->decimal('unit_price', 10, 5);
			$table->decimal('ordered_quantity', 15, 5);
			$table->decimal('allocated_quantity', 15, 5)->defualt(0);
			$table->decimal('shipped_quantity', 15, 5);
			$table->decimal('shipped_amount', 15, 5);
			$table->decimal('discount', 5, 2)->default(0);
			$table->enum('discount_type', array('percent', 'amount'));
			$table->boolean('taxable');
			$table->enum('status', array('open', 'void', 'closed'));
			$table->date('delivery');
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
		Schema::dropIfExists('sales_details');
	}
}
