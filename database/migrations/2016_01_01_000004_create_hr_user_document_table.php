<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateHrUserDocumentTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hr_user_document', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->integer('document_id')->unsigned();
			$table->foreign('document_id')->references('id')->on('documents');
			$table->integer('creator_id')->unsigned();
			$table->foreign('creator_id')->references('id')->on('users');
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
		Schema::dropIfExists('hr_user_document');
	}
}
