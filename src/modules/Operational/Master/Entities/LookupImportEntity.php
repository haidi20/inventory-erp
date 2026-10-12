<?php

namespace App\Modules\Operational\Master\Entities;

class LookupImportEntity
{
    public function __construct(
        public ?string $type_code = null,
        public int|string|null $lookup_set_uuid = null,
        public array $rows = [],
        public ?string $company_uuid = null,
    ) {
    }
}
