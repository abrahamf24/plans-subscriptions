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

    /**
     * Modelo usado para suscripciones, usado para las relaciones
     * del Trait
     */
    'models' => [
        'subscription' => \Abrahamf24\PlansSubscriptions\PlanSubscription::class,
        'plan' => \Abrahamf24\PlansSubscriptions\Plan::class,
        'usage' => \Abrahamf24\PlansSubscriptions\PlanSubscriptionUsage::class,
    ]

];
