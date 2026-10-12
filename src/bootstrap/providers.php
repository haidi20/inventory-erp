<?php

return [
    App\Modules\Analytics\AnalyticsServiceProvider::class,
    App\Modules\Core\CoreServiceProvider::class,
    App\Modules\Finance\FinanceServiceProvider::class,
    App\Modules\Operational\OperationalServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    ...(env('APP_ENV') === 'local' ? [App\Providers\TelescopeServiceProvider::class] : []),
    PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
    Tightenco\Ziggy\ZiggyServiceProvider::class,
];