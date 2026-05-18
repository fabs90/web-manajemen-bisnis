<?php

namespace App\Mail;

use App\Models\SuratUndanganRapat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SuratUndanganRapatMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public SuratUndanganRapat $suratUndanganRapat, public $user) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan Rapat: '.$this->suratUndanganRapat->judul_rapat,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.surat-undangan-rapat',
            with: [
                'surat' => $this->suratUndanganRapat,
                'user' => $this->user,
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
        $attachments = [];

        if ($this->suratUndanganRapat->file_lampiran) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->suratUndanganRapat->file_lampiran);
        }

        // Generate PDF using model method and attach it
        $pdf = $this->suratUndanganRapat->generatePdf();

        $attachments[] = Attachment::fromData(fn () => $pdf->output(), Str::slug('Surat Undangan Rapat '.$this->suratUndanganRapat->perihal).'.pdf')
            ->withMime('application/pdf');

        return $attachments;
    }
}
