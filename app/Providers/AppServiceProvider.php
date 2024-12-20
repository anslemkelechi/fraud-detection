<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\RiskInterface;
use App\Repository\RiskRepository;
use App\Contracts\UserInterface;
use App\Repository\UserRepository;
use App\Contracts\TransactionInterface;
use App\Repository\TransactionRepository;
use App\Repository\BlacklistRepository;
use App\Contracts\BlacklistInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RiskInterface::class, RiskRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(TransactionInterface::class, TransactionRepository::class);
        $this->app->bind(BlacklistInterface::class, BlacklistRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
