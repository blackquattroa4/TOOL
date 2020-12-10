<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateConsignmentReceivableRelationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('consignment_receivable_relations', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('transactable_header_id')->unsigned();
			$table->foreign('transactable_header_id')->references('id')->on('transactable_headers');
			$table->text('meta');
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
		Schema::dropIfExists('consignment_receivable_relations');
	}
}
