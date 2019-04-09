<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PlanPeriod extends Model
{
    protected $fillable = [
        'name', 'price', 'currency', 'period_unit', 'period_count', 'is_recurring', 'visibility'
    ];

    protected $casts = [
        'is_recurring'=>'boolean'
    ];

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_HIDDEN = 'hidden';

    /**
     * PlanPeriod constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.periods'));
    }

    /**
     * Boot function for using with events
     * 
     * @return void
     */
    protected static function boot(){
        parent::boot();

        static::creating(function($model){
            //Si la unidad de periodo o la cantidad del periodo es nula
            //ambos campos se definen a nulos
            if(!$model->period_unit || !$model->period_count){
                $model->period_unit = null;
                $model->period_count = null;
                $model->is_recurring = false;
            }

            //Si el precio es cero o null(gratis) entonces se definirá
            //como no recurrente.
            if(!$model->price){
                $model->price = 0;
                $model->is_recurring = false;
            }
        });

        //Solo devolver periodos públicos
        static::addGlobalScope('publics', function(Builder $builder){
            $builder->where('visibility','public');
        });
    }

    /**
     * Returns the associated Plan model
     * 
     * @return Plan
     */
    public function plan(){
        return $this->belongsTo(config('subscriptions.models.plan'), 'plan_id');
    }

    public function subscriptions(){
        return $this->hasMany(config('subscriptions.models.subscription'), 'period_id');
    }

    /**
     * Check if a period is public
     * 
     * @return boolean
     */
    public function isPublic(){
        return $this->visibility == self::VISIBILITY_PUBLIC;
    }

    /**
     * Check if a period is hidden
     * 
     * @return boolean
     */
    public function isHidden(){
        return $this->visibility == self::VISIBILITY_HIDDEN;
    }

    public function scopeName($query, $name){
        return $query->where('name',$name);
    }
}
