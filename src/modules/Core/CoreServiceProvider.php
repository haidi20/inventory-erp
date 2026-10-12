<?php

namespace App\Modules\Core;

use Illuminate\Support\ServiceProvider;
use App\Modules\Core\Organization\Repositories\CompanyRepositoryInterface;
use App\Modules\Core\Organization\Repositories\CompanyRepository;
use App\Modules\Core\Organization\Repositories\BranchRepositoryInterface;
use App\Modules\Core\Organization\Repositories\BranchRepository;
use App\Modules\Core\Identity\Repositories\AuthRepositoryInterface;
use App\Modules\Core\Identity\Repositories\AuthRepository;
use App\Modules\Core\Identity\Repositories\UserRepositoryInterface;
use App\Modules\Core\Identity\Repositories\UserRepository;
use App\Modules\Core\Authorization\Repositories\RoleRepositoryInterface;
use App\Modules\Core\Authorization\Repositories\RoleRepository;
use App\Modules\Core\Identity\Repositories\OwnerRegistrationRepositoryInterface;
use App\Modules\Core\Identity\Repositories\OwnerRegistrationRepository;
use App\Modules\Core\Identity\Repositories\WebAuthRepositoryInterface;
use App\Modules\Core\Identity\Repositories\WebAuthRepository;
use App\Modules\Core\Authorization\Repositories\PermissionRepositoryInterface;
use App\Modules\Core\Authorization\Repositories\PermissionRepository;
use App\Modules\Core\Authorization\Repositories\AppMenuRepositoryInterface;
use App\Modules\Core\Authorization\Repositories\AppMenuRepository;
use App\Modules\Core\Platform\Repositories\SettingsRepositoryInterface;
use App\Modules\Core\Platform\Repositories\SettingsRepository;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Organization
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        // Identity
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WebAuthRepositoryInterface::class, WebAuthRepository::class);
        $this->app->bind(OwnerRegistrationRepositoryInterface::class, OwnerRegistrationRepository::class);
        // Authorization
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(AppMenuRepositoryInterface::class, AppMenuRepository::class);
        // Platform
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
