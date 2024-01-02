<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $commands = [
        \App\Console\Commands\FetchCurrencyRatesCommand::class,
    ];
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('fetch:currency-rates')
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/currency_rates.log'))
            ->after(function () {
                info('Scheduled task ran successfully at ' . now());
            });
        $schedule->command('balances:update-investment')->yearly();

        $schedule->command('fetch:crypto-rates')
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/currency_rates.log'))
            ->after(function () {
                info('Scheduled task ran successfully at ' . now());
            });
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
