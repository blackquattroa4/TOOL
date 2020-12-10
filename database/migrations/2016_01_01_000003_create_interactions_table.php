<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInteractionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('interactions', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('type', array('request', 'assignment'));
			$table->text('description');
			$table->enum('status', ['requested', 'evaluating', 'in-progress', 'closed' ]);
			$table->string('requestor_machine');
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
		Schema::dropIfExists('interactions');
	}
}
