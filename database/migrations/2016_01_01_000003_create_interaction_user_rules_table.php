<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInteractionUserRulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('interaction_user_rules', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('originator_id')->unsigned();
			$table->integer('participant_id')->unsigned();
			$table->foreign('participant_id')->references('id')->on('users');
			$table->enum('role', ['participant', 'requestor', 'requestee', 'assigner', 'assignee']);
			$table->boolean('valid');
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
		Schema::dropIfExists('interaction_user_rules');
	}
}
