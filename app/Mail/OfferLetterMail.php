<?php

namespace App\Mail;

use App\Models\OfferLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OfferLetter $offerLetter,
        public string      $pdfContent,
        public string      $filename,
        public string      $companyName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Offer Letter — ' . $this->offerLetter->designation . ' | ' . $this->companyName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-letter',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
