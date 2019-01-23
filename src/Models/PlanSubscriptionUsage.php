<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscriptionUsage extends Model
{
    protected $fillable = [
    	
    ];

    /**
     * PlanSubscriptionUsage constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.usages'));
    }

    /**
     * Returns the PlanSubscription model associated
     * 
     * @return Abrahamf24\PlansSubscriptions\Models\PlanSubscription
     */
    public function subscription(){
        return $this->belongsTo(config('subscriptions.models.subscription'), 'subscription_id');
    }


    public function scopeCode($query, string $code){
        return $query->where('code', $code);
    }
}
