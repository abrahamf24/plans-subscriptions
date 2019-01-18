<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
    	'name', 'code', 'description', 'type', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'object',
    ];
}
