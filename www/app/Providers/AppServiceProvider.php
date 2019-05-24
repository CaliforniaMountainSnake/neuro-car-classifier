<?php

namespace App\Providers;

use App\ApplicationStorageService;
use App\FileDownloader\StorageService\StorageServiceInterface;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     * @throws \LogicException
     */
    public function register(): void
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        // Binding Interfaces To Implementations.
        $this->app->bind(StorageServiceInterface::class, ApplicationStorageService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
