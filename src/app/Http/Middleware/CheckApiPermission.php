<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

/**
 * Custom permission middleware untuk API routes.
 * Spatie default pakai Auth::guard(null) = web guard.
 * Middleware ini resolve user dari api guard (JWT) terlebih dahulu,
 * lalu cek permission tanpa batasan guard_name.
 */
class CheckApiPermission
{
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $user = auth('api')->user() ?? auth('web')->user();

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $permissions = explode('|', $permission);

        // Cek permission langsung via DB query tanpa batasan guard
        $hasPermission = \DB::table('permissions')
            ->join('role_has_permissions', 'permissions.uuid', '=', 'role_has_permissions.permission_uuid')
            ->join('roles', 'roles.uuid', '=', 'role_has_permissions.role_uuid')
            ->join('model_has_roles', 'roles.uuid', '=', 'model_has_roles.role_uuid')
            ->where('model_has_roles.model_uuid', $user->getAuthIdentifier())
            ->whereIn('permissions.name', $permissions)
            ->exists();

        if (! $hasPermission) {
            throw UnauthorizedException::forPermissions($permissions);
        }

        return $next($request);
    }
}
