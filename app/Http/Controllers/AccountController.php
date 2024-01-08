<?php

namespace App\Http\Controllers;

use App\Models\ApiCurrency;
use App\Models\Balance;
use App\Models\Crypto;
use App\Models\Transaction;
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
            'newCurrency' => 'required|in:' . implode(',', config('currencies.allowed')),
        ]);

        $user = auth()->user();

        if (!$user->balances()->where('currency', $request->input('newCurrency'))->exists()) {
            $iban = $this->generateUniqueIban();

            $user->balances()->create([
                'account_type' => $request->input('newCurrency'),
                'currency' => $request->input('newCurrency'),
                //'balance' => 100,
                'iban' => $iban,
            ]);
        }
        $request->session()->flash('success', 'Currency Account successfully created.');
        return redirect()->route('dashboard');
    }

    public function transferMoney(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'transferCurrency' => 'required|in:' . implode(',', config('currencies.allowed')),
            'iban' => 'required',
        ]);

        $user = auth()->user();
        $iban = $request->input('iban');
        $amount = $request->input('amount');
        $transferCurrency = $request->input('transferCurrency');
        $accountType = 'USD';

        try {
            DB::beginTransaction();

            $userBalance = $this->getUserBalance($user, $accountType);
            $userInvestmentBalance = $this->getUserInvestmentBalance($user, $transferCurrency, $iban);

            if ($this->isInsufficientBalance($userBalance, $amount) || !$this->userHasCurrency($user, $transferCurrency)) {
                Log::channel('transfer')->info('Transfer failed: Insufficient balance or invalid currency.');
                $request->session()->flash('error', 'Transfer failed: Insufficient balance or invalid currency.');
                return redirect()->route('dashboard');
            }

            if ($userInvestmentBalance) {
                $this->decrementBalance($userBalance, $amount);
                $this->incrementBalance($userInvestmentBalance, $amount);
            }

            $recipient = User::whereHas('balances', function ($query) use ($iban) {
                $query->where('iban', $iban);
            })->first();

            if (!$recipient) {
                $request->session()->flash('error', 'IBAN not found.');

                return redirect()->route('dashboard');
            }

            $balanceForTransaction = $userInvestmentBalance ? $userInvestmentBalance : $userBalance;

            Transaction::create([
                'user_id' => $user->id,
                'balance_id' => $balanceForTransaction->id,
                'amount' => $amount,
                'iban' => $iban,
                'type' => 'transfer',
                'recipient_id' => $recipient->id,
            ]);


            $this->decrementBalance($user->balances()->where('currency', $transferCurrency)->first(), $amount);
            $this->transferToRecipient($recipient, $amount, $transferCurrency);

            DB::commit();

            Log::channel('transfer')->info('Transfer successful: ' . $amount . ' ' . $transferCurrency . ' transferred from accNr: ' . $user->id . ' to accNr: ' . $recipient->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('transfer')->error('Transfer failed: ' . $e->getMessage());

            $request->session()->flash('error', 'Transfer failed.');

            return redirect()->route('dashboard');
        }
        $request->session()->flash('success', 'Transfer successful: ' . $amount . ' ' . $transferCurrency . '  To IBAN: ' . $iban);

        return redirect()->route('dashboard');
    }

    private function getUserInvestmentBalance(User $user, string $currency, string $iban): ?object
    {
        $balance = $user->balances()
            ->where('currency', $currency)
            ->where('iban', $iban)
            ->first();

        return $balance;
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

    private function generateUniqueIban(): string
    {
        $iban = 'LV' . rand(1000000000, 9999999999);

        while (Balance::where('iban', $iban)->exists()) {
            $iban = 'LV' . rand(1000000000, 9999999999);
        }

        return $iban;
    }

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

    private function transferToRecipient(?User $recipient, float $amount, string $transferCurrency): void
    {

        if (!$recipient) {
            \Log::error('Recipient is null. Transfer cannot be completed.');
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

    public function buyCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'buyCurrency' => 'required|in:BTC,ETH,XRP',
        ]);

        $user = auth()->user();
        $usdAmount = $request->input('amount');
        $buyCurrency = $request->input('buyCurrency');

        try {
            DB::beginTransaction();

            $usdBalance = $user->balances()->where('currency', 'inv')->first();
            $iban = optional($usdBalance)->iban;

            $cryptoAcc = $user->balances()->where('currency', strtolower($buyCurrency))->first();


            if ($this->isInsufficientBalance($usdBalance, $usdAmount) || !$cryptoAcc) {
                Log::channel('buy-crypto')->info('Crypto purchase failed: Insufficient Investment balance or crypto account not found.');
                $request->session()->flash('error', 'Crypto purchase failed: Insufficient Investment balance or crypto account not found.');
                return redirect()->route('dashboard');
            }

            $cryptoUsdRate = $this->getCryptoUsdRate(strtoupper($buyCurrency));

            Log::channel('buy-crypto')->info('USD Amount: ' . $usdAmount);
            Log::channel('buy-crypto')->info($buyCurrency . ' USD Rate: ' . $cryptoUsdRate);

            if (is_numeric($usdAmount) && is_numeric($cryptoUsdRate) && $cryptoUsdRate > 0) {
                $cryptoAmount = $usdAmount * $cryptoUsdRate;
                $cryptoAmount = number_format($cryptoAmount, 8, '.', '');
if($cryptoAcc === 'btc'){
    $this->updateAvgBtcPrice($user, $cryptoAmount);
}


                Log::channel('buy-crypto')->info($buyCurrency . ' Amount: ' . $cryptoAmount);

                $this->decrementBalance($usdBalance, $usdAmount);

                $recipient = User::whereHas('balances', function ($query) use ($buyCurrency) {
                    $query->where('account_type', strtolower($buyCurrency));
                })->first();

                if (!$recipient) {
                    $request->session()->flash('error', 'Recipient not found.');
                }

                $query = $recipient->balances()->where('currency', strtolower($buyCurrency));

                Log::channel('buy-crypto')->info('Query: ' . $query->toSql());

                $cryptoBalance = $query->first();

                if (!$cryptoBalance) {
                    Log::channel('buy-crypto')->error('Recipient ' . $buyCurrency . ' balance not found.');
                    throw new \Exception('Recipient ' . $buyCurrency . ' balance not found.');
                }

                Log::channel('buy-crypto')->info('Before increment: ' . $cryptoBalance->balance);

                $this->incrementBalance($cryptoBalance, $cryptoAmount);

                Log::channel('buy-crypto')->info('After increment: ' . $cryptoBalance->balance);
                $request->session()->flash('success', 'Crypto purchase successful: ' . $cryptoAmount . ' ' . $buyCurrency);

                Transaction::create([
                    'user_id' => $user->id,
                    'balance_id' => $usdBalance->id,
                    'amount' => $cryptoAmount,
                    'iban' => $iban,
                    'type' => 'buy_crypto',
                    'recipient_id' => $recipient->id,
                ]);

                DB::commit();

                Log::channel('buy-crypto')->info('Crypto purchase successful: ' . $cryptoAmount . ' ' . $buyCurrency . ' purchased by user ' . $user->id);
            } else {
                Log::channel('buy-crypto')->error('Crypto purchase failed: Invalid exchange rate or amount.');
                $request->session()->flash('error', 'Crypto purchase failed: Invalid exchange rate or amount.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('buy-crypto')->error('Crypto purchase failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard');
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
                    ['avgBtcPrice' => $avgBtcPrice, 'account_type' => 'btc']
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

    public function sellCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.000000001',
            'sellCurrency' => 'required|in:btc,eth,xrp',
        ]);

        $user = auth()->user();
        $cryptoCurrency = $request->input('sellCurrency');
        $cryptoAmount = $request->input('amount');

        try {
            DB::beginTransaction();

            // Fetch the cryptocurrency balance for the user
            $cryptoBalance = $user->balances()->where('currency', $cryptoCurrency)->first();
            $iban = optional($cryptoBalance)->iban;

            if ($this->isInsufficientBalance($cryptoBalance, $cryptoAmount)) {
                Log::channel('sell-crypto')->error("Crypto sell failed: Insufficient {$cryptoCurrency} balance for user {$user->id}");
                $request->session()->flash('error', "Crypto sell failed: Insufficient {$cryptoCurrency} balance for user");
                return redirect()->route('dashboard');
            }

            $cryptoUsdRate = $this->getCryptoUsdRate($cryptoCurrency);

            Log::channel('sell-crypto')->info("{$cryptoCurrency} Amount: {$cryptoAmount}");
            Log::channel('sell-crypto')->info("Crypto USD Rate for {$cryptoCurrency}: {$cryptoUsdRate}");

            if (is_numeric($cryptoAmount) && is_numeric($cryptoUsdRate) && $cryptoUsdRate > 0) {

                $usdAmount = $cryptoAmount / $cryptoUsdRate;
                $usdAmount = number_format($usdAmount, 2, '.', '');

                $usdBalance = $user->balances()->where('currency', 'inv')->first();
                Log::channel('sell-crypto')->info('Before increment: ' . $usdBalance->balance);

                $this->incrementBalance($usdBalance, $usdAmount);

                Log::channel('sell-crypto')->info('After increment: ' . $usdBalance->balance);

                $this->decrementBalance($cryptoBalance, $cryptoAmount);

                Log::channel('sell-crypto')->info("Crypto sell successful: {$cryptoAmount} {$cryptoCurrency} sold by user {$user->id}");
                $request->session()->flash('success', "Crypto sell successful: {$cryptoAmount} {$cryptoCurrency}");

                Transaction::create([
                    'user_id' => $user->id,
                    'balance_id' => $usdBalance->id,
                    'amount' => $usdAmount,
                    'iban' => $iban,
                    'type' => 'sell_crypto',
                    'recipient_id' => $usdBalance->id,
                ]);

                DB::commit();
            } else {
                Log::channel('sell-crypto')->error("Crypto sell failed: Invalid exchange rate or amount for user {$user->id}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $request->session()->flash('error', 'Crypto sell failed');
            Log::channel('sell-crypto')->error("Crypto sell failed: {$e->getMessage()} for user {$user->id}");
        }

        return redirect()->route('dashboard');
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



    private function getAvgBtcPriceFromBalance(User $user): float
    {

        $avgBtcPrice = $user->balances()->where('currency', 'btc')->value('avgBtcPrice');

        return $avgBtcPrice ?: 0;
    }
    private function getPercentOfBtcPriceChange(User $user): float
    {
        $avgPrice = $this->getAvgBtcPriceFromBalance($user);
        $realBtcPrice = $this->getCryptoUsdRate('BTC');

        if ($avgPrice != 0) {
            $percentageChange = (($avgPrice - $realBtcPrice) / $avgPrice) * 100;
        } else {
            $percentageChange = 0;
        }

        return $percentageChange;
    }

    public function success(): Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = auth()->user();
        $eurBalance = $user->balances()->where('currency', 'eur')->value('balance');
        $usdBalance = $user->balances()->where('currency', 'usd')->value('balance');
        $invBalance = $user->balances()->where('currency', 'inv')->value('balance');

        $cryptoBalance = $user->balances()->where('currency', 'btc')->value('balance');
        $xrpBalance = $user->balances()->where('currency', 'xrp')->value('balance');
        $ethBalance = $user->balances()->where('currency', 'eth')->value('balance');


        $btcAvgPrice = $this->getAvgBtcPriceFromBalance($user);
        $btcCurrentPrice = $this->getCryptoUsdRate('BTC');
        $btcPercentage = $this->getPercentOfBtcPriceChange($user);

        $eurBalanceIban = $user->balances()->where('currency', 'eur')->first();
        $eurIban = optional($eurBalanceIban)->iban;

        $usdBalanceIban = $user->balances()->where('currency', 'usd')->first();
        $usdIban = optional($usdBalanceIban)->iban;

        $invBalanceIban = $user->balances()->where('currency', 'inv')->first();
        $invIban = optional($invBalanceIban)->iban;

        return view('page.dashboard', [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'eurBalance' => $eurBalance,
            'usdBalance' => $usdBalance,
            'eurIban' => $eurIban,
            'usdIban' => $usdIban,
            'invIban' => $invIban,
            'invBalance' => $invBalance,
            'cryptoBalance' => $cryptoBalance,
            'xrpBalance' => $xrpBalance,
            'ethBalance' => $ethBalance,
            'btcCurrentPrice' => number_format($btcCurrentPrice, 12),
            'btcAvgPrice' => number_format($btcAvgPrice, 12),
            'btcPercentage' => number_format($btcPercentage, 3),
        ]);
    }
    public function transactions(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|Application
    {

        $user = auth()->user();

        $transactions = Transaction::where('user_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->with(['balance', 'recipient'])
            ->get();

        $transactions = Transaction::paginate(10);

        return view('page.transactions', ['transactions' => $transactions]);
    }

}
