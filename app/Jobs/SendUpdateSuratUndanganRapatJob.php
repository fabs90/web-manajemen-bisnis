<?php

namespace App\Jobs;

use App\Mail\UpdateSuratUndanganRapatMail;
use App\Models\SuratUndanganRapat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendUpdateSuratUndanganRapatJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected SuratUndanganRapat $suratUndanganRapat, protected $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->suratUndanganRapat->email_penerima) {
            Mail::to($this->suratUndanganRapat->email_penerima)
                ->send(new UpdateSuratUndanganRapatMail($this->suratUndanganRapat, $this->user));
        }
    }
}
