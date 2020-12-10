<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTaxableEntitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taxable_entities', function (Blueprint $table) {
			$table->increments('id');
			$table->string('code')->unique();
			$table->string('name');
			$table->enum('type', array('unknown', 'self', 'individual', 'employee', 'supplier', 'customer', 'bank', 'creditcard'));
			$table->integer('region_id')->unsigned();
			$table->string('tax_id')->unique();
			$table->integer('payment_term_id')->unsigned();
			$table->foreign('payment_term_id')->references('id')->on('payment_terms');
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
			$table->integer('transaction_t_account_id')->unsigned();
			$table->foreign('transaction_t_account_id')->references('id')->on('chart_accounts');
			$table->integer('revenue_t_account_id')->unsigned();
			$table->foreign('revenue_t_account_id')->references('id')->on('chart_accounts');
			$table->boolean('active');
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
		Schema::dropIfExists('taxable_entities');
	}
}
