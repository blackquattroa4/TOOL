<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateExpenseDetailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expense_details', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('expense_header_id')->unsigned();
			$table->foreign('expense_header_id')->references('id')->on('expense_headers');
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->decimal('unit_price', 10, 5);
			$table->decimal('quantity', 15, 5);
			$table->decimal('subtotal', 15, 5);
			$table->date('incur_date');
			$table->text('notes');
			$table->integer('attachment_id')->unsigned();
			$table->foreign('attachment_id')->references('id')->on('downloadables');
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
		Schema::dropIfExists('expense_details');
	}
}
