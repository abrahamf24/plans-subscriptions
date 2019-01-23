<?php

namespace Abrahamf24\PlansSubscriptions\Test;

use Abrahamf24\PlansSubscriptions\Models\Plan;
//use Abrahamf24\Plans\Test\Models\User;
use Abrahamf24\PlansSubscriptions\Models\PlanFeature;
use Orchestra\Testbench\TestCase as Orchestra;
use Abrahamf24\PlansSubscriptions\Models\PlanSubscription;
use Abrahamf24\PlansSubscriptions\Models\PlanSubscriptionUsage;
use Abrahamf24\PlansSubscriptions\Models\PlanPeriod;

abstract class TestCase extends Orchestra
{

    public function setUp()
    {
        parent::setUp();
    }
}
