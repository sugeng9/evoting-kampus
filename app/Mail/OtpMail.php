<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param string $otp  Kode OTP 6 digit dalam plaintext (hanya untuk dikirim ke email)
     * @param string $nim  NIM pemilih (untuk personalisasi email)
     */
    public function __construct(
        public readonly string $otp,
        public readonly string $nim,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PEMIRA POLBENG] Kode OTP Pendaftaran Pemilih',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'nim' => $this->nim,
            ],
        );
    }
}
