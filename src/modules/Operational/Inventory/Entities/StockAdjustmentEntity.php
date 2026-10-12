<?php

namespace App\Modules\Operational\Inventory\Entities;

use App\Modules\Operational\Sales\Enums\TransactionStatus;

class StockAdjustmentEntity
{
    public function __construct(
        public ?string $uuid = null,
        public string $branch_uuid = '',
        public ?string $company_uuid = null,
        public string $location_uuid = '',
        public ?string $adjustment_no = null,
        public ?string $reference_no = null,
        public int $date = 0,
        public TransactionStatus $status = TransactionStatus::DRAFT,
        public ?string $account_uuid = null,
        public ?string $notes = null,
        public bool $is_value_adjustment = false,
        public array $items = [],
        public ?string $actor_uuid = null
    ) {}
}

