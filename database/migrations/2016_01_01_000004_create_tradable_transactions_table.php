<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTradableTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tradable_transactions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->integer('location_id')->unsigned();
			$table->foreign('location_id')->references('id')->on('locations');
			$table->integer('owner_entity_id')->unsigned();
			$table->foreign('owner_entity_id')->references('id')->on('taxable_entities');
			$table->decimal('quantity', 15, 5);
			$table->decimal('unit_cost', 10, 5);
			$table->string('src_table');
			$table->integer('src_id')->unsigned();
			$table->boolean('valid');
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
		Schema::dropIfExists('tradable_transactions');
	}
}
