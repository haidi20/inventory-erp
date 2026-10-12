<?php

namespace App\Modules\Operational\Sales\Entities;

class SaleFilterEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $search = null,
        public ?string $status = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? (string) $data['uuid'] : null,
            search: isset($data['search']) ? trim((string) $data['search']) : null,
            status: isset($data['status']) ? trim((string) $data['status']) : null,
        );
    }
}

