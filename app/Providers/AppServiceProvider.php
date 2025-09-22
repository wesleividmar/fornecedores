<?php

namespace App\Providers;

use App\Services\Contracts\FornecedorServiceInterface;
use App\Services\FornecedorService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FornecedorServiceInterface::class, FornecedorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
