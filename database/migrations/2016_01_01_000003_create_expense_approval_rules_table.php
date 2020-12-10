<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateExpenseApprovalRulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expense_approval_rules', function (Blueprint $table) {
			$table->increments('id');
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
		Schema::dropIfExists('expense_approval_rules');
	}
}
