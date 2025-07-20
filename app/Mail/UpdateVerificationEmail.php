<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpdateVerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationLink;
    public $email;
    public $new_email;


    public function __construct(string $newData, string $email ,string $verificationLink)
    {
        $this->verificationLink = $verificationLink;
        $this->email            = $email;
        $this->new_email        = $newData;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '變更確認信',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.update_verification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}