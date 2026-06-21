<?php

namespace App\Console\Commands;

use App\Mail\SuratKeluarMail;
use App\Models\AgendaSuratKeluar;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSuratKeluarTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-surat-keluar-test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending surat keluar email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $surat = AgendaSuratKeluar::latest()->first();
        $user = User::first();

        if (! $surat || ! $user) {
            $this->error('No AgendaSuratKeluar or User found in the database.');

            return;
        }

        $email = $this->argument('email') ?? $surat->email_penerima;

        if (empty($email)) {
            $this->error('No email specified. Please provide an email address as an argument or ensure the latest surat has an email_penerima.');

            return;
        }

        $this->info("Sending surat keluar test email to: {$email}");

        Mail::to($email)->send(new SuratKeluarMail($surat, $user));

        $this->info('Email dispatched successfully.');
    }
}
