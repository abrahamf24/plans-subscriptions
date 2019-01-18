<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPeriod extends Model
{
    protected $table = 'plans_periods';

    protected $fillable = [
    	'name', 'price', 'currency', 'period_unit', 'period_count'
    ];
}
