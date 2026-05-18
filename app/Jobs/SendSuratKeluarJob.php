<?php

namespace App\Jobs;

use App\Mail\SuratKeluarMail;
use App\Models\AgendaSuratKeluar;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSuratKeluarJob implements ShouldQueue
{
    use Queueable;

    public $surat;

    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(AgendaSuratKeluar $surat, User $user)
    {
        $this->surat = $surat;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->surat->email_penerima)) {
            Log::warning("Skipping SendSuratKeluarJob: email_penerima is empty for Surat ID: {$this->surat->id}");

            return;
        }

        Mail::to($this->surat->email_penerima)->send(new SuratKeluarMail($this->surat, $this->user));
    }
}
