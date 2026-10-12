<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    protected array $modules = [
        'Analytics/Dashboard',
        'Clinical/Integration',
        'Clinical/MedicalRecord',
        'Clinical/Pharmacy',
        'Clinical/Registration',
        'Clinical/Ancillary',
        'Clinical/Classification',
        'Core/Authorization',
        'Core/Identity',
        'Core/Organization',
        'Core/Platform',
        'Finance/Journal',
        'Finance/Ledger',
        'Analytics/Reports',
        'Operational/Inventory',
        'Operational/Manufacturing',
        'Operational/Master',
        'Operational/Procurement',
        'Operational/Sales',
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $basePath = base_path("modules/{$module}");
            $migrationsPath = "{$basePath}/database/migrations";
            if (is_dir($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
            $viewsPath = "{$basePath}/resources/views";
            if (is_dir($viewsPath)) {
                $namespace = Str::lower(str_replace("/", ".", $module));
                $this->loadViewsFrom($viewsPath, $namespace);
            }
        }
    }
}
