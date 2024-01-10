<?php

namespace App\Services;

use App\Models\User;

class CurrencyService
{
    public function addCurrency(User $user, string $newCurrency, string $iban): void
    {
        if (!$user->balances()->where('currency', $newCurrency)->exists()) {
            $user->balances()->create([
                'account_type' => $newCurrency,
                'currency' => $newCurrency,
                'iban' => $iban,
            ]);
        }
    }
}
