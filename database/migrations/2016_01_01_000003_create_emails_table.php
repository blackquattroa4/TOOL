<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEmailsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('emails', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->datetime('sent_at');
			$table->enum('folder', array('inbox', 'sent'));
			$table->text('from');
			$table->text('subject');
			$table->string('uid', 150)->index();
			$table->boolean('recent');
			$table->boolean('seen');
			$table->boolean('flagged');
			$table->boolean('answered');
			$table->boolean('deleted');
			$table->boolean('draft');
			$table->timestamps();

			$table->unique(['user_id', 'uid']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('emails');
	}
}
