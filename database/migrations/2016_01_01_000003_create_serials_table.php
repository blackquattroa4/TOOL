<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSerialsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('serials', function (Blueprint $table) {
			$table->increments('id');
			$table->string('serial');
			$table->enum('src_table', array('na', 'warehouse_headers', 'warehouse_details'));
			$table->integer('src_id')->unsigned();
			$table->integer('tradable_id')->unsigned();
			$table->foreign('tradable_id')->references('id')->on('tradables');
			$table->integer('pallet_id')->unsigned();
			$table->integer('carton_id')->unsigned();
			$table->date('warranty_from');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('serials');
	}
}
