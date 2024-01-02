<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Http;
    use App\Models\ApiCurrency;

    class FetchCurrencyRatesCommand extends Command
    {
        protected $signature = 'fetch:currency-rates';
        protected $description = 'Fetch currency rates from Coinbase API and update the database';

        public function handle()
        {
            $this->call('schedule:run');
            $this->output->writeln('Fetching currency rates from Coinbase API and updating the database...');

            $baseCurrency = 'EUR';
            $allowedCurrencies = ['USD', 'GBP', 'JPY'];

            $response = Http::get('https://api.coinbase.com/v2/exchange-rates', [
                'currency' => $baseCurrency,
            ]);

            if ($response->successful()) {
                $exchangeRates = $response->json('data.rates');

                foreach ($allowedCurrencies as $currency) {
                    // Check if the allowed currency is present in the exchange rates
                    if (isset($exchangeRates[$currency])) {
                        // Insert or update the currency rate in the database
                        ApiCurrency::updateOrCreate(
                            ['symbol' => $currency],
                            ['rate' => $exchangeRates[$currency]]
                        );

                        $this->output->writeln("Exchange rate for {$currency}: {$exchangeRates[$currency]} updated in the database.");
                    } else {
                        $this->output->error("Exchange rate for {$currency} not available.");
                    }
                }
            } else {
                $this->output->error('Failed to fetch exchange rates from the Coinbase API.');
            }

            $this->output->success('Currency rates update complete.');

            return 0;
        }
    }
