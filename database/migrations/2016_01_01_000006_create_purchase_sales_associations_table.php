<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseSalesAssociationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_sales_associations', function (Blueprint $table) {
			$table->integer('purchase_detail_id')->unsigned();
			$table->foreign('purchase_detail_id')->references('id')->on('purchase_details');
			$table->integer('sales_detail_id')->unsigned();
			$table->foreign('sales_detail_id')->references('id')->on('sales_details');
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
		Schema::dropIfExists('purchase_sales_associations');
	}
}
