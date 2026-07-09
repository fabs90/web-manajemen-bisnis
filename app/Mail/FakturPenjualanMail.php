<?php

namespace App\Mail;

use App\Models\Faktur\FakturPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class FakturPenjualanMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public FakturPenjualan $faktur, public $user)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Faktur Penjualan: ' . $this->faktur->kode_faktur,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.faktur-penjualan',
            with: [
                'data' => $this->faktur,
                'user' => $this->user,
            ]
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

        $faktur = FakturPenjualan::with([
            'suratPengirimanBarang',
            'suratPengirimanBarang.pesananPenjualan.pelanggan',
        ])->find($this->faktur->id);

        $profileUser = $this->user;

        $pdf = Pdf::loadView(
            'administrasi.surat.faktur-penjualan.template-pdf',
            compact('faktur', 'profileUser'),
        )->setPaper('A4', 'portrait');

        $attachments[] = Attachment::fromData(
            fn () => $pdf->output(),
            Str::slug('faktur-penjualan-' . $this->faktur->kode_faktur) . '.pdf'
        )->withMime('application/pdf');

        return $attachments;
    }
}
