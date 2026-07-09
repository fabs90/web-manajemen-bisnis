<?php

namespace App\Jobs;

use App\Models\Faktur\FakturPenjualan;
use App\Mail\FakturPenjualanMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendSFakturPenjualanJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public FakturPenjualan $faktur, public $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $email = $this->faktur->suratPengirimanBarang?->pesananPenjualan?->pelanggan?->email ?? null;
            if (!$email) {
                Log::warning('Faktur Penjualan Job: Email pelanggan tidak ditemukan untuk Faktur ID ' . $this->faktur->id);
                return;
            }

            Mail::to($email)->send(new FakturPenjualanMail($this->faktur, $this->user));
        } catch (Exception $e) {
            Log::error('Error mengirim Faktur Penjualan Mail: ' . $e->getMessage());
            throw $e;
        }
    }
}
