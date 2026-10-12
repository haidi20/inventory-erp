<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Authorization\Entities\RoleEntity;
use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Str;

class OwnerRegistrationRepository implements OwnerRegistrationRepositoryInterface
{
    public function ensureOwnerRole(): RoleEntity
    {
        $role = Role::firstOrCreate(
            ['name' => 'owner', 'guard_name' => 'web'],
            ['label' => 'Owner / Direktur']
        );

        Permission::firstOrCreate(
            ['name' => 'dashboard.view', 'guard_name' => 'web'],
            ['label' => 'Akses Dashboard']
        );

        $role->syncPermissions(Permission::query()->pluck('name')->all());

        return RoleEntity::fromModel($role->load('permissions'));
    }

    public function generateUniqueBranchShortName(string $companyName): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $companyName) ?: 'MAIN');
        $base = Str::limit($base, 4, '');
        $suffix = 1;

        do {
            $shortName = substr($base . $suffix, 0, 5);
            $exists = Branch::query()->whereRaw('UPPER(short_name) = ?', [$shortName])->exists();
            $suffix++;
        } while ($exists);

        return $shortName;
    }

    public function assignDefaultCompany(string $userUuid, string $companyUuid): void
    {
        // firstOrCreate (bukan create) agar idempotent: registrasi ulang / retry
        // tidak lagi 500 karena UNIQUE constraint user_companies (TEST-001).
        $pivot = UserCompany::query()->firstOrNew([
            'user_uuid' => $userUuid,
            'company_uuid' => $companyUuid,
        ]);
        $pivot->is_default = true;
        $pivot->save();
    }

    public function assignDefaultBranch(string $userUuid, string $companyUuid, string $branchUuid): void
    {
        // Pola sama dengan assignDefaultCompany: idempotent, aman dipanggil ulang.
        $pivot = UserBranch::query()->firstOrNew([
            'user_uuid' => $userUuid,
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
        ]);
        $pivot->is_default = true;
        $pivot->save();
    }
}
