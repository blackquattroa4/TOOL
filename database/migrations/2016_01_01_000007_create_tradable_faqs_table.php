<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTradableFaqsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tradable_faqs', function (Blueprint $table) {
			$table->increments('id');
			$table->string('question');
			$table->string('answer');
			$table->integer('document_id')->unsigned();
			$table->foreign('document_id')->references('id')->on('documents');
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
		Schema::dropIfExists('tradable_faqs');
	}
}
