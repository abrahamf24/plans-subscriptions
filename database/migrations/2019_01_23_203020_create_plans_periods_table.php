<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlansPeriodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.periods'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->float('price')->nullable();
			$table->string('currency', 10)->default('MXN');
			$table->enum('period_unit', array('day','month'))->nullable()->default('month');
			$table->integer('period_count')->nullable()->default(1);
			$table->boolean('is_recurring')->default(1);
			$table->integer('plan_id');
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
		Schema::drop(config('subscriptions.tables.periods'));
	}

}
