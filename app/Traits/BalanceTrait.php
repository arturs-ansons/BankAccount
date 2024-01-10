<?php

namespace App\Traits;

use App\Models\Balance;

trait BalanceTrait
{
    private function isInsufficientBalance(?Balance $balance, float $amount): bool
    {
        return !$balance || $balance->balance < $amount;
    }

    private function decrementBalance(?Balance $balance, float $amount): void
    {
        $balance?->decrement('balance', $amount);
    }

    private function incrementBalance(?Balance $balance, float $amount): void
    {
        $balance?->increment('balance', $amount);
    }
}
