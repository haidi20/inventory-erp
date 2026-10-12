<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * TreatmentPackageItemEntity
 * 
 * Entitas untuk mengelola item rincian dalam sebuah paket layanan (Service Package Item).
 */
class TreatmentPackageItemEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $treatment_package_uuid = null,
        public ?string $item_type = null, // 'service' or 'product'
        public ?string $item_uuid = null,
        public int $qty = 1,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'service_package_uuid' => $this->treatment_package_uuid, // mapping to DB column
            'item_type' => $this->item_type,
            'item_uuid' => $this->item_uuid,
            'qty' => $this->qty,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            treatment_package_uuid: $model->service_package_uuid,
            item_type: $model->item_type,
            item_uuid: $model->item_uuid,
            qty: (int) $model->qty,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            treatment_package_uuid: $data['service_package_uuid'] ?? ($data['treatment_package_uuid'] ?? null),
            item_type: $data['item_type'] ?? null,
            item_uuid: $data['item_uuid'] ?? null,
            qty: (int) ($data['qty'] ?? 1),
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
