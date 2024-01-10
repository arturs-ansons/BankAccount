<?php

namespace App\Services;

use App\Models\Balance;

class IbanService
{
    public function generateUniqueIban(): string
    {
        $iban = 'LV' . rand(1000000000, 9999999999);

        while (Balance::where('iban', $iban)->exists()) {
            $iban = 'LV' . rand(1000000000, 9999999999);
        }

        return $iban;
    }
}
