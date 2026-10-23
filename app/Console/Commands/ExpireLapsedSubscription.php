<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Console\Command;

class ExpireLapsedSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:expire-lapsed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire lapsed subscription';

    /**
     * Execute the console command.
     */
    public function handle(
        SubscriptionService $subscriptionService
    ) {
        $this->comment('Expire lapsed subscription');
        Subscription::query()->lapsed()->with(['plan'])->chunkById(100, function ($subscriptions) use ($subscriptionService) {
            $this->comment('Expire Subscription for: '.$subscriptions->id);
            $subscriptionService->expire($subscriptions);
        });
    }
}
