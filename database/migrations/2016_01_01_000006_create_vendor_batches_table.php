<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateVendorBatchesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_batches', function (Blueprint $table) {
			$table->increments('id');
			$table->string('reference');
			$table->decimal('quantity', 15, 5);
			$table->date('ready_date');
			$table->string('shipment_reference')->nullable();
			$table->integer('purchase_detail_id')->unsigned();
			$table->foreign('purchase_detail_id')->references('id')->on('purchase_details');
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
		Schema::dropIfExists('vendor_batches');
	}
}
