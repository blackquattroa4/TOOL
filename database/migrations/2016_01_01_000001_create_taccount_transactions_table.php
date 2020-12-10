<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTaccountTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taccount_transactions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('debit_t_account_id')->unsigned();
			$table->foreign('debit_t_account_id')->references('id')->on('chart_accounts');
			$table->integer('credit_t_account_id')->unsigned();
			$table->foreign('credit_t_account_id')->references('id')->on('chart_accounts');
			$table->decimal('amount', 15, 3);
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
			$table->date('book_date');
			$table->string('src');
			$table->integer('src_id')->unsigned();
			$table->boolean('valid');
			$table->boolean('reconciled');
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
		Schema::dropIfExists('taccount_transactions');
	}
}
