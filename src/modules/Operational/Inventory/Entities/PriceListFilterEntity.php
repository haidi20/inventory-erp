<?php

namespace App\Modules\Operational\Inventory\Entities;

class PriceListFilterEntity
{
    public function __construct(
        public ?string $search = null,
        public ?string $tab = null,
        public ?string $company_uuid = null,
        public ?string $branch_uuid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: isset($data['search']) ? trim((string) $data['search']) : null,
            tab: $data['tab'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
        );
    }
}

