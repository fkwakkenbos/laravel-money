<?php

namespace Flooris\LaravelMoney;

use Illuminate\Support\ServiceProvider;

class LaravelMoneyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/money.php' => config_path('money.php'),
        ], 'laravel-money');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/money.php', 'money'
        );
    }
}
