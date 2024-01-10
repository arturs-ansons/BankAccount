<?php

namespace App\Http\Controllers;

use App\Services\CryptoService;
use App\Services\CurrencyService;
use App\Services\DashboardService;
use App\Services\IbanService;
use App\Services\TransactionService;
use App\Services\TransferService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class AccountController extends Controller
{
    private DashboardService $dashboardService;
    private CurrencyService $currencyService;
    private CryptoService $cryptoService;
    private TransferService $transferService;
    private TransactionService $transactionService;
    private IbanService $ibanService;

    public function __construct(
        CurrencyService $currencyService,
        CryptoService $cryptoService,
        TransferService $transferService,
        DashboardService $dashboardService,
        TransactionService $transactionService,
        IbanService $ibanService,
    ) {
        $this->currencyService = $currencyService;
        $this->cryptoService = $cryptoService;
        $this->transferService = $transferService;
        $this->dashboardService = $dashboardService;
        $this->transactionService = $transactionService;
        $this->ibanService = $ibanService;
    }

    public function addCurrency(Request $request): RedirectResponse
    {
        $request->validate([
            'newCurrency' => 'required|in:' . implode(',', config('currencies.allowed')),
        ]);

        $user = auth()->user();
        $newCurrency = $request->input('newCurrency');
        $iban = $this->ibanService->generateUniqueIban();

        $this->currencyService->addCurrency($user, $newCurrency, $iban);

<<<<<<< HEAD
=======
            $user->balances()->create([
                'account_type' => $request->input('newCurrency'),
                'currency' => $request->input('newCurrency'),
                //'balance' => 100,
                'iban' => $iban,
            ]);
        }
>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
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
        $amount = $request->input('amount');
        $transferCurrency = $request->input('transferCurrency');
        $iban = $request->input('iban');

        try {
            $recipient = $this->transferService->findRecipientByIban($iban);

            $this->transferService->transferMoney($user, $user->balances()->where('currency', 'USD')->first(), $amount, $iban, $transferCurrency, $recipient);

            $request->session()->flash('success', 'Transfer successful: ' . $amount . ' ' . $transferCurrency . ' to IBAN: ' . $iban);
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Transfer failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard');
    }

<<<<<<< HEAD
=======
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

>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
    public function buyCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
<<<<<<< HEAD
            'buyCurrency' => 'required|in:' . implode(',', config('currencies.allowed')),
=======
            'buyCurrency' => 'required|in:BTC,ETH,XRP',
>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
        ]);

        $user = auth()->user();
        $usdAmount = $request->input('amount');
        $buyCurrency = $request->input('buyCurrency');

        try {
<<<<<<< HEAD
            $this->cryptoService->buyCrypto($user, $usdAmount, $buyCurrency);

            $request->session()->flash('success', 'Crypto purchase successful: ' . $usdAmount . ' USD converted to ' . $buyCurrency);
=======
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
>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Crypto purchase failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard');
    }

<<<<<<< HEAD
=======

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

>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
    public function sellCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.00000001',
            'sellCurrency' => 'required|in:' . implode(',', config('currencies.allowed')),
        ]);

        $user = auth()->user();
        $cryptoAmount = $request->input('amount');
        $sellCurrency = $request->input('sellCurrency');

        try {
            $this->cryptoService->sellCrypto($user, $cryptoAmount, $sellCurrency);

            $request->session()->flash('success', 'Crypto sale successful: ' . $cryptoAmount . ' ' . $sellCurrency . ' sold.');
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Crypto sale failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard');
    }

<<<<<<< HEAD
    public function transactions(): Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
=======

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
>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
    {
        $user = auth()->user();
        $transactions = $this->transactionService->getUserTransactions($user);

        return view('page.transactions', ['transactions' => $transactions]);
    }

    public function success(): Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = auth()->user();

        $balances = $this->dashboardService->getUserBalances($user);
        $btcPriceDetails = $this->dashboardService->getBtcPriceDetails($user);
        extract($balances);

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
<<<<<<< HEAD
            'btcCurrentPrice' => number_format($btcPriceDetails['btcCurrentPrice'], 12),
            'btcAvgPrice' => number_format($btcPriceDetails['btcAvgPrice'], 12),
            'btcPercentage' => number_format($btcPriceDetails['btcPercentage'], 3),
=======
            'btcCurrentPrice' => number_format($btcCurrentPrice, 12),
            'btcAvgPrice' => number_format($btcAvgPrice, 12),
            'btcPercentage' => number_format($btcPercentage, 3),
>>>>>>> fee700db2235c29a28b6bcf09ce4b8691e0628f7
        ]);
    }
}
