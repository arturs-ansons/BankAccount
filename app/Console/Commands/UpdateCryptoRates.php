<?php
namespace App\Console\Commands;

use App\Models\Crypto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCryptoRates extends Command
{
    protected $signature = 'fetch:crypto-rates';
    protected $description = 'Update USD to Bitcoin, Ethereum, and Ripple exchange rates from Coinbase';

    public function handle()
    {
        $response = Http::get('https://api.coinbase.com/v2/exchange-rates?currency=USD');
        $data = $response->json();

        $bitcoinRate = $data['data']['rates']['BTC'];
        $ethereumRate = $data['data']['rates']['ETH'];
        $rippleRate = $data['data']['rates']['XRP'];

        // Update or create records for each cryptocurrency
        Crypto::updateOrCreate(
            ['crypto_name' => 'BTC'],
            ['usd_rate' => $bitcoinRate]
        );

        Crypto::updateOrCreate(
            ['crypto_name' => 'ETH'],
            ['usd_rate' => $ethereumRate]
        );

        Crypto::updateOrCreate(
            ['crypto_name' => 'XRP'],
            ['usd_rate' => $rippleRate]
        );

        $this->info('Crypto rates updated successfully: BTC: ' . $bitcoinRate . ', ETH: ' . $ethereumRate . ', XRP: ' . $rippleRate);
    }
}
