<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCommissionProfilesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('commission_profiles', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('payable_entity_id')->unsigned();
			$table->foreign('payable_entity_id')->references('id')->on('taxable_entities');
			$table->enum('base', ['purchase-basis', 'sales-basis', 'payment-basis', 'gross-profit-basis']);
			$table->date('last_recorded_date')->nullable();
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
		Schema::dropIfExists('commission_profiles');
	}
}
