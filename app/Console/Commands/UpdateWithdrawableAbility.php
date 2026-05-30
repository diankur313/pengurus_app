<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateWithdrawableAbility extends Command
{
    protected $signature   = 'payment:update-withdrawable';
    protected $description = 'Update withdrawable_ability to true for payments settled H+3 working days';

    public function handle(): int
    {
        $today = Carbon::today();

        // Get payments that are PAID, not yet withdrawable, and have a settle_date
        $payments = Payment::where('status', 'PAID')
            ->where('withdrawable_ability', false)
            ->whereNotNull('settle_date')
            ->get();

        $updated = 0;

        foreach ($payments as $payment) {
            $settleDate = Carbon::parse($payment->settle_date);
            $releaseDate = Holiday::addWorkingDays($settleDate, 3);

            if ($today->gte($releaseDate)) {
                $payment->update([
                    'withdrawable_ability' => true,
                    'withdrawable'         => (float) $payment->amount - (float) $payment->fee_pg - (float) $payment->fee_sysdev,
                ]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} payment(s) to withdrawable.");
        return Command::SUCCESS;
    }
}
