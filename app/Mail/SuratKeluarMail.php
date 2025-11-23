<?php

namespace App\Mail;

use App\Models\AgendaSuratKeluar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuratKeluarMail extends Mailable
{
    use Queueable, SerializesModels;

    public $surat;
    public $user;

    public function __construct(AgendaSuratKeluar $surat, $user)
    {
        $this->surat = $surat;
        $this->user = $user;
    }
    public function build()
    {
        $email = $this->from(
            "no-reply@digitrans.co.id",
            "Digitrans | {$this->user->email}",
        )
            ->subject(
                "Surat | {$this->surat->nomor_surat} | {$this->user->name}",
            )
            ->view("emails.surat-keluar-template")
            ->with([
                "surat" => $this->surat,
            ]);

        // Lampiran opsional
        if ($this->surat->file_lampiran) {
            $email->attach(
                storage_path("app/public/" . $this->surat->file_lampiran),
            );
        }

        return $email;
    }
}
