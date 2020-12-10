<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactableHistoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactable_histories', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('src', array('transactable_headers', 'transactable_details'));
			$table->integer('src_id')->unsigned();
			$table->decimal('amount', 15, 5);
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->string('machine');
			$table->enum('process_status', array('created', 'debited', 'credited', 'closed', 'voided'));
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
		Schema::dropIfExists('transactable_histories');
	}
}
