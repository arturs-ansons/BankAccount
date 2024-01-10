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

    public function buyCrypto(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'buyCurrency' => 'required|in:' . implode(',', config('currencies.allowed')),
        ]);

        $user = auth()->user();
        $usdAmount = $request->input('amount');
        $buyCurrency = $request->input('buyCurrency');

        try {
            $this->cryptoService->buyCrypto($user, $usdAmount, $buyCurrency);

            $request->session()->flash('success', 'Crypto purchase successful: ' . $usdAmount . ' USD converted to ' . $buyCurrency);
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Crypto purchase failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard');
    }

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

    public function transactions(): Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
            'btcCurrentPrice' => number_format($btcPriceDetails['btcCurrentPrice'], 12),
            'btcAvgPrice' => number_format($btcPriceDetails['btcAvgPrice'], 12),
            'btcPercentage' => number_format($btcPriceDetails['btcPercentage'], 3),
        ]);
    }
}
