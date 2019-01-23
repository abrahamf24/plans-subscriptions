<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPeriod extends Model
{
    protected $fillable = [
    	'name', 'price', 'currency', 'period_unit', 'period_count', 'is_recurring'
    ];

    protected $casts = [
    	'is_recurring'=>'boolean'
    ];

    /**
     * PlanFeature constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.periods'));
    }

    /**
     * Returns the associated Plan model
     * 
     * @return Plan
     */
    public function plan(){
    	return $this->belongsTo(config('subscriptions.models.plan'), 'plan_id');
    }

    public function scopeName($query, $name){
        return $query->where('name',$name);
    }
}
