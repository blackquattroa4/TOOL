<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->integer('original_version')->unsigned();
			$table->integer('version')->unsigned();
			$table->integer('creator_id')->unsigned();
			$table->foreign('creator_id')->references('id')->on('users');
			$table->string('file_path');
			$table->integer('file_size')->unsigned();
			$table->string('file_type');
			$table->string('file_name');
			$table->boolean('valid');
			$table->text('notes');
			$table->boolean('ocr_scanned');
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
		Schema::dropIfExists('documents');
	}
}
