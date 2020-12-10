<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLoanHistoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loan_histories', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('src', array('loan_headers'));
			$table->integer('src_id')->unsigned();
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->string('machine');
			$table->enum('process_status', array('created', 'debited', 'credited', 'closed'));
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
		Schema::dropIfExists('loan_histories');
	}
}
