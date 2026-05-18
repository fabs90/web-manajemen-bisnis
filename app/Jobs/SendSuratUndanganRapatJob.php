<?php

namespace App\Jobs;

use App\Mail\SuratUndanganRapatMail;
use App\Models\SuratUndanganRapat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSuratUndanganRapatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected SuratUndanganRapat $suratUndanganRapat, protected $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->suratUndanganRapat->email_penerima) {
            Mail::to($this->suratUndanganRapat->email_penerima)->send(new SuratUndanganRapatMail($this->suratUndanganRapat, $this->user));
        }
    }
}
