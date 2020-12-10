<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseHistoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('warehouse_histories', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('src', array('warehouse_headers', 'warehouse_details'));
			$table->integer('src_id')->unsigned();
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->string('machine');
			$table->enum('process_status', array('created', 'updated', 'processed', 'scanned', 'closed', 'void'));
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
		Schema::dropIfExists('warehouse_histories');
	}
}
