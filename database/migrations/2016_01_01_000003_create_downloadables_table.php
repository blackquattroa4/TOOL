<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDownloadablesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('downloadables', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('uploader_id')->unsigned();
			$table->foreign('uploader_id')->references('id')->on('users');
			$table->string('title');
			$table->string('description');
			$table->string('original_name');
			$table->integer('file_size')->unsigned();
			$table->string('mime_type');
			$table->string('hash');
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
		Schema::dropIfExists('downloadables');
	}
}
