<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscriptionUsage extends Model
{
    protected $table = 'plans_usages';

    protected $fillable = [
    	
    ];

    /**
     * Returns the PlanSubscription model associated
     * 
     * @return Abrahamf24\PlansSubscriptions\Models\PlanSubscription
     */
    public function subscription(){
        return $this->belongsTo(config('plans.models.subscription'), 'subscription_id');
    }


    public function scopeCode($query, string $code){
        return $query->where('code', $code);
    }
}
