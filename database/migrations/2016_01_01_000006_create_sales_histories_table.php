<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSalesHistoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sales_histories', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('src', array('sales_headers', 'sales_details'));
			$table->integer('src_id')->unsigned();
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->string('machine');
			$table->enum('process_status', array('created', 'updated', 'approved', 'approval expired', 'rejected', 'disapproval expired', 'prepared', 'partially shipped', 'shipped', 'partially returned', 'returned', 'closed'));
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
		Schema::dropIfExists('sales_histories');
	}
}
