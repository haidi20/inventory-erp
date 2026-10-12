<?php

namespace App\Modules\Analytics;

use App\Modules\Analytics\Dashboard\Repositories\AnalyticsRepository;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AnalyticsRepository::class,
            AnalyticsRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}