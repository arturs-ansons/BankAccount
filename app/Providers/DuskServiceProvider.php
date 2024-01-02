<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\Browser;
use Illuminate\Support\Str;

class DuskServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Browser::macro('waitForTextIgnoringCase', function ($text, $seconds = null) {
            return $this->waitUsing($seconds, 100, function () use ($text) {
                return Str::contains(
                    strtolower($this->resolver->findOrFail('')->getText()),
                    strtolower($text)
                );
            }, "Waited %s seconds for text [{$text}] ignoring case.");
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
