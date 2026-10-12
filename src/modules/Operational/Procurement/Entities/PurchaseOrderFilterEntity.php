<?php

namespace App\Modules\Operational\Procurement\Entities;

class PurchaseOrderFilterEntity
{
    public function __construct(
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $status = null,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 10
    ) {}
}

