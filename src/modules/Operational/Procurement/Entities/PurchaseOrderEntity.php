<?php

namespace App\Modules\Operational\Procurement\Entities;

use App\Modules\Operational\Sales\Enums\TransactionStatus;

class PurchaseOrderEntity
{
    public function __construct(
        public ?string $uuid = null,
        public string $branch_uuid = '',
        public ?string $company_uuid = null,
        public string $supplier_uuid = '',
        public string $location_uuid = '',
        public string $document_no = '',
        public ?string $reference_no = null,
        public int $date = 0,
        public ?int $required_at = null,
        public TransactionStatus $status = TransactionStatus::DRAFT,
        public ?string $notes = null,
        public float $discount_percent = 0,
        public float $discount_amount = 0,
        public float $tax_percent = 0,
        public float $tax_amount = 0,
        public array $items = [],
        public ?string $actor_uuid = null,
        public ?string $description = null
    ) {}
}

