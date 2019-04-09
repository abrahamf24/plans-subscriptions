<?php

namespace Abrahamf24\PlansSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Abrahamf24\PlansSubscriptions\Events\FeatureConsumed;
use Abrahamf24\PlansSubscriptions\Events\FeatureUnconsumed;
use Abrahamf24\PlansSubscriptions\Library\Dates;
use Abrahamf24\PlansSubscriptions\Events\ExtendSubscription;

class PlanSubscription extends Model
{
    protected $fillable = [
        'name', 
        'payment_method', 
        'starts_on', 
        'expires_on',
        'is_paid', 
        'period_id', 
        'is_recurring', 
        'charging_price', 
        'charging_currency','recurring_each_unit', 
        'recurring_each_count', 
        'cancelled_on'
    ];

    protected $dates = [
        'starts_on',
        'expires_on',
        'cancelled_on',
    ];

    protected $casts = [
        'is_paid'=>'boolean'
    ];

    /**
     * PlanSubscription constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.subscriptions'));
    }

    /**
     * Returns the model associated with the subscription
     * 
     * @return mixed
     */
    public function model(){
        return $this->morphTo();
    }

    /**
     * Returns the PlanPeriod model associated with the
     * subscription
     * 
     * @return Abrahamf24\PlansSubscriptions\Models\PlanPeriod
     */
    public function plan_period(){
        return $this->belongsTo(config('subscriptions.models.period'), 'period_id');
    }

    /**
     * [features description]
     * @return [type] [description]
     */
    public function features(){
        return $this->plan_period->plan->features();
    }

    /**
     * Returns the features usage of the subscription
     * 
     * @return Collection
     */
    public function usages(){
        return $this->hasMany(config('subscriptions.models.usage'), 'subscription_id');
    }



    public function scopePaid($query){
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query){
        return $query->where('is_paid', false);
    }

    public function scopeExpired($query){
        return $query->where('expires_on', '<', Carbon::now()->toDateTimeString());
    }

    public function scopeCancelled($query){
        return $query->whereNotNull('cancelled_on');
    }

    public function scopeNotCancelled($query){
        return $query->whereNull('cancelled_on');
    }

    public function scopePaymentMethod($query, $method){
        return $query->where('payment_method', $method);
    }

    public function scopeName($query, $name){
        return $query->where('name',$name);
    }

    public function scopeActive($query){
        return $query->where('starts_on', '<=', Carbon::now())
            ->where(function($query){
                $query->whereNull('expires_on')
                    ->orWhere('expires_on', '>', Carbon::now());
            })->notCancelled();
    }

    public function scopeValid($query){
        return $query->where('starts_on', '<=', Carbon::now())
            ->notCancelled();
    }





    /**
     * Checks if the subscription is indefinied
     * 
     * @return boolean [description]
     */
    public function isIndefinied(){
        return !$this->expires_on;
    }

    /**
     * Checks if the current subscription has started.
     *
     * @return bool
     */
    public function hasStarted(){
        return (bool) Carbon::now()->greaterThanOrEqualTo(Carbon::parse($this->starts_on));
    }

    /**
     * Checks if the current subscription has expired.
     * When expires_on is null then returns false because
     * the subscription is indefinied
     *
     * @return bool
     */
    public function hasExpired(){
        if($this->isIndefinied())
            return false;

        \Log::info('Hoy'.print_r(Carbon::now(),true));
        \Log::info('Expira'.print_r(Carbon::parse($this->expires_on),true));
        return (bool) Carbon::now()->greaterThan(Carbon::parse($this->expires_on));
    }

    /**
     * Returns the number of days the subscription has expired
     * 
     * @return int
     */
    public function expiredDays(){
        if(!$this->hasExpired())
            return 0;

        $expire = $this->expires_on->copy();
        $now = Carbon::now();
        $expire->setTime(0,0,0);
        $now->setTime(0,0,0);
        return $expire->diffInDays($now);
    }

    /**
     * Checks if the current subscription is active.
     *
     * @return bool
     */
    public function isActive(){
        return (bool) ($this->hasStarted() && ! $this->hasExpired() && !$this->isCancelled());
    }

    /**
     * Checks if the current subscription is valid.
     *
     * @return bool
     */
    public function isValid(){
        return (bool) ($this->hasStarted() && !$this->isCancelled());
    }

    /**
     * Get the remaining days in this subscription.
     * If the subscrption is indefinied returns -1
     *
     * @return int
     */
    public function remainingDays(){
        if ($this->hasExpired()) {
            return (int) 0;
        }elseif($this->isIndefinied()){
            return (int) -1;
        }

        return (int) Carbon::now()->diffInDays(Carbon::parse($this->expires_on));
    }

    /**
     * Checks if the current subscription is cancelled (expiration date is in the past & the subscription is cancelled).
     *
     * @return bool
     */
    public function isCancelled(){
        return (bool) $this->cancelled_on != null;
    }

    /**
     * Checks if the current subscription is pending cancellation.
     *
     * @return bool
     */
    public function isCancellationWithValidPeriod(){
        return (bool) ($this->isCancelled() && !$this->hasExpired());
    }

