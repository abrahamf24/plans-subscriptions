<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPeriod extends Model
{
    protected $table = 'plans_periods';

    protected $fillable = [
    	'name', 'price', 'currency', 'period_unit', 'period_count', 'is_recurring'
    ];

    protected $casts = [
    	'is_recurring'=>'boolean'
    ];

    /**
     * Returns the associated Plan model
     * 
     * @return Plan
     */
    public function plan(){
    	return $this->belongsTo(config('plans.models.plan'), 'plan_id');
    }

    public function scopeName($query, $name){
        return $query->where('name',$name);
    }
}
