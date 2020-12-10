<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRecurringExpensesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recurring_expenses', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->integer('quantity')->unsigned();
			$table->text('notes');
			$table->integer('frequency_numeral')->unsigned();
			$table->enum('frequency_unit', array('days', 'weeks', 'months', 'years'));
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
		Schema::dropIfExists('recurring_expenses');
	}
}