    /**
     * Cancel this subscription.
     *
     * @return self $this
     */
    public function cancel(){
        $this->update([
            'cancelled_on' => Carbon::now(),
        ]);

        return $this;
    }

    /**
     * Consume a feature, if it is 'limit' type.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @param float $amount The amount consumed.
     * @return bool Wether the feature was consumed successfully or not.
     */
    public function consumeFeature(string $featureCode, float $amount)
    {
        $usageModel = config('subscriptions.models.usage');

        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return false;
        }

        $usage = $this->usages()->featureCode($featureCode)->first();

        if (! $usage) {
            $usage = $this->usages()->save(new $usageModel([
                'feature_code' => $featureCode,
                'used' => 0,
            ]));
        }

        if (! $feature->isUnlimited() && $usage->used + $amount > $feature->limit) {
            return false;
        }

        $remaining = (float) ($feature->isUnlimited()) ? -1 : $feature->limit - ($usage->used + $amount);

        event(new FeatureConsumed($this, $feature, $amount, $remaining));

        return $usage->update([
            'used' => (float) ($usage->used + $amount),
        ]);
    }

    /**
     * Reverse of the consume a feature method, if it is 'limit' type.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @param float $amount The amount consumed.
     * @return bool Wether the feature was consumed successfully or not.
     */
    public function unconsumeFeature(string $featureCode, float $amount)
    {
        $usageModel = config('subscriptions.models.usage');

        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return false;
        }

        $usage = $this->usages()->code($featureCode)->first();

        if (! $usage) {
            $usage = $this->usages()->save(new $usageModel([
                'code' => $featureCode,
                'used' => 0,
            ]));
        }

        $used = (float) ($feature->isUnlimited()) ? ($usage->used - $amount < 0) ? 0 : ($usage->used - $amount) : ($usage->used - $amount);
        $remaining = (float) ($feature->isUnlimited()) ? -1 : ($used > 0) ? ($feature->limit - $used) : $feature->limit;

        event(new FeatureUnconsumed($this, $feature, $amount, $remaining));

        return $usage->update([
            'used' => $used,
        ]);
    }

    /**
     * Get the amount used for a limit.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @return null|float Null if doesn't exist, integer with the usage.
     */
    public function getUsageOf(string $featureCode)
    {
        $usage = $this->usages()->featureCode($featureCode)->first();
        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return;
        }

        if (! $usage) {
            return 0;
        }

        return (float) $usage->used;
    }

    /**
     * Check if subscription has feature
     * 
     * @param  string  $featureCode 
     * @return boolean              
     */
    public function hasFeature(string $featureCode){
        $feature = $this->features()->code($featureCode)->first();

        if (!$feature) {
            return false;
        }

        return true;
    }

    /**
     * Get the amount remaining for a feature.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @return float The amount remaining.
     */
    public function getRemainingOf(string $featureCode)
    {
        $usage = $this->usages()->featureCode($featureCode)->first();
        $feature = $this->features()->code($featureCode)->first();

        if (! $feature || $feature->type != 'limit') {
            return 0;
        }

        if (! $usage) {
            return (float) ($feature->isUnlimited()) ? -1 : $feature->limit;
        }

        return (float) ($feature->isUnlimited()) ? -1 : ($feature->limit - $usage->used);
    }

    /**
     * Extend the current subscription for the specified periods as long as
     * it is not cancelled.
     * Se puede extender a partir del día actual o del día en el que venció
     * la suscripción
     *
     * @param int    $periods       Periods to extend the subscriptio
     * @param bool   $startFromNow  
     * @return PlanSubscription The PlanSubscription model instance of the extended subscription.
     */
    public function extend($periods, bool $startFromNow = true, $is_paid=false)
    {
        if ($periods < 1) {
            return false;
        }

        /*if(!$this->hasExpired()){
            //Agregar periodos desde la fecha en que caducó la sucripción
            $newExpiration = Dates::addPeriods($this->expires_on, $periods, $this->recurring_each_count, $this->recurring_each_unit);
            $this->update([
                'expires_on' => $newExpiration,
            ]);

            event(new ExtendSubscription($this->model, $this, $startFromNow, null));
            return $this;

        }else{*/
            if ($startFromNow) {
                //Agregar periodos desde la fecha actual
                $newStart = Carbon::now();
                $newExpiration = Dates::addPeriods($newStart, $periods, $this->recurring_each_count, $this->recurring_each_unit);
            }else{
                //Agregar periodos desde la fecha en que caducó la sucripción
                $newStart = $this->expires_on;
                $newExpiration = Dates::addPeriods($newStart, $periods, $this->recurring_each_count, $this->recurring_each_unit);
            }
            $subscriptionModel = config('subscriptions.models.subscription');

            //Suscripción termina al final del día
            $newExpiration->setTime(23,59,59);
            $this->update([
                'expires_on' => $newExpiration,
                'is_paid' => (bool) $this->charging_price==0?true:$is_paid
            ]);

            event(new ExtendSubscription($this->model, $this, $startFromNow, $this));
            return $this;
        /*}*/
    }

}
