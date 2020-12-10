<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateIncidencesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('incidences', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('origin')->unsigned();
			$table->integer('sequence')->unsigned();
			$table->enum('status', array('unresponded', 'responded', 'junked'));
			$table->enum('media', array('unknown', 'email', 'phone', 'messager'));
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->integer('counter_user_id')->unsigned();
			$table->foreign('counter_user_id')->references('id')->on('users');
			$table->text('exchange');
			$table->text('internal_notes');
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
		Schema::dropIfExists('incidences');
	}
}
