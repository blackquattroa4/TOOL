<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLoanHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loan_headers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->enum('role', array('lender', 'borrower'));
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->decimal('principal', 15, 5);
			$table->decimal('annual_percent_rate', 5, 2);
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
			$table->string('notes');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
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
		Schema::dropIfExists('loan_headers');
	}
}
