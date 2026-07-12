<?php

namespace App\Jobs;

use App\Mail\SuratPesananPembelianMail;
use App\Models\SPP\SuratPesananPembelian;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSuratPesananPembelianJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SuratPesananPembelian $pesananPembelian,
        public User $user,
        public string $emailPenerima
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->emailPenerima)->send(
            new SuratPesananPembelianMail($this->pesananPembelian, $this->user)
        );
    }
}
