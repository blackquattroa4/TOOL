<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactableDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactable_details', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('transactable_header_id')->unsigned();
			$table->foreign('transactable_header_id')->references('id')->on('transactable_headers');
			$table->string('src_table');
			$table->integer('src_id')->unsigned();
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->string('display_as');
			$table->string('description');
			$table->decimal('unit_price', 10, 5);
			$table->decimal('discount', 5, 2);
			$table->enum('discount_type', array('percent', 'amount'));
			$table->decimal('transacted_quantity', 15, 5);
			$table->decimal('transacted_amount', 15, 5);
			$table->decimal('discount_amount', 10, 5);
			$table->decimal('tax_amount', 10, 4);
			$table->enum('status', array('valid', 'void'));
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
		Schema::dropIfExists('transactable_details');
	}
}
