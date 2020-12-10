<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseApprovalRulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_approval_rules', function (Blueprint $table) {
			$table->increments('id');
			$table->boolean('applied_to_quote');
			$table->boolean('applied_to_order');
			$table->boolean('applied_to_return');
			$table->integer('approver_id')->unsigned();
			$table->foreign('approver_id')->references('id')->on('users');
			$table->string('src_table');
			$table->integer('src_entity_id')->unsigned();
			$table->decimal('threshold', 15, 5);
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
		Schema::dropIfExists('purchase_approval_rules');
	}
}
