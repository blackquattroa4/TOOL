<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('calendar_events', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->string('subject');
			$table->string('description');
			$table->date('from_date');
			$table->date('to_date');
			$table->enum('repeat_type', array('never','daily','weekly','biweekly','monthly','bimonthly','quarterly','semiannually','annually'));
			$table->time('from_time');
			$table->time('to_time');
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
		Schema::dropIfExists('calendar_events');
	}
}
