<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purchase_headers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->boolean('printed');
			$table->boolean('approved');
			$table->boolean('released');
			$table->string('reference');
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->integer('contact_id')->unsigned();
			$table->foreign('contact_id')->references('id')->on('users');
			$table->integer('purchase_id')->unsigned();
			$table->foreign('purchase_id')->references('id')->on('users');
			$table->enum('type', array('quote', 'order', 'return'));
			$table->enum('status', array('open', 'void', 'closed'));
			$table->integer('billing_address_id')->unsigned();
			$table->foreign('billing_address_id')->references('id')->on('addresses');
			$table->integer('shipping_address_id')->unsigned();
			$table->foreign('shipping_address_id')->references('id')->on('addresses');
			$table->integer('payment_term_id')->unsigned();
			$table->foreign('payment_term_id')->references('id')->on('payment_terms');
			$table->string('fob');
			$table->string('via');
			$table->decimal('tax_rate', 5, 2);
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
			$table->date('order_date');
			$table->text('notes');
			$table->text('internal_notes');			
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
		Schema::dropIfExists('purchase_headers');
	}
}
