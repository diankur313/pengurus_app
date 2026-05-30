<?php

namespace App\Mail;

use App\Models\CivitasPendidikan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitePortalMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public CivitasPendidikan $civitas
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎓 Undangan Akses Portal Pembelajaran — e-SII Yisc Al Azhar',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invite-portal',
            with: [
                'userName'      => $this->civitas->name,
                'userEmail'     => $this->civitas->email,
                'angkatan'      => $this->civitas->angkatan,
                'levelAngkatan' => $this->civitas->level_angkatan,
                'portalUrl'     => env('ESII_PORTAL_URL', 'https://e-sii.yiscalazhar.web.id'),
                'logoUrl'       => config('app.url') . '/yisclogo.png',
            ]
        );
    }
}
