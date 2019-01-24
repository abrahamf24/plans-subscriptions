<?php
namespace Abrahamf24\PlansSubscriptions\Traits;
use Carbon\Carbon;
use Abrahamf24\PlansSubscriptions\Library\Dates;
use Abrahamf24\PlansSubscriptions\Events\UpgradeSubscription;

trait HasSubscriptions{

	/**
     * Get Subscriptions relatinship.
     *
     * @return morphMany Relatinship.
     */
    public function subscriptions()
    {
        return $this->morphMany(config('subscriptions.models.subscription'), 'model');
    }

	/**
     * Return the current subscriptions relatinship. Una suscripción es
     * actual si la fecha actual es mayor a la fecha de inicio de la
     * suscripción y si la fecha de fin es menor a la fecha actual o
     * es nula
     *
     * @return morphMany Relatinship.
     */
    public function currentSubscriptions()
    {
        return $this->subscriptions()
            ->where('starts_on', '<=', Carbon::now())
            ->where(function($query){
            	$query->whereNull('expires_on')
            		->orWhere('expires_on', '>', Carbon::now());
            });
    }

	/**
     * Return the current active subscription.
     * 
     * @param  $name The name of subscription
     * @return PlanSubscriptionModel The PlanSubscription model instance.
     */
    public function activeSubscription($name = 'main')
    {
        return $this->currentSubscriptions()->name($name)->paid()->notCancelled()->first();
    }


	/**
     * Check if the model has an active subscription right now of type $type.
     *
     * @param $name The name of subscription
     * @return bool Wether the binded model has an active subscription or not.
     */
    public function hasActiveSubscription($name='main')
    {
        return (bool) $this->activeSubscription($name);
    }

    /**
     * Check if the model has subscriptions with a name.
     *
     * @param  $name The name of subscription
     * @return bool Wether the binded model has subscriptions or not.
     */
    public function hasSubscriptions($name='main')
    {
        return (bool) ($this->subscriptions()->name($name)->count() > 0);
    }

    /**
     * Get the last active subscription.
     *
     * @param  $name The name of subscription
     * @return null|PlanSubscriptionModel The PlanSubscription model instance.
     */
    public function lastActiveSubscription($name='main')
    {
        if (! $this->hasSubscriptions($name)) {
            return;
        }

        if ($this->hasActiveSubscription($name)) {
            return $this->activeSubscription($name);
        }

        return $this->subscriptions()->name($name)->latest('starts_on')->paid()->notCancelled()->first();
    }

    /**
     * Get the last unpaid subscription, if any.
     *
     * @param  $name The name of subscription
     * @return PlanSubscriptionModel
     */
    public function lastUnpaidSubscription($name='main')
    {
        return $this->subscriptions()->name($name)->latest('starts_on')->notCancelled()->unpaid()->first();
    }

    /**
     * Get the last subscription.
     *
     * @param  $name The name of subscription
     * @return null|PlanSubscriptionModel Either null or the last subscription.
     */
    public function lastSubscription($name='main')
    {
        if (! $this->hasSubscriptions($name)) {
            return;
        }

        if ($this->hasActiveSubscription($name)) {
            return $this->activeSubscription($name);
        }

        return $this->subscriptions()->name($name)->latest('starts_on')->first();
    }

    /**
     * When a subscription is due, it means it was created, but not paid.
     * For example, on subscription, if your user wants to subscribe to another subscription and has a due (unpaid) one, it will
     * check for the last due, will cancel it, and will re-subscribe to it.
     *
     * @return null|PlanSubscriptionModel Null or a Plan Subscription instance.
     */
    public function lastDueSubscription($name='main')
    {
        if (! $this->hasSubscriptions($name)) {
            return;
        }

        if ($this->hasActiveSubscription($name)) {
            return;
        }

        $lastActiveSubscription = $this->lastActiveSubscription($name);

        if (! $lastActiveSubscription) {
            return $this->lastUnpaidSubscription($name);
        }

        $lastSubscription = $this->lastSubscription($name);

        if ($lastActiveSubscription->is($lastSubscription)) {
            return;
        }

        return $this->lastUnpaidSubscription($name);
    }

    /**
     * Check if the mode has a due, unpaid subscription.
     *
     * @param  $name The name of subscription
     * @return bool
     */
    public function hasDueSubscription($name = 'main')
    {
        return (bool) $this->lastDueSubscription($name);
    }

