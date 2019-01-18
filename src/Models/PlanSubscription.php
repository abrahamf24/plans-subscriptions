<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscription extends Model
{
    protected $table = 'plans_subscriptions';

    protected $fillable = [
    	'name', 'payment_method', 'starts_on', 'expires_on'
    ];

    protected $dates = [
        'starts_on',
        'expires_on',
        'cancelled_on',
    ];
}
