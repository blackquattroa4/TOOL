<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUniqueTradablesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('unique_tradables', function (Blueprint $table) {
			$table->increments('id');
			$table->string('sku');
			$table->string('description');
			$table->string('product_id');
			$table->boolean('current');
			$table->boolean('phasing_out');
			$table->boolean('stockable');
			$table->boolean('expendable');
			$table->boolean('forecastable');
			$table->integer('replacing_unique_tradable_id');
			$table->integer('replaced_by_unique_tradable_id');
			$table->integer('expense_t_account_id')->unsigned();
			$table->foreign('expense_t_account_id')->references('id')->on('chart_accounts');
			$table->integer('cogs_t_account_id')->unsigned();
			$table->foreign('cogs_t_account_id')->references('id')->on('chart_accounts');
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
		Schema::dropIfExists('unique_tradables');
	}
}
