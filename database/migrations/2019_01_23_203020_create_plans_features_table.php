<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlansFeaturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.features'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('plan_id');
			$table->string('name');
			$table->string('code');
			$table->text('description')->nullable();
			$table->enum('type', array('limit','feature'))->default('feature');
			$table->integer('limit')->nullable();
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
		Schema::drop(config('subscriptions.tables.features'));
	}

}
