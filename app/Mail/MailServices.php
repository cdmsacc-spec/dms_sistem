<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailServices extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $url;
    public $ceks;
    public $status;

    public $subj;
    public $datetime;
    public function __construct($nama, $url, $ceks,  $status, $subj, $datetime)
    {
        $this->nama = $nama;
        $this->url = $url;
        $this->ceks = $ceks;
        $this->status = $status;
        $this->subj = $subj;
        $this->datetime = $datetime;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subj?? 'Reminder Status DMS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.reminder',
            with: [
                'nama' => $this->nama,
                'url' => $this->url,
                'ceks' => $this->ceks,
                'status' => $this->status,
                'datetime' => $this->datetime,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
