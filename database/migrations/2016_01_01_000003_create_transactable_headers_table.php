<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactableHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactable_headers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('src_table');
			$table->integer('src_id')->unsigned();
			// laravel does not support set!  We'll modify this later with ALTER
			$table->enum('flags', array('printed', 'email_when_created', 'show_bank_account', 'show_discount'));
			$table->string('reference');
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->integer('contact_id')->unsigned();
			$table->foreign('contact_id')->references('id')->on('users');
			$table->integer('staff_id')->unsigned();
			$table->foreign('staff_id')->references('id')->on('users');
			$table->enum('status', array('open', 'closed', 'void'));
			$table->decimal('balance', 15, 5);
			$table->integer('billing_address_id')->unsigned();
			$table->foreign('billing_address_id')->references('id')->on('addresses');
			$table->integer('shipping_address_id')->unsigned();
			$table->foreign('shipping_address_id')->references('id')->on('addresses');
			$table->integer('payment_term_id')->unsigned();
			$table->foreign('payment_term_id')->references('id')->on('payment_terms');
			$table->date('incur_date');
			$table->date('approx_due_date');
			$table->decimal('tax_rate', 5, 2);
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
			$table->text('notes');
			$table->text('internal_notes');
			$table->timestamps();
		});

		DB::statement("ALTER TABLE `transactable_headers` CHANGE `flags` `flags` SET('printed','email_when_created','show_bank_account','show_discount') NOT NULL;");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('transactable_headers');
	}
}