  	/**
     * Subscribe the binded model to a plan for a number of periods. Returns false if it has an 
     * active subscription of same type already.
     *
     * @param PlanPeriod $plan_period The PlanPeriod model instance.
     * @param int $periods The number of periods of the plan
     * @param string $name The name of the subscription, default is "main"
     * @param string $payment_method The payment method of the subscription
     * @param boolean $is_paid  The subscription is paid, true is defined for plan periods with price equals to zero 
     * @return PlanSubscription The PlanSubscription model instance.
     */
    public function subscribeTo($plan_period, int $periods = null, $name = 'main', $payment_method=null, $is_paid=false){
        $subscriptionModel = config('subscriptions.models.subscription');
        $plan = $plan_period->plan;

        $is_recurring = $plan_period->is_recurring;

        //Si el periodo de plan no tiene unidad o cantidad de periodo
        //entonces se define periodos a cero ya que es infinito
        if(!$plan_period->period_unit || !$plan_period->period_count){
        	$periods = null;
        }

        //Una subscripción recurrente debe tener el número de periodos definidos
        if($is_recurring && !$periods){
        	throw new \Exception("Cuando una subscripción es recurrente se deben definir los periodos de la subscripción", 1);
        }

        //No se permiten 2 o más subscripciones del mismo tipo de plan
        if ($this->hasActiveSubscription($name)) {
            return false;
        }

        if ($this->hasDueSubscription($name)) {
            $this->lastDueSubscription($name)->delete();
        }

        //Suscripción termina al final del día
        $endOfDay = Carbon::now();
        $endOfDay->setTime(23,59,59);

        $subscription = $this->subscriptions()->save(new $subscriptionModel([
            'period_id' => $plan_period->id,
            'starts_on' => Carbon::now()->subSeconds(1),
            'expires_on' => !$is_recurring && !$periods? null: Dates::addPeriods($endOfDay, $periods, $plan_period->period_count, $plan_period->period_unit),
            'cancelled_on' => null,
            'payment_method' => $payment_method,
            'is_paid' => (bool) $plan_period->price==0?true:$is_paid,
            'charging_price' => $plan_period->price,
            'charging_currency' => $plan_period->currency,
            'is_recurring' => $plan_period->is_recurring,
            'recurring_each_unit' => $plan_period->period_unit,
            'recurring_each_count' => $plan_period->period_count,
        ]));

        event(new \Abrahamf24\PlansSubscriptions\Events\NewSubscription($this, $subscription));

        return $subscription;
    }


    /**
     * Extend the current subscription for the specified periods as long as
     * it is not cancelled.
     * Se puede extender a partir del día actual o del día en el que venció
     * la suscripción
     *
     * @param string $name          Name of subscription
     * @param int    $periods       Periods to extend the subscriptio
     * @param bool   $startFromNow  
     * @return PlanSubscription The PlanSubscription model instance of the extended subscription.
     */
    public function extendSubscription($name='main', $periods, bool $startFromNow = true, $is_paid=false)
    {
        //Si no tiene suscripción activa con name $name
        if (! $this->hasActiveSubscription($name)) {

            //Si tiene suscripción con name $name
            if ($this->hasSubscriptions($name)) {
                $lastActiveSubscription = $this->lastActiveSubscription($name);
                $lastActiveSubscription->load(['plan_period']);

                return $this->subscribeTo($lastActiveSubscription->plan_period, $periods, $name, $lastActiveSubscription->payment_method,$is_paid);
            }

            //En caso de que no tenga suscripciones del name $name
            return false;
        }

        return $this->activeSubscription($name)->extend($periods, $startFromNow, $is_paid);
    }

    /**
     * Cancela una suscripción con name = $name,
     * en caso de que no haya una suscripción
     * activa devuelve false
     * 
     * @param  string $name Name of the subscription
     * @return boolean
     */
    public function cancelSubscription($name='main'){
        $activeSubscription = $this->activeSubscription($name);

        if(!$activeSubscription)
            return false;

        $activeSubscription->cancel();
        return true;
    }


    /**
     * Upgrade the binded model's plan. If it is the same plan, it just extends it.
     *
     * @param PlanModel $newPlan The new Plan model instance.
     * @param int $duration The duration, in days, for the new subscription.
     * @param bool $startFromNow Wether the subscription will start from now, extending the current plan, or a new subscription will be created to extend the current one.
     * @param bool $isRecurring Wether the subscription should auto renew. The renewal period (in days) is the difference between now and the set date.
     * @return PlanSubscription The PlanSubscription model instance with the new plan or the current one, extended.
     */
    public function upgradePlanTo($new_plan_period, int $periods = null, $name = 'main', $payment_method=null, $is_paid=false)
    {
        if ($periods < 1) {
            return false;
        }

        $activeSubscription = null;
        if($this->hasActiveSubscription($name)){
            $activeSubscription = $this->activeSubscription($name);
            $activeSubscription->cancel();
        }

        $newSubscription = $this->subscribeTo($new_plan_period, $periods, $name, $payment_method, $is_paid);

        event(new UpgradeSubscription($this, $activeSubscription, $newSubscription));

        return $newSubscription;
    }
}