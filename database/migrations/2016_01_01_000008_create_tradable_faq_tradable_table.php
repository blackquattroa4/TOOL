<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTradableFaqTradableTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tradable_faq_tradable', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('faq_id')->unsigned();
			$table->foreign('faq_id')->references('id')->on('tradable_faqs');
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
		Schema::dropIfExists('tradable_faq_tradable');
	}
}
