<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlansSubscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.subscriptions'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('period_id');
			$table->integer('model_id');
			$table->string('model_type');
			$table->string('name')->default('main');
			$table->string('payment_method')->nullable();
			$table->boolean('is_paid')->default(0);
			$table->float('charging_price')->nullable();
			$table->string('charging_currency', 10)->nullable();
			$table->boolean('is_recurring')->default(1);
			$table->enum('recurring_each_unit', array('day','month'))->nullable()->default('month');
			$table->integer('recurring_each_count')->nullable()->default(1);
			$table->dateTime('starts_on');
			$table->dateTime('expires_on')->nullable();
			$table->dateTime('payment_tolerance')->nullable();
			$table->dateTime('cancelled_on')->nullable();
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
		Schema::drop(config('subscriptions.tables.subscriptions'));
	}

}
