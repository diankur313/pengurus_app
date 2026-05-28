<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteUserMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Undangan Bergabung — Pengurus Yisc Al Azhar',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invite',
            with: [
                'userName'     => $this->user->name,
                'userEmail'    => $this->user->email,
                'userPassword' => $this->plainPassword,
                'appUrl'       => config('app.url'),
                'appName'      => config('app.name'),
                'logoUrl'      => config('app.url') . '/yisclogo.png',
            ]
        );
    }
}
