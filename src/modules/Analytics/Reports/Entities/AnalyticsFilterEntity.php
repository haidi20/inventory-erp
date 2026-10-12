<?php

namespace App\Modules\Analytics\Reports\Entities;

/**
 * AnalyticsFilterEntity
 * 
 * Entitas untuk mengelola filter parameter pada modul Analitik.
 */
class AnalyticsFilterEntity
{
    public function __construct(
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public int $limit = 5,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            date_from: $data['date_from'] ?? null,
            date_to: $data['date_to'] ?? null,
            limit: (int) ($data['limit'] ?? 5),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
