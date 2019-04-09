<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscriptionUsage extends Model
{
    protected $fillable = [
        'feature_code',
        'used'
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


    public function scopeFeatureCode($query, string $code){
        return $query->where('feature_code', $code);
    }
}
