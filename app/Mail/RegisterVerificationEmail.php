<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterVerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationLink;
    public $userName;


    public function __construct(string $verificationLink, string $userName)
    {
        $this->verificationLink = $verificationLink;
        $this->userName = $userName;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '開通帳號信',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.register_verification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
