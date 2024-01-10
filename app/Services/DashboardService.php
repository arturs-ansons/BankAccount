<?php
namespace App\Services;

use App\Models\Crypto;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function getUserBalances(User $user): array
    {
        $eurBalance = $user->balances()->where('currency', 'eur')->value('balance');
        $usdBalance = $user->balances()->where('currency', 'usd')->value('balance');
        $invBalance = $user->balances()->where('currency', 'inv')->value('balance');

        $cryptoBalance = $user->balances()->where('currency', 'btc')->value('balance');
        $xrpBalance = $user->balances()->where('currency', 'xrp')->value('balance');
        $ethBalance = $user->balances()->where('currency', 'eth')->value('balance');

        $eurBalanceIban = $user->balances()->where('currency', 'eur')->first();
        $eurIban = optional($eurBalanceIban)->iban;

        $usdBalanceIban = $user->balances()->where('currency', 'usd')->first();
        $usdIban = optional($usdBalanceIban)->iban;

        $invBalanceIban = $user->balances()->where('currency', 'inv')->first();
        $invIban = optional($invBalanceIban)->iban;

        return compact(
            'eurBalance',
            'usdBalance',
            'invBalance',
            'cryptoBalance',
            'xrpBalance',
            'ethBalance',
            'eurIban',
            'usdIban',
            'invIban',
        );
    }

    public function getBtcPriceDetails(User $user): array
    {
        $btcAvgPrice = $this->getAvgBtcPriceFromBalance($user);
        $btcCurrentPrice = $this->getCryptoUsdRate('BTC');
        $btcPercentage = $this->getPercentOfBtcPriceChange($user);

        return compact('btcAvgPrice', 'btcCurrentPrice', 'btcPercentage');
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
}
