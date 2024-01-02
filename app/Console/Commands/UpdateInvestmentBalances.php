<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateInvestmentBalances extends Command
{
    protected $signature = 'balances:update-investment';

    protected $description = 'Update investment balances';

    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            $investmentBalance = $user->balances()
                ->where('currency', 'inv')
                ->where('account_type', 'inv')
                ->first();

            if ($investmentBalance) {
                $elapsedHours = Carbon::now()->diffInHours($investmentBalance->created_at);

                $growthRate = 0.02;
                $newBalance = $investmentBalance->balance * (1 + $growthRate) ** $elapsedHours;

                $investmentBalance->update(['balance' => $newBalance]);
            }
        }

        $this->info('Investment balances updated successfully.');
    }


}
