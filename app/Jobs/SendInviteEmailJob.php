<?php

namespace App\Jobs;

use App\Mail\InviteUserMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SendInviteEmailJob implements ShouldQueue
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
    public function __construct(public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $plainPassword = $this->generatePassword();

        // Update password user di DB
        $this->user->update(['password' => Hash::make($plainPassword)]);

        // Kirim email undangan
        Mail::to($this->user->email)
            ->send(new InviteUserMail($this->user, $plainPassword));
    }

    /**
     * Generate secure 8-char password.
     * Guarantee: min 2 uppercase + 2 digits + 2 special + 2 mixed, then shuffle.
     */
    private function generatePassword(): string
    {
        $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower   = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@#$!%*?&';
        $all     = $upper . $lower . $numbers . $special;

        $password  = $upper[random_int(0, strlen($upper) - 1)];
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        $password .= $all[random_int(0, strlen($all) - 1)];
        $password .= $all[random_int(0, strlen($all) - 1)];

        return str_shuffle($password);
    }
}
