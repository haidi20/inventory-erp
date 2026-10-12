<?php

namespace App\Shared\Support;

use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Http\Request;

/**
 * Resolver untuk menentukan company_uuid dan branch_uuid aktif dari request/user.
 *
 * Urutan prioritas resolusi company_uuid:
 *   1. Header X-Klinik-Company-Uuid (eksplisit dari client)
 *   2. Sandbox: company dari pivot user_companies (is_default=true) jika sandbox
 *   3. Pivot user_companies (is_default=true) milik authenticated user
 *   4. Pivot user_companies (first entry) milik authenticated user
 *   5. (legacy fallback dihapus -- langkah 6)
 *   6. Runtime branch config (production/sandbox)
 *   7. Company pertama di database (fallback terakhir)
 *
 * Urutan prioritas resolusi branch_uuid:
 *   1. Header X-Klinik-Branch-Uuid (eksplisit dari client)
 *   2. Pivot user_branches (is_default=true) untuk company aktif
 *   3. Pivot user_branches (first entry) untuk company aktif
 *   4. (legacy fallback dihapus -- langkah 6)
 */
class CompanyScopeResolver
{
    public function resolveEnvironment(?Request $request = null): string
    {
        $request ??= request();

        $headerName = (string) config('klinik.headers.environment', 'X-Klinik-Environment');
        $headerEnvironment = strtolower(trim((string) $request->header($headerName)));
        if ($headerEnvironment !== '') {
            return $headerEnvironment;
        }

        return strtolower(trim((string) config('klinik.runtime_environment', config('app.env', 'production'))));
    }

    public function isSandboxLike(?Request $request = null): bool
    {
        $environment = $this->resolveEnvironment($request);
        $sandboxEnvironments = array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            (array) config('klinik.sandbox_environments', [])
        );

