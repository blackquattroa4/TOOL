<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUniqueTradableRestrictionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('unique_tradable_restrictions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('unique_tradable_id')->unsigned();
			$table->foreign('unique_tradable_id')->references('id')->on('unique_tradables');
			$table->enum('action', array('include', 'exclude'));
			$table->enum('associated_attribute', array('region', 'entity'));
			$table->integer('associated_id')->unsigned();
			$table->boolean('enforce');
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
		Schema::dropIfExists('unique_tradable_restrictions');
	}
}
