<?php

namespace Abrahamf24\PlansSubscriptions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class PlansSubscriptionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/2019_01_23_203020_create_plans_features_table.php' => database_path('migrations/2019_01_23_203020_create_plans_features_table.php'),
            __DIR__.'/../database/migrations/2019_01_23_203020_create_plans_periods_table.php' => database_path('migrations/2019_01_23_203020_create_plans_periods_table.php'),
            __DIR__.'/../database/migrations/2019_01_23_203020_create_plans_subscriptions_table.php' => database_path('migrations/2019_01_23_203020_create_plans_subscriptions_table.php'),
            __DIR__.'/../database/migrations/2019_01_23_203020_create_plans_table.php' => database_path('migrations/2019_01_23_203020_create_plans_table.php'),
            __DIR__.'/../database/migrations/2019_01_23_203020_create_plans_usages_table.php' => database_path('migrations/2019_01_23_203020_create_plans_usages_table.php'),
        ], 'migration');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
