<?php

namespace App\Modules\Core\Authorization\Repositories;

use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Facades\Config;

/**
 * Class AppMenuRepository
 *
 * Menangani akses data untuk menu aplikasi dan permission sidebar.
 */
class AppMenuRepository implements AppMenuRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getSidebarDefinitions(): array
    {
        // Membaca dari config/menus.php['sidebar_menu']
        return Config::get('menus.sidebar_menu', []);
    }

    /**
     * @inheritDoc
     */
    public function getUserPermissionNames(string $userId): array
    {
        $user = User::with('roles.permissions')
            ->where('uuid', $userId)
            ->first();

        if (!$user) {
            return [];
        }

        $permissions = [];
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[$permission->name] = $permission->name;
            }
        }

        $permissionNames = array_values($permissions);
        sort($permissionNames);

        return $permissionNames;
    }
}
