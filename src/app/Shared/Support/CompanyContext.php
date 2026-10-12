<?php

namespace App\Shared\Support;

class CompanyContext
{
    public function __construct(
        public readonly ?string $companyUuid,
        public readonly ?string $branchUuid,
    ) {}
}
