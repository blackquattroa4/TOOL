<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDocumentPermissionTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document_permission', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('original_document_id')->unsigned();
			$table->foreign('original_document_id')->references('id')->on('documents');
			$table->enum('accessor_type' , ['roles', 'users']);
			$table->integer('accessor_id')->unsigned();
			$table->boolean('permission_read');
			$table->boolean('permission_update');
			$table->boolean('permission_delete');
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
		Schema::dropIfExists('document_permission');
	}
}
