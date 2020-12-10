<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDocumentKeywordTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document_keyword', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('document_id')->unsigned();
			$table->foreign('document_id')->references('id')->on('documents');
			$table->integer('keyword_id')->unsigned();
			$table->foreign('keyword_id')->references('id')->on('searchable_keywords');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('document_keyword');
	}
}
