<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateExpenseHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expense_headers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->date('booking_date');
			$table->string('reference');
			$table->enum('status', array('un-submitted', 'under review', 'approved', 'cancelled', 'rejected', 'void', 'paid'));
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
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
		Schema::dropIfExists('expense_headers');
	}
}