        return in_array($environment, $sandboxEnvironments, true);
    }

    public function resolveCompanyUuid(?Request $request = null): string
    {
        $request ??= request();

        // 0. Context tervalidasi dari middleware ResolveCompanyContext (jika sudah di-resolve)
        $context = $request->attributes->get('company_context');
        if ($context instanceof CompanyContext && $context->companyUuid !== null && $context->companyUuid !== '') {
            return $context->companyUuid;
        }

        // 1. Cek header eksplisit dari client
        $headerCompanyUuid = $this->resolveHeaderCompanyUuid($request);
        if ($headerCompanyUuid !== '') {
            return $headerCompanyUuid;
        }

        // 2. Sandbox mode
        if ($this->isSandboxLike($request)) {
            $sandboxCompanyUuid = $this->resolveCompanyUuidFromRuntimeBranch(
                (string) config('klinik.runtime_branches.sandbox', 'KSB')
            );
            if ($sandboxCompanyUuid !== '') {
                return $sandboxCompanyUuid;
            }
        }

        // 3. Dari pivot user_companies (multi-org)
        $user = $request->user();
        if ($user) {
            $pivotCompanyUuid = $this->resolveCompanyUuidFromPivot($user->uuid);
            if ($pivotCompanyUuid !== '') {
                return $pivotCompanyUuid;
            }

        }

        // 5. Runtime branch config (production)
        $productionCompanyUuid = $this->resolveCompanyUuidFromRuntimeBranch(
            (string) config('klinik.runtime_branches.production', 'KPR')
        );
        if ($productionCompanyUuid !== '') {
            return $productionCompanyUuid;
        }

        // 6. Company pertama di database (last resort)
        return (string) Company::query()->orderBy('id')->value('uuid');
    }

    public function resolveBranchUuid(?Request $request = null): string
    {
        $request ??= request();

        // 0. Context tervalidasi dari middleware ResolveCompanyContext (jika sudah di-resolve)
        $context = $request->attributes->get('company_context');
        if ($context instanceof CompanyContext && $context->branchUuid !== null && $context->branchUuid !== '') {
            return $context->branchUuid;
        }

        // 1. Cek header eksplisit dari client
        $headerName = (string) config('klinik.headers.branch_uuid', 'X-Klinik-Branch-Uuid');
        $headerBranchUuid = trim((string) $request->header($headerName));
        if ($headerBranchUuid !== '') {
            return $headerBranchUuid;
        }

        $user = $request->user();
        if (!$user) {
            return '';
        }

        $companyUuid = $this->resolveCompanyUuid($request);

        // 2. Dari pivot user_branches untuk company aktif
        $pivotBranchUuid = $this->resolveBranchUuidFromPivot($user->uuid, $companyUuid);
        if ($pivotBranchUuid !== '') {
            return $pivotBranchUuid;
        }

        // 3. (legacy fallback dihapus -- langkah 6)
        return '';
    }

    /**
     * Cek apakah user punya akses ke company tertentu.
     * Cek pivot user_companies.
     */
    public function userHasAccessToCompany(string $userUuid, string $companyUuid): bool
    {
        // Cek pivot
        if (UserCompany::where('user_uuid', $userUuid)->where('company_uuid', $companyUuid)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user punya akses ke branch tertentu pada company aktif.
     */
    public function userHasAccessToBranch(string $userUuid, string $companyUuid, string $branchUuid): bool
    {
        if (UserBranch::where('user_uuid', $userUuid)
            ->where('company_uuid', $companyUuid)
            ->where('branch_uuid', $branchUuid)
            ->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Company default milik user: pivot is_default -> pivot pertama. Dipakai middleware context.
     */
    public function resolveUserDefaultCompanyUuid(string $userUuid): string
    {
        // Kolom legacy users.company_uuid sudah di-drop; resolusi murni via pivot.
        return $this->resolveCompanyUuidFromPivot($userUuid);
    }

    /**
     * Branch default milik user untuk company aktif: pivot is_default -> pivot pertama.
     */
    public function resolveUserDefaultBranchUuid(string $userUuid, string $companyUuid): string
    {
        // Kolom legacy users.branch_uuid sudah di-drop; resolusi murni via pivot.
        return $this->resolveBranchUuidFromPivot($userUuid, $companyUuid);
    }

    /**
     * Ambil semua company_uuid yang bisa diakses user.
     */
    public function resolveAllCompanyUuids(string $userUuid): array
    {
        return UserCompany::where('user_uuid', $userUuid)
            ->pluck('company_uuid')
            ->toArray();
    }

    /**
     * Ambil semua branch_uuid yang bisa diakses user untuk company tertentu.
     */
    public function resolveAllBranchUuids(string $userUuid, string $companyUuid): array
    {
        return UserBranch::where('user_uuid', $userUuid)
            ->where('company_uuid', $companyUuid)
            ->pluck('branch_uuid')
            ->toArray();
    }

    private function resolveHeaderCompanyUuid(Request $request): string
    {
        $headerName = (string) config('klinik.headers.company_uuid', 'X-Klinik-Company-Uuid');
        return $this->normalizeCompanyUuid((string) $request->header($headerName));
    }

    private function resolveCompanyUuidFromPivot(string $userUuid): string
    {
        // Cari yang is_default=true
        $pivot = UserCompany::where('user_uuid', $userUuid)
            ->where('is_default', true)
            ->first();

        if ($pivot) {
            return (string) $pivot->company_uuid;
        }

        // Fallback ke entry pertama
        $first = UserCompany::where('user_uuid', $userUuid)->first();
        return $first ? (string) $first->company_uuid : '';
    }

    private function resolveBranchUuidFromPivot(string $userUuid, string $companyUuid): string
    {
        // Cari yang is_default=true untuk company aktif
        $pivot = UserBranch::where('user_uuid', $userUuid)
            ->where('company_uuid', $companyUuid)
            ->where('is_default', true)
            ->first();

        if ($pivot) {
            return (string) $pivot->branch_uuid;
        }

        // Fallback ke entry pertama untuk company aktif
        $first = UserBranch::where('user_uuid', $userUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return $first ? (string) $first->branch_uuid : '';
    }

    private function normalizeCompanyUuid(string $companyUuid): string
    {
        $companyUuid = trim($companyUuid);
        if ($companyUuid === '') {
            return '';
        }
        return $this->companyExists($companyUuid) ? $companyUuid : '';
    }

    private function companyExists(string $companyUuid): bool
    {
        return Company::query()->where('uuid', $companyUuid)->exists();
    }

    private function resolveCompanyUuidFromRuntimeBranch(string $shortName): string
    {
        $shortName = trim($shortName);
        if ($shortName === '') {
            return '';
        }
        $companyUuid = (string) (Branch::query()->where('short_name', $shortName)->value('company_uuid') ?? '');
        return $this->normalizeCompanyUuid($companyUuid);
    }
}
