<?php

namespace App\Modules\Operational\Sales\Entities;

/**
 * DeliveryOrderEntity
 * 
 * Data Transfer Object untuk transaksi Delivery Order.
 */
class DeliveryOrderEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $person_uuid = null,
        public ?string $location_uuid = null,
        public ?string $delivery_no = null,
        public ?int $delivery_date = null,
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

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            person_uuid: $model->person_uuid,
            location_uuid: $model->location_uuid,
            delivery_no: $model->delivery_no,
            delivery_date: $model->delivery_date,
            description: $model->description,
            details: $model->relationLoaded('details') ? $model->details->toArray() : []
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
            delivery_no: $data['delivery_no'] ?? null,
            delivery_date: $data['delivery_date'] ?? null,
            description: $data['description'] ?? null,
            details: $data['details'] ?? [],
            actor_uuid: $data['actor_uuid'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 10),
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? 'all',
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'person_uuid' => $this->person_uuid,
            'location_uuid' => $this->location_uuid,
            'delivery_no' => $this->delivery_no,
            'delivery_date' => $this->delivery_date,
            'description' => $this->description,
            'details' => $this->details,
        ];
    }
}

