<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('password');
			$table->string('phone');
			$table->integer('entity_id')->unsigned();
			$table->foreign('entity_id')->references('id')->on('taxable_entities');
			$table->text('landing_page');
			$table->boolean('active');
			$table->enum('language', array('en', 'zh', 'zht', 'es'));
			$table->integer('failure_count');
			$table->string('imap_endpoint');
			$table->string('smtp_endpoint');
			$table->string('email_password');
			$table->timestamp('last_failure')->useCurrent();
			$table->timestamp('last_login')->useCurrent();
			$table->rememberToken();
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
		Schema::dropIfExists('users');
	}
}
