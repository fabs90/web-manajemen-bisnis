<?php

namespace App\Mail;

use App\Models\SPB\SuratPengirimanBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SuratPengirimanBarangMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public SuratPengirimanBarang $spb, public $user)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Surat Pengiriman Barang: '.$this->spb->nomor_pengiriman_barang,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.surat-pengiriman-barang',
            with: [
                'data' => $this->spb,
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

        $data = SuratPengirimanBarang::with([
            'pesananPembelian.pelanggan',
            'pesananPembelian',
            'pesananPenjualan.pelanggan',
            'pesananPenjualan',
            'suratPengirimanBarangDetail.pesananPembelianDetail',
            'suratPengirimanBarangDetail.pesananPenjualanDetail',
        ])->find($this->spb->id);

        $profileUser = $this->user;

        $pdf = Pdf::loadView(
            'administrasi.surat.surat-pengiriman-barang.template-pdf',
            compact('data', 'profileUser'),
        )->setPaper('A4', 'portrait');

        $attachments[] = Attachment::fromData(
            fn () => $pdf->output(),
            Str::slug('surat-pengiriman-barang-'.$this->spb->nomor_pengiriman_barang).'.pdf'
        )->withMime('application/pdf');

        return $attachments;
    }
}
