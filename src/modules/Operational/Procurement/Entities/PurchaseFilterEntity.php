<?php

namespace App\Modules\Operational\Procurement\Entities;

class PurchaseFilterEntity
{
    public function __construct(
        public ?string $branch_uuid = null,
        public ?string $supplier_uuid = null,
        public ?string $status = null,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 10
    ) {}
}

