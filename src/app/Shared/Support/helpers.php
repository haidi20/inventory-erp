<?php

use App\Modules\Core\Identity\Models\User;
use App\Shared\Support\CompanyContext;
use App\Shared\Support\CompanyScopeResolver;

if (!function_exists('current_company_uuid')) {
    function current_company_uuid(): ?string
    {
        $context = request()->attributes->get('company_context');

        if ($context instanceof CompanyContext) {
            return $context->companyUuid;
        }

        // request()->user() kosong di luar HTTP (test actingAs, artisan, queue);
        // fallback ke auth()->user().
        $user = request()->user() ?: auth()->user();
        if (!$user instanceof User) {
            return null;
        }

        $cache = request()->attributes->get('scope_resolver_cache', []);
        $cacheKey = 'company:' . $user->uuid;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $companyUuid = app(CompanyScopeResolver::class)
            ->resolveUserDefaultCompanyUuid($user->uuid) ?: null;

        $cache[$cacheKey] = $companyUuid;
        request()->attributes->set('scope_resolver_cache', $cache);

        return $companyUuid;
    }
}

if (!function_exists('current_branch_uuid')) {
    function current_branch_uuid(): ?string
    {
        $context = request()->attributes->get('company_context');

        if ($context instanceof CompanyContext) {
            return $context->branchUuid;
        }

        // request()->user() kosong di luar HTTP (test actingAs, artisan, queue);
        // fallback ke auth()->user().
        $user = request()->user() ?: auth()->user();
        if (!$user instanceof User) {
            return null;
        }

        $cache = request()->attributes->get('scope_resolver_cache', []);
        $companyUuid = current_company_uuid();
        $cacheKey = 'branch:' . $user->uuid . ':' . ($companyUuid ?? '');
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $branchUuid = app(CompanyScopeResolver::class)
            ->resolveUserDefaultBranchUuid($user->uuid, (string) $companyUuid) ?: null;

        $cache[$cacheKey] = $branchUuid;
        request()->attributes->set('scope_resolver_cache', $cache);

        return $branchUuid;
    }
}
