<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTradableNoticeTradableTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tradable_notice_tradable', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('notice_id')->unsigned();
			$table->foreign('notice_id')->references('id')->on('tradable_notices');
			$table->integer('tradable_id')->unsigned();
			$table->foreign('tradable_id')->references('id')->on('tradables');
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
		Schema::dropIfExists('tradable_notice_tradable');
	}
}
