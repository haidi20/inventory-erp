<?php

namespace App\Modules\Finance\Journal\Entities;

/**
 * FinancialReportEntity
 * 
 * Entitas untuk parameter filter laporan keuangan.
 */
class FinancialReportEntity
{
    public function __construct(
        public ?string $period_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $format = 'excel',
        public ?int $as_of_date = null,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            period_uuid: $data['period_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            format: $data['format'] ?? 'excel',
            as_of_date: isset($data['as_of_date']) ? (int)$data['as_of_date'] : null,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
