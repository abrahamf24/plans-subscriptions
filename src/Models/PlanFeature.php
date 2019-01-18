<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $table = 'plans_features';

    protected $fillable = [
    	'name', 'code', 'description', 'type', 'limit', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'object',
    ];

}
