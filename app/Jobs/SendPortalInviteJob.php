<?php

namespace App\Jobs;

use App\Mail\InvitePortalMail;
use App\Models\CivitasPendidikan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPortalInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max retry attempts.
     */
    public int $tries = 3;

    /**
     * Delay (seconds) before retry.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(public CivitasPendidikan $civitas) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = $this->civitas->email;

        if (empty($email)) {
            Log::warning("Cannot send portal invite: CivitasPendidikan ID {$this->civitas->id} has no email address.");
            return;
        }

        // Send portal invite email
        Mail::to($email)->send(new InvitePortalMail($this->civitas));
    }
}
