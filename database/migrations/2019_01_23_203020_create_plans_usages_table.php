<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlansUsagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.usages'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('subscription_id');
			$table->string('feature_code');
			$table->integer('used')->default(0);
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
		Schema::drop(config('subscriptions.tables.usages'));
	}

}
