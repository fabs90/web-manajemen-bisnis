<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalNoticeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Subscription $subscription,
        public ?User $user = null,
    ) {
        $this->subscription->loadMissing(['plan', 'user', 'organization.owners']);

        $this->user ??= $this->subscription->user
            ?? $this->subscription->organization?->owners->first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $planName = $this->subscription->plan?->name ?? config('app.name');

        return new Envelope(
            subject: 'Pemberitahuan Perpanjangan Langganan: ' . $planName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $daysRemaining = $this->subscription->ends_at
            ? max(0, (int) now()->startOfDay()->diffInDays($this->subscription->ends_at->copy()->startOfDay(), false))
            : null;

        return new Content(
            markdown: 'emails.subscription-renewal-notice',
            with: [
                'subscription' => $this->subscription,
                'plan' => $this->subscription->plan,
                'user' => $this->user,
                'owner' => $this->subscription->owner(),
                'daysRemaining' => $daysRemaining,
                'renewUrl' => route('subscriptions.index'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
