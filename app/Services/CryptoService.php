<?php

namespace App\Services;

use App\Models\Crypto;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Balance;
use App\Traits\BalanceTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CryptoService
{
    use BalanceTrait;

    public function buyCrypto(User $user, float $amount, string $buyCurrency): void
    {
        try {
            DB::beginTransaction();

            $usdBalance = $this->getUserBalance($user, 'inv');

            if ($this->isInsufficientBalance($usdBalance, $amount) || !$usdBalance) {
                Log::channel('buy-crypto')->info('Crypto purchase failed: Insufficient USD balance or USD account not found.');
                throw new \Exception('Crypto purchase failed: Insufficient USD balance or USD account not found.');
            }

            $cryptoBalance = $this->getUserBalance($user, $buyCurrency);

            if (!$cryptoBalance) {
                Log::channel('buy-crypto')->info('Crypto purchase failed: Crypto account not found.');
                throw new \Exception('Crypto purchase failed: Crypto account not found.');
            }

            $recipient = $this->findRecipient($buyCurrency);

            $this->decrementBalance($usdBalance, $amount);
            $this->incrementBalance($cryptoBalance, $amount);

            Transaction::create([
                'user_id' => $user->id,
                'balance_id' => $cryptoBalance->id,
                'amount' => $amount,
                'iban' => $recipient->balances()->where('currency', $buyCurrency)->value('iban'),
                'type' => 'buy_crypto',
            ]);

            DB::commit();

            Log::channel('buy-crypto')->info('Crypto purchase successful: ' . $amount . ' ' . $buyCurrency . ' bought by user ' . $user->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('buy-crypto')->error('Crypto purchase failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sellCrypto(User $user, float $cryptoAmount, string $sellCurrency): void
    {
        try {
            DB::beginTransaction();

            $cryptoBalance = $this->getUserBalance($user, $sellCurrency);

            if ($this->isInsufficientBalance($cryptoBalance, $cryptoAmount) || !$cryptoBalance) {
                Log::channel('sell-crypto')->info('Crypto sale failed: Insufficient crypto balance or crypto account not found.');
                throw new \Exception('Crypto sale failed: Insufficient crypto balance or crypto account not found.');
            }

            $usdAmount = $this->calculateUsdAmount($cryptoAmount, $sellCurrency);

            $usdBalance = $this->getUserBalance($user, 'inv');
            $iban = optional($usdBalance)->iban;

            $this->decrementBalance($cryptoBalance, $cryptoAmount);
            $this->incrementBalance($usdBalance, $usdAmount);

            Transaction::create([
                'user_id' => $user->id,
                'balance_id' => $cryptoBalance->id,
                'amount' => $cryptoAmount,
                'iban' => $iban,
                'type' => 'sell_crypto',
            ]);

            DB::commit();

            Log::channel('sell-crypto')->info('Crypto sale successful: ' . $cryptoAmount . ' ' . $sellCurrency . ' sold by user ' . $user->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('sell-crypto')->error('Crypto sale failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getCryptoUsdRate(string $cryptoName): float
    {
        try {
            $crypto = Crypto::where('crypto_name', strtoupper($cryptoName))->first();

            if ($crypto) {
                return $crypto->usd_rate;
            } else {
                Log::info('Invalid exchange rate for ' . $cryptoName);
                return 0.0;
            }
        } catch (\Exception $e) {
            Log::error('Error in getCryptoUsdRate: ' . $e->getMessage());
            return 0.0;
        }
    }

    private function findRecipient(string $currency): ?User
    {

        return User::whereHas('balances', function ($query) use ($currency) {
            $query->where('currency', $currency);
        })->first();
    }

    private function getUserBalance(User $user, string $accountType): ?Balance
    {
        return Balance::where('account_type', $accountType)
            ->where('user_id', $user->id)
            ->first();
    }

    private function calculateUsdAmount(float $cryptoAmount, string $sellCurrency): float
    {
        $cryptoUsdRate = $this->getCryptoUsdRate($sellCurrency);

        if (is_numeric($cryptoAmount) && is_numeric($cryptoUsdRate) && $cryptoUsdRate > 0) {
            return $cryptoAmount * $cryptoUsdRate;
        }

        Log::channel('sell-crypto')->error('Crypto sale failed: Invalid exchange rate or amount.');
        throw new \Exception('Crypto sale failed: Invalid exchange rate or amount.');
    }

}
