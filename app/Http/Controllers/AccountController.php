<?php

namespace App\Http\Controllers;

use App\Models\ApiCurrency;
use App\Models\Balance;
use App\Models\Crypto;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AccountController extends Controller
{

    public function addCurrency(Request $request): RedirectResponse
    {
        $request->validate([
            'newCurrency' => 'required|in:eur,usd,inv,btc', // Validate the chosen currency
        ]);

        $user = auth()->user();

        if (!$user->balances()->where('currency', $request->input('newCurrency'))->exists()) {

            $user->balances()->create([
                'account_type' => $request->input('newCurrency'),
                'currency' => $request->input('newCurrency'),
                //'balance' => 100, // Initial balance for the new currency
            ]);
        }

        return redirect()->route('clientAccount');
    }

    public function transferMoney(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'transferCurrency' => 'required|in:eur,usd,inv',
        ]);

        $user = auth()->user();
        $recipientId = $request->input('accountNr');
        $amount = $request->input('amount');
        $transferCurrency = $request->input('transferCurrency');
        $accountType = 'EUR';

        try {
            DB::beginTransaction();

            $userBalance = $this->getUserBalance($user, $accountType);
            $userInvestmentBalance = $this->getUserInvestmentBalance($user, $transferCurrency);

            if ($this->isInsufficientBalance($userBalance, $amount) || !$this->userHasCurrency($user, $transferCurrency)) {
                Log::info('Transfer failed: Insufficient balance or invalid currency.');
                return redirect()->route('clientAccount');
            }

            if ($userInvestmentBalance) {
                $this->decrementBalance($userBalance, $amount);
                $this->incrementBalance($userInvestmentBalance, $amount);
            }

            $recipient = User::find($recipientId);
            $this->decrementBalance($user->balances()->where('currency', $transferCurrency)->first(), $amount);
            $this->transferToRecipient($recipient, $amount, $transferCurrency);

            DB::commit();

            Log::info('Transfer successful: ' . $amount . ' ' . $transferCurrency . ' transferred from accNr: ' . $user->id . ' to accNr: ' . $recipientId);
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Transfer failed: ' . $e->getMessage());

            return redirect()->route('clientAccount');
        }

        return redirect()->route('clientAccount');
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

    private function isInsufficientBalance(?Balance $balance, float $amount): bool
    {
        return !$balance || $balance->balance < $amount;

    }

    private function getUserInvestmentBalance(User $user, string $currency): object
    {
        return $user->balances()
            ->where('currency', $currency)
            ->where('account_type', 'inv')
            ->first();
    }

    private function decrementBalance(?Balance $balance, float $amount): void
    {
        if ($balance) {
            $balance->decrement('balance', $amount);
        }
    }

    private function incrementBalance(?Balance $balance, float $amount): void
    {
        if ($balance) {
            $balance->increment('balance', $amount);
        }
    }

    private function transferToRecipient(User $recipient, float $amount, string $transferCurrency): void
    {
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
        return (float) $currency->rate;
    }

    public function buyCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'buyCurrency' => 'required|in:usd',
        ]);

        $user = auth()->user();
        $usdAmount = $request->input('amount');

        try {
            DB::beginTransaction();

            $usdBalance = $user->balances()->where('currency', 'usd')->first();
            if ($this->isInsufficientBalance($usdBalance, $usdAmount)) {
                Log::info('Crypto purchase failed: Insufficient USD balance.');
                return redirect()->route('clientAccount');
            }

            // Retrieve the exchange rate for the specified cryptocurrency (e.g., BTC to USD)
            $cryptoUsdRate = $this->getCryptoUsdRate('BTC'); // Replace 'BTC' with the correct cryptocurrency symbol

            Log::info('USD Amount: ' . $usdAmount);
            Log::info('Crypto USD Rate: ' . $cryptoUsdRate);

            if (is_numeric($usdAmount) && is_numeric($cryptoUsdRate) && $cryptoUsdRate > 0) {
                
                $cryptoAmount = $usdAmount * $cryptoUsdRate;

                $cryptoAmount = number_format($cryptoAmount, 8, '.', '');

                $this->updateAvgBtcPrice($user, $cryptoAmount);

                Log::info('Crypto Amount: ' . $cryptoAmount);

                $this->decrementBalance($usdBalance, $usdAmount);

                $cryptoBalance = $user->balances()->where('currency', 'BTC')->first();
                $this->incrementBalance($cryptoBalance, $cryptoAmount);

                DB::commit();

                Log::info('Crypto purchase successful: ' . $cryptoAmount . ' BTC purchased by user ' . $user->id);
            } else {
                Log::info('Crypto purchase failed: Invalid exchange rate or amount.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crypto purchase failed: ' . $e->getMessage());
        }

        return redirect()->route('clientAccount');
    }

    private function updateAvgBtcPrice(User $user, $cryptoAmount): float
    {
        try {
            DB::beginTransaction();

            $cryptoUsdRate = $this->getCryptoUsdRate('BTC');

            Log::info('Crypto USD Rate for updateAvgBtcPrice: ' . $cryptoUsdRate);

            if ($cryptoUsdRate > 0) {
                $avgBtcPrice = $cryptoUsdRate;

                Log::info('New Avg BTC Price: ' . $avgBtcPrice);

                $user->balances()->updateOrInsert(
                    ['currency' => 'btc', 'user_id' => $user->id],
                    ['avgBtcPrice' => $avgBtcPrice]
                );

                DB::commit();
            } else {
                Log::info('Invalid exchange rate for BTC');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in updateAvgBtcPrice: ' . $e->getMessage());
        }
        return $avgBtcPrice;
    }

   private function getCryptoUsdRate(string $cryptoCurrency): float
    {
        try {
            $crypto = Crypto::where('currency', 'USD')->first();
            if ($crypto && $crypto->btc_rate > 0) {
                return (float) $crypto->btc_rate;
            } else {
                Log::info('Invalid exchange rate for ' . $cryptoCurrency);
                return 0.0;
            }
        } catch (\Exception $e) {
            Log::error('Error in getCryptoUsdRate: ' . $e->getMessage());
            return 0.0;
        }
    }

    private function getAvgBtcPriceFromBalance(User $user): float
    {

        $avgBtcPrice = $user->balances()->where('currency', 'btc')->value('avgBtcPrice');

        return $avgBtcPrice ?: 0;
    }

    public function success(): Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = auth()->user();
        $eurBalance = $user->balances()->where('currency', 'eur')->value('balance');
        $usdBalance = $user->balances()->where('currency', 'usd')->value('balance');
        $invBalance = $user->balances()->where('currency', 'inv')->value('balance');
        $cryptoBalance = $user->balances()->where('currency', 'btc')->value('balance');
        $btcAvgPrice = $this->getAvgBtcPriceFromBalance($user);
        $btcCurrentPrice = $this->getCryptoUsdRate('BTC');


        return view('page.clientAccount', [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'eurBalance' => $eurBalance,
            'usdBalance' => $usdBalance,
            'invBalance' => $invBalance,
            'cryptoBalance' => $cryptoBalance,
            'btcCurrentPrice' => number_format($btcCurrentPrice, 12),
            'btcAvgPrice' => number_format($btcAvgPrice, 12),
        ]);
    }

}
