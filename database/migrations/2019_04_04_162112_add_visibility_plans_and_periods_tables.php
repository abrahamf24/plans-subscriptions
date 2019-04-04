<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibilityPlansAndPeriodsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('subscriptions.tables.plans'), function (Blueprint $table) {
            $table->enum('visibility', array('public','hidden'))->default('public');
        });
        Schema::table(config('subscriptions.tables.periods'), function (Blueprint $table) {
            $table->enum('visibility', array('public','hidden'))->default('public');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('subscriptions.tables.plans'), function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
        Schema::table(config('subscriptions.tables.periods'), function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
}
