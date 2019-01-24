<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $fillable = [
    	'name', 'code', 'description', 'type', 'limit', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'object',
    ];

    /**
     * PlanFeature constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.features'));
    }

    /**
     * Boot function for using with events
     * 
     * @return void
     */
    protected static function boot(){
        parent::boot();

        static::creating(function($model){
            //Si el tipo de feature es feature entonces no 
            //se debe definir límite
            if($model->type == 'feature'){
                $model->limit = null;
            }
        });
    }

    /**
     * Returns the model of Plan
     * 
     * @return Abrahamf24\PlansSubscriptions\Models\PlanPeriod
     */
    public function plan(){
    	return $this->belongsTo('Abrahamf24\PlansSubscriptions\Models\PlanPeriod', 'plan_id');
    }

    /**
     * Query plans by type
     * 
     * @param  Illuminate\Database\Query\Builder 	$query
     * @param  string $code
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeCode($query, $code){
    	return $query->where('code',$code);
    }

    /**
     * Query plans by type
     * 
     * @param  Illuminate\Database\Query\Builder 	$query
     * @param  string $type
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeType($query, $type){
    	return $query->where('type',$type);
    }

    /**
     * Query plans by type
     * 
     * @param  Illuminate\Database\Query\Builder 	$query
     * @param  string $limit
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeLimited($query, $limit){
    	return $query->where('limit',$limit);
    }

    /**
     * Verify if plan feature is unlimited
     * 
     * @return boolean
     */
    public function isUnlimited()
    {
        return (bool) ($this->type == 'limit' && $this->limit < 0);
    }
}
