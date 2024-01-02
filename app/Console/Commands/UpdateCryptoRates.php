<?php

namespace App\Console\Commands;

use App\Models\Crypto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http; // Import the Http facade

class UpdateCryptoRates extends Command
{
    protected $signature = 'fetch:crypto-rates';
    protected $description = 'Update USD to Bitcoin exchange rates from Coinbase';

    public function handle()
    {
        $response = Http::get('https://api.coinbase.com/v2/exchange-rates?currency=USD');

        $data = $response->json();

        $bitcoinRate = $data['data']['rates']['BTC'];

        // Assuming 'id' is the primary key for your Crypto model
        // Update or create a record with the specified 'id'
        Crypto::updateOrCreate(
            ['id' => 1], // Change 'id' to the actual unique identifier for your Crypto model
            [
                'currency' => 'USD', // Assuming the currency is always USD
                'btc_rate' => $bitcoinRate,
            ]
        );

        $this->info('Crypto rates updated successfully: BTC: ' . $bitcoinRate);
    }
}
