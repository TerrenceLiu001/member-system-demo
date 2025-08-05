<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordVerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationLink;


    public function __construct(string $verificationLink, string $userName)
    {
        $this->verificationLink = $verificationLink;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '重設密碼',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot_password',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}