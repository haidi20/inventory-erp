<?php

namespace App\Shared\Auditing\Resolvers;

use App\Shared\Support\CompanyScopeResolver;
use OwenIt\Auditing\Contracts\Resolver;
use OwenIt\Auditing\Contracts\Auditable;

class BranchUuidResolver implements Resolver
{
    public static function resolve(Auditable $auditable): ?string
    {
        try {
            return app(CompanyScopeResolver::class)->resolveBranchUuid() ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}