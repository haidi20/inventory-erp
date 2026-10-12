<?php

namespace App\Modules\Operational\Master\Entities;

class ItemUomEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $item_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $uom_code = null,
        public float $to_qty = 1.0,
        public ?string $barcode = null,
        public bool $is_base_uom = false,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            item_uuid: $model->item_uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            uom_code: $model->uom_code,
            to_qty: (float) ($model->to_qty ?? 1.0),
            barcode: $model->barcode,
            is_base_uom: (bool) ($model->is_base_uom ?? false),
        );
    }
}
