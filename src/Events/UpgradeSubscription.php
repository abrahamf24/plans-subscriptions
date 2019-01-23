<?php

namespace Abrahamf24\PlansSubscriptions\Events;

use Illuminate\Queue\SerializesModels;

class UpgradeSubscription
{
    use SerializesModels;

    public $model;
    public $oldSubscription;
    public $newSubscription;

    /**
     * @param Model $model The model on which the action was done.
     * @param Subscription $oldSubscription Old subscription
     * @param Subscription $newSubscription New subscription
     * @return void
     */
    public function __construct($model, $oldSubscription, $newSubscription)
    {
        $this->model = $model;
        $this->oldSubscription = $oldSubscription;
        $this->newSubscription = $newSubscription;
    }
}
