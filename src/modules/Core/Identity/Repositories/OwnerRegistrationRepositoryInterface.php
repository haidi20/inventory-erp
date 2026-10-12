<?php

namespace App\Modules\Core\Identity\Repositories;

use App\Modules\Core\Authorization\Entities\RoleEntity;

interface OwnerRegistrationRepositoryInterface
{
    public function ensureOwnerRole(): RoleEntity;

    public function generateUniqueBranchShortName(string $companyName): string;

    public function assignDefaultCompany(string $userUuid, string $companyUuid): void;

    public function assignDefaultBranch(string $userUuid, string $companyUuid, string $branchUuid): void;
}
