<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInteractionLogsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('interaction_logs', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('interaction_id')->unsigned();
			$table->foreign('interaction_id')->references('id')->on('interactions');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->text('log')->nullable();
			$table->integer('downloadable_id')->unsigned()->nullable();
			$table->foreign('downloadable_id')->references('id')->on('downloadables');
			$table->timestamp('created_at')->useCurrent();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('interaction_logs');
	}
}
