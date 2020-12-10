<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateVendorBatchHistoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_batch_histories', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('batch_id')->unsigned();
			$table->foreign('batch_id')->references('id')->on('vendor_batches');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->string('machine');
			$table->enum('process_status', array('created', 'reference-modified', 'notes-modified', 'quantity-increased', 'quantity-decreased', 'date-advanced', 'date-postponed', 'shipped'));
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
		Schema::dropIfExists('vendor_batch_histories');
	}
}
