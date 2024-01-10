<?php

namespace App\Services;

use App\Models\ApiCurrency;
use App\Models\Balance;
use App\Models\User;
use App\Models\Transaction;
use App\Traits\BalanceTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    use BalanceTrait;
    /**
     * @throws \Exception
     */
    public function transferMoney(User $user, Balance $senderBalance, float $amount, string $iban, string $transferCurrency, User $recipient): void
    {
        try {
            DB::beginTransaction();

            $userBalance = $this->getUserBalance($user, 'USD');
            $userInvestmentBalance = $this->getUserInvestmentBalance($user, $transferCurrency, $iban);

            if ($this->isInsufficientBalance($userBalance, $amount) || !$this->userHasCurrency($user, $transferCurrency)) {
                Log::channel('transfer')->info('Transfer failed: Insufficient balance or invalid currency.');
                throw new \Exception('Transfer failed: Insufficient balance or invalid currency.');
            }

            if ($userInvestmentBalance) {
                $this->decrementBalance($userBalance, $amount);
                $this->incrementBalance($userInvestmentBalance, $amount);
            }

            $recipientBalance = $this->getUserBalance($recipient, $transferCurrency);

            if (!$recipientBalance) {
                Log::channel('transfer')->info('Recipient balance not found.');
                throw new \Exception('Recipient balance not found.');
            }
            $this->transferToRecipient($recipient, $amount, $transferCurrency);
            $this->createTransaction($user, $userInvestmentBalance ?? $userBalance, $amount, $iban, 'transfer', $recipient);

            $this->decrementBalance($user->balances()->where('currency', $transferCurrency)->first(), $amount);
            $this->incrementBalance($recipientBalance, $amount);

            DB::commit();

            Log::channel('transfer')->info('Transfer successful: ' . $amount . ' ' . $transferCurrency . ' transferred from accNr: ' . $user->id . ' to accNr: ' . $recipient->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('transfer')->error('Transfer failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createTransaction(User $user, Balance $balance, float $amount, string $iban, string $type, User $recipient): void
    {
        Transaction::create([
            'user_id' => $user->id,
            'balance_id' => $balance->id,
            'amount' => $amount,
            'iban' => $iban,
            'type' => $type,
            'recipient_id' => $recipient->id,
        ]);
    }

    private function getUserInvestmentBalance(User $user, string $currency, string $iban): ?object
    {
        return $user->balances()
            ->where('currency', $currency)
            ->where('iban', $iban)
            ->first();
    }

    private function userHasCurrency(User $user, string $currency): bool
    {
        return $user->balances()->where('currency', $currency)->exists();
    }

    private function getUserBalance(User $user, string $accountType): ?Balance
    {
        return Balance::where('account_type', $accountType)
            ->where('user_id', $user->id)
            ->first();
    }


    private function transferToRecipient(?User $recipient, float $amount, string $transferCurrency): void
    {
        if (!$recipient) {
            Log::error('Recipient is null. Transfer cannot be completed.');
            return;
        }

        $recipientBalance = $recipient->balances()->where('currency', $transferCurrency)->first();

        if ($recipientBalance) {
            $this->incrementBalance($recipientBalance, $amount);
        } else {
            $this->handleRecipientWithoutCurrency($recipient, $amount, $transferCurrency);
        }
    }

    private function handleRecipientWithoutCurrency(User $recipient, float $amount, string $transferCurrency): void
    {
        $exchangeRate = ($transferCurrency === 'eur') ? $this->getExchangeRate() : 1.0;
        $convertedAmount = $amount * $exchangeRate;

        $recipientUsdBalance = $recipient->balances()->where('currency', 'usd')->first();

        if ($recipientUsdBalance) {
            $this->incrementBalance($recipientUsdBalance, $convertedAmount);
        }
    }

    private function getExchangeRate(): float
    {
        $currency = ApiCurrency::where('symbol', 'usd')->first();
        return $currency->rate;
    }

    public function findRecipientByIban(string $iban): ?User
    {
        return User::whereHas('balances', function ($query) use ($iban) {
            $query->where('iban', $iban);
        })->first();
    }

}
