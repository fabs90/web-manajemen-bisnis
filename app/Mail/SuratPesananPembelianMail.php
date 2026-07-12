<?php

namespace App\Mail;

use App\Models\SPP\SuratPesananPembelian;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SuratPesananPembelianMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public SuratPesananPembelian $pesananPembelian, public $user) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Surat Pesanan Pembelian: '.$this->pesananPembelian->nomor_pesanan_pembelian,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.surat-pesanan-pembelian',
            with: [
                'data' => $this->pesananPembelian,
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

        // Generate PDF and attach it
        $pdf = $this->pesananPembelian->generatePdf();

        $attachments[] = Attachment::fromData(
            fn () => $pdf->output(),
            Str::slug('surat-pesanan-pembelian-'.$this->pesananPembelian->nomor_pesanan_pembelian).'.pdf'
        )->withMime('application/pdf');

        return $attachments;
    }
}
