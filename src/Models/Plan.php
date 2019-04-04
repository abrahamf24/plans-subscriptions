<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Plan extends Model
{
    protected $fillable = [
    	'name', 'code', 'description', 'type', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'object',
    ];

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_HIDDEN = 'hidden';

    /**
     * Plan constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.plans'));
    }

    /**
     * Boot function for using with events
     * 
     * @return void
     */
    protected static function boot(){
        parent::boot();

        static::creating(function($model){
            //Si ya existe un plan con el mismo código y el mismo tipo
            //lanzar excepción
            if(self::code($model->code)->type($model->type)->count()){
                throw new \Exception("There is a plan with same code and same type", 1);
            }
        });

        //Solo devolver planes públicos
        static::addGlobalScope('publics', function(Builder $builder){
            $builder->where('visibility','public');
        });
    }

    /**
     * Returns collection of associate periods
     * 
     * @return Collection
     */
    public function periods(){
    	return $this->hasMany('Abrahamf24\PlansSubscriptions\Models\PlanPeriod', 'plan_id');
    }

    /**
     * Returns the PlanFeature models associated with the plan
     * 
     * @return Collection
     */
    public function features(){
        return $this->hasMany('Abrahamf24\PlansSubscriptions\Models\PlanFeature', 'plan_id');
    }

    /**
     * Check if a plan is public
     * 
     * @return boolean
     */
    public function isPublic(){
        return $this->visibility == self::VISIBILITY_PUBLIC;
    }

    /**
     * Check if a plan is hidden
     * 
     * @return boolean
     */
    public function isHidden(){
        return $this->visibility == self::VISIBILITY_HIDDEN;
    }

    /**
     * Query plans by type
     * 
     * @param  Illuminate\Database\Query\Builder    $query
     * @param  string                               $type
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeType($query, $type='main'){
        return $query->where('type',$type);
    }

    /**
     * Query plans by code
     * 
     * @param  Illuminate\Database\Query\Builder    $query
     * @param  string                               $code
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeCode($query, $code){
        return $query->where('code',$code);
    }
}
