<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCommissionSourcesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('commission_sources', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('profile_id')->unsigned();
			$table->foreign('profile_id')->references('id')->on('commission_profiles');
			$table->text('source_product_id');
			$table->text('source_entity_id');
			$table->decimal('per_piece_rate', 4, 2);
			$table->enum('per_piece_method', ['percent', 'amount']);
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
		Schema::dropIfExists('commission_sources');
	}
}
