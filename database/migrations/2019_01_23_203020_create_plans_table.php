<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlansTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.plans'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('code');
			$table->text('description')->nullable();
			$table->string('type')->default('main');
			$table->text('metadata')->nullable();
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
		Schema::drop(config('subscriptions.tables.plans'));
	}

}
