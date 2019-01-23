<?php

return [

    /**
     * Nombres de las tablas que se definirán a la hora de
     * ejecutar la migración
     */
    'tables' => [
        'plans' => 'plans',
        'periods' => 'plan_periods',
        'subscriptions' => 'plan_subscriptions',
        'features' => 'plan_features',
        'usages' => 'plan_subscription_usages'
    ],

    /*
     * The model which handles the plans tables.
     */
    'models' => [
        'plan' => \Abrahamf24\Plans\Models\PlanModel::class,
        'subscription' => \Abrahamf24\Plans\Models\PlanSubscriptionModel::class,
        'feature' => \Abrahamf24\Plans\Models\PlanFeatureModel::class,
        'usage' => \Abrahamf24\Plans\Models\PlanSubscriptionUsageModel::class,
    ],

];
