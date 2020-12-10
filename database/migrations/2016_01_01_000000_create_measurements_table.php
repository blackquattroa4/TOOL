<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateMeasurementsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('measurements', function (Blueprint $table) {
			$table->increments('id');
			$table->string('symbol')->unique();
			$table->enum('type', array('weight', 'length', 'volume', 'area', 'time'));
			$table->text('description');
			$table->decimal('conversion_ratio', 15, 5);
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
		Schema::dropIfExists('measurements');
	}
}
