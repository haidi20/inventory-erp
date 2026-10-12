<?php

namespace App\Modules\Operational\Manufacturing\Entities;

/**
 * AssemblyEntity
 * 
 * Entitas untuk mengelola data perakitan barang (Assembly).
 */
class AssemblyEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $location_uuid = null,
        public ?string $item_uuid = null,
        public ?string $unit_uuid = null,
        public ?string $assembly_no = null,
        public ?int $date = null,
        public float $qty = 0.0,
        public ?string $description = null,
        public string $status = 'draft',
        public array $items = [],
        public ?string $actor_uuid = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?string $search = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'location_uuid' => $this->location_uuid,
            'item_uuid' => $this->item_uuid,
            'unit_uuid' => $this->unit_uuid,
            'assembly_no' => $this->assembly_no,
            'date' => $this->date,
            'qty' => $this->qty,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid:          $model->uuid,
            branch_uuid:   $model->branch_uuid,
            company_uuid:  $model->company_uuid,
            location_uuid: $model->location_uuid,
            item_uuid:     $model->item_uuid,
            unit_uuid:     $model->unit_uuid,
            assembly_no:   $model->document_no,
            date:          (int) $model->date,
            qty:           (float) ($model->quantity ?? 0),
            description:   $model->notes,
            status:        $model->status,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            location_uuid: $data['location_uuid'] ?? null,
            item_uuid: $data['item_uuid'] ?? null,
            unit_uuid: $data['unit_uuid'] ?? null,
            assembly_no: $data['assembly_no'] ?? null,
            date: isset($data['date']) ? (int) $data['date'] : time(),
            qty: (float) ($data['qty'] ?? 0.0),
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'draft',
            items: $data['items'] ?? [],
            actor_uuid: $data['actor_uuid'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            search: $data['search'] ?? null,
        );
    }
}

