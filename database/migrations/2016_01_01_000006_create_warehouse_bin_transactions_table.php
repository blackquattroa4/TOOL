<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseBinTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('warehouse_bin_transactions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('warehouse_detail_id')->unsigned();
			$table->foreign('warehouse_detail_id')->references('id')->on('warehouse_details');
			$table->integer('bin_id')->unsigned();
			$table->foreign('bin_id')->references('id')->on('warehouse_bins');
			$table->integer('tradable_id')->unsigned();
			$table->foreign('tradable_id')->references('id')->on('tradables');
			$table->decimal('quantity', 15, 5);
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
		Schema::dropIfExists('warehouse_bin_transactions');
	}
}
