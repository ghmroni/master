<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailSend extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $datas;
    public $subject;
    public $template;
    public $fromName;
    /**
     * Create a new message instance.
     */
    public function __construct($datas, $subject, $template, $fromName = null)
    {
        $this->datas = $datas;
        $this->subject = $subject;
        $this->template = $template;
        $this->fromName = $fromName ?? config('mail.from.name');
    }

    // public function build()
    // {

    //     return $this->subject($this->subject)
    //         ->view($this->template)
    //         ->with(['data' => $this->datas]);
    // }

    public function build()
    {
        // Tetap ambil email resmi dari .env secara otomatis di latar belakang
        $automaticEmail = config('mail.from.address');

        return $this->from($automaticEmail, $this->fromName)
            ->subject($this->subject)
            ->view($this->template)
            ->with(['data' => $this->datas]);
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Mail Send',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

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
