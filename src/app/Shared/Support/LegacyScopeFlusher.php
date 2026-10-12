<?php

namespace App\Shared\Support;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;

/**
 * Menerjemahkan pasangan legacy branch_uuid/company_uuid menjadi pivot
 * user_companies/user_branches (is_default=true) milik User.
 */
class LegacyScopeFlusher
{
    public function flush(User $user, ?string $branchUuid, ?string $companyUuid): void
    {
        if ($branchUuid !== null && $companyUuid === null) {
            // Bypass global scope: user belum punya pivot, jadi lookup ber-scope
            // pasti kosong (catch-22 saat resolve company dari branch).
            $branch = Branch::query()->withoutGlobalScopes()->where('uuid', $branchUuid)->first();

            if ($branch) {
                $companyUuid = $branch->company_uuid;

                // Branch tanpa company (pola test lama) di-auto-heal: buat
                // company baru agar pivot user_branches (company NOT NULL) valid.
                if ($companyUuid === null) {
                    $company = Company::create(['name' => 'Company ' . ($branch->name ?: $branch->short_name), 'is_active' => true]);
                    $branch->forceFill(['company_uuid' => $company->uuid])->saveQuietly();
                    $companyUuid = $company->uuid;
                }
            }
        }

        if ($companyUuid !== null) {
            UserCompany::query()->firstOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $companyUuid],
                ['is_default' => true]
            );
        }

        // Pivot user_branches butuh company_uuid (NOT NULL).
        if ($branchUuid !== null && $companyUuid !== null) {
            UserBranch::query()->firstOrCreate(
                ['user_uuid' => $user->uuid, 'branch_uuid' => $branchUuid],
                ['company_uuid' => $companyUuid, 'is_default' => true]
            );
        }
    }
}
