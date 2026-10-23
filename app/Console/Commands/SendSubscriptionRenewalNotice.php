<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionRenewalNoticeMail;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionRenewalNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:send-subscription-renewal-notice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription Renewal Notice';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->comment('Send Subscription Renewal Notice');

        Subscription::query()
            // preloads the user, organization owners, and plan in bulk so no extra database queries run inside the loop
            ->with(['user', 'organization.owners', 'plan'])
            ->renewalNotice()
            ->chunkById(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $this->comment('Send Subscription Renewal Notice for: ' . $subscription->id);

                    if ($email = $subscription->receipentEmail()) {
                        Mail::to($email)->send(new SubscriptionRenewalNoticeMail($subscription));
                    } else {
                        $this->comment('No email found for subscription: ' . $subscription->id);
                    }
                }
            });
    }
}
