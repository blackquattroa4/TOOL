<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBinSerialTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bin_serial', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('bin_id')->unsigned();
			$table->foreign('bin_id')->references('id')->on('warehouse_bins');
			$table->integer('tradable_id')->unsigned();
			$table->foreign('tradable_id')->references('id')->on('tradables');
			$table->string('serial');
			$table->datetime('occupied_since');
			$table->datetime('occupied_until')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bin_serial');
	}
}
