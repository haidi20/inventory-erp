<?php

namespace App\Shared\Traits;

use App\Shared\Support\CompanyScopeResolver;
use Illuminate\Http\Request;

trait ResolvesKlinikContext
{
    protected function resolveCompanyUuid(?Request $request = null): string
    {
        return app(CompanyScopeResolver::class)->resolveCompanyUuid($request);
    }

    protected function resolveRuntimeEnvironment(?Request $request = null): string
    {
        return app(CompanyScopeResolver::class)->resolveEnvironment($request);
    }
}
