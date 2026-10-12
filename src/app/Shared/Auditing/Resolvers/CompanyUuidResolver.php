<?php

namespace App\Shared\Auditing\Resolvers;

use App\Shared\Support\CompanyScopeResolver;
use OwenIt\Auditing\Contracts\Resolver;
use OwenIt\Auditing\Contracts\Auditable;

class CompanyUuidResolver implements Resolver
{
    public static function resolve(Auditable $auditable): ?string
    {
        try {
            return app(CompanyScopeResolver::class)->resolveCompanyUuid() ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}