<?php

namespace App\Modules\Operational\Sales\Entities;

/**
 * SalesOrderEntity
 * 
 * Data Transfer Object untuk transaksi Sales Order.
 */
class SalesOrderEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $person_uuid = null,
        public ?string $location_uuid = null,
        public ?string $payment_term_uuid = null,
        public ?string $sales_order_no = null,
        public ?int $sales_order_date = null,
        public ?string $status = null,
        public ?string $description = null,
        public array $details = [],
        public ?string $actor_uuid = null,
        public int $page = 1,
        public int $per_page = 10,
        public ?string $search = null,
        public ?string $search_column = 'all',
        public ?string $start_date = null,
        public ?string $end_date = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'person_uuid' => $this->person_uuid,
            'location_uuid' => $this->location_uuid,
            'payment_term_uuid' => $this->payment_term_uuid,
            'sales_order_no' => $this->sales_order_no,
            'sales_order_date' => $this->sales_order_date,
            'status' => $this->status,
            'description' => $this->description,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            person_uuid: $model->person_uuid,
            location_uuid: $model->location_uuid,
            payment_term_uuid: $model->payment_term_uuid,
            sales_order_no: $model->sales_order_no,
            sales_order_date: (int) $model->sales_order_date,
            status: $model->status,
            description: $model->description,
            details: $model->relationLoaded('details') ? $model->details->toArray() : [],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            person_uuid: $data['person_uuid'] ?? null,
            location_uuid: $data['location_uuid'] ?? null,
            payment_term_uuid: $data['payment_term_uuid'] ?? null,
            sales_order_no: $data['sales_order_no'] ?? null,
            sales_order_date: isset($data['sales_order_date']) ? (int) $data['sales_order_date'] : null,
            status: $data['status'] ?? null,
            description: $data['description'] ?? null,
            details: $data['details'] ?? [],
            actor_uuid: $data['actor_uuid'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 10),
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? 'all',
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
        );
    }
}

