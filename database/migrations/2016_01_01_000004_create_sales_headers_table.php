<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSalesHeadersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sales_headers', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->boolean('printed');
			$table->boolean('email_when_invoiced');
			$table->boolean('show_bank_account');
			$table->boolean('show_discount');
			$table->boolean('palletized');
			$table->boolean('approved');
			$table->enum('external_source', array('shopify', 'woo', 'magento'))->nullable();
			$table->string('reference');
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->integer('contact_id')->unsigned();
			$table->foreign('contact_id')->references('id')->on('users');
			$table->integer('sales_id')->unsigned();
			$table->foreign('sales_id')->references('id')->on('users');
			$table->enum('type', array('quote', 'order', 'return'));
			$table->enum('status', array('open', 'closed', 'void'));
			$table->integer('billing_address_id')->unsigned();
			$table->foreign('billing_address_id')->references('id')->on('addresses');
			$table->integer('shipping_address_id')->unsigned();
			$table->foreign('shipping_address_id')->references('id')->on('addresses');
			$table->integer('payment_term_id')->unsigned();
			$table->foreign('payment_term_id')->references('id')->on('payment_terms');
			$table->string('fob');
			$table->string('via');
			$table->date('order_date');
			$table->decimal('tax_rate', 5, 2);
			$table->integer('currency_id')->unsigned();
			$table->foreign('currency_id')->references('id')->on('currencies');
			$table->integer('shipping_location_id')->unsigned();
			$table->foreign('shipping_location_id')->references('id')->on('locations');
			$table->string('reserved_receivable_title');
			$table->text('notes');
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
		Schema::dropIfExists('sales_headers');
	}
}
