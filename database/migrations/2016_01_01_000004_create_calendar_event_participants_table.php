<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventParticipantsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('calendar_event_participants', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('calendar_event_id')->unsigned();
			$table->foreign('calendar_event_id')->references('id')->on('calendar_events');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->enum('action', array('invited', 'accepted', 'declined'));
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
		Schema::dropIfExists('calendar_event_participants');
	}
}
