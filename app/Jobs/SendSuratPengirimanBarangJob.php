<?php

namespace App\Jobs;

use App\Mail\SuratPengirimanBarangMail;
use App\Models\SPB\SuratPengirimanBarang;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSuratPengirimanBarangJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public SuratPengirimanBarang $spb, public $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $email = $this->spb->pesananPenjualan?->pelanggan?->email ?? null;
            if (! $email) {
                Log::warning('SPB Job: Email pelanggan tidak ditemukan untuk SPB ID '.$this->spb->id);

                return;
            }

            Mail::to($email)->send(new SuratPengirimanBarangMail($this->spb, $this->user));
        } catch (Exception $e) {
            Log::error('Error mengirim SPB Mail: '.$e->getMessage());
            throw $e;
        }
    }
}
