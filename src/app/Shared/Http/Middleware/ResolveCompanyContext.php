<?php

namespace App\Shared\Http\Middleware;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Shared\Support\CompanyContext;
use App\Shared\Support\CompanyScopeResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            // Sekali load per request supaya isSuperAdmin() di scope/Gate bebas query.
            $user->loadMissing('roles');

            $resolver = app(CompanyScopeResolver::class);
            $isSuperAdmin = $user->isSuperAdmin();

            $headerCompany = trim((string) $request->header('X-Klinik-Company-Uuid'));
            $headerBranch = trim((string) $request->header('X-Klinik-Branch-Uuid'));

            if ($headerCompany !== '') {
                if (!$isSuperAdmin && !$resolver->userHasAccessToCompany($user->uuid, $headerCompany)) {
                    abort(403, 'Anda tidak memiliki akses ke perusahaan yang diminta.');
                }
                $companyUuid = $headerCompany;
            } else {
                $companyUuid = $resolver->resolveUserDefaultCompanyUuid($user->uuid) ?? '';
            }

            if ($headerBranch !== '') {
                if (!$isSuperAdmin && !$resolver->userHasAccessToBranch($user->uuid, $companyUuid, $headerBranch)) {
                    abort(403, 'Anda tidak memiliki akses ke cabang yang diminta.');
                }

                $branchCompanyId = (string) (Branch::query()->where('uuid', $headerBranch)->value('company_uuid') ?? '');
                if ($companyUuid !== '' && $branchCompanyId !== '' && $branchCompanyId !== $companyUuid) {
                    abort(403, 'Cabang tidak berada di perusahaan yang aktif.');
                }

                $branchUuid = $headerBranch;
            } else {
                $branchUuid = $resolver->resolveUserDefaultBranchUuid($user->uuid, $companyUuid) ?? '';
            }

            $request->attributes->set('company_context', new CompanyContext(
                $companyUuid !== '' ? $companyUuid : null,
                $branchUuid !== '' ? $branchUuid : null,
            ));
        }

        return $next($request);
    }
}
