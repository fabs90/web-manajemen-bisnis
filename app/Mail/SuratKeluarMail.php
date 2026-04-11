<?php

namespace App\Mail;

use App\Models\AgendaSuratKeluar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Barryvdh\DomPDF\Facade\Pdf; // 1. Import Facade PDF

class SuratKeluarMail extends Mailable implements ShouldQueue
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
        $pdfContent = Pdf::loadView('emails.surat-keluar-pdf', [
            'surat' => $this->surat,
            'user' => $this->user
        ])->output();

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

        $namaFileSurat = str_replace('/', '-', $this->surat->nomor_surat) . '.pdf';
        $email->attachData($pdfContent, $namaFileSurat, [
            'mime' => 'application/pdf',
        ]);

        // if ($this->surat->file_lampiran) {
        //     $pathFile = storage_path("app/public/" . $this->surat->file_lampiran);

        //     if (file_exists($pathFile)) {
        //         $email->attach(
        //             $pathFile,
        //             [
        //                 'as' => basename($this->surat->file_lampiran),
        //                 'mime' => File::mimeType($pathFile),
        //             ]
        //         );
        //     }
        // }

        return $email;
    }
}
