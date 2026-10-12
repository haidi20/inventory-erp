<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * ItemConversionEntity
 * 
 * Entitas untuk mengelola data konversi satuan barang (Product Unit).
 * Diselaraskan dengan skema database aktual (Tinker):
 * - item_conversions: item_uuid, from_unit_uuid, to_unit_uuid, conversion_ratio
 * - item_uoms: item_uuid, uom_code, to_qty, barcode, is_base_uom
 */
class ItemConversionEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $item_uuid = null,
        public ?string $from_unit_uuid = null,
        public ?string $to_unit_uuid = null,
        public float $to_qty = 1.0,
        public float $conv_ratio = 1.0,
        public bool $is_active = true,
        public bool $is_base_uom = false,
        public ?string $uom_code = null,
        public ?string $barcode = null,
        public ?string $search = null,
        public ?string $sort_by = 'updated_at',
        public string $sort_dir = 'desc',
        public int $page = 1,
        public int $per_page = 15,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'item_uuid' => $this->item_uuid,
            'from_unit_uuid' => $this->from_unit_uuid,
            'to_unit_uuid' => $this->to_unit_uuid,
            'to_qty' => $this->to_qty,
            'conv_ratio' => $this->conv_ratio,
            'is_active' => $this->is_active,
            'is_base_uom' => $this->is_base_uom,
            'uom_code' => $this->uom_code,
            'barcode' => $this->barcode,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            // Scoping fields might be missing in item_conversions but present in item_uoms
            branch_uuid: $model->branch_uuid ?? null,
            company_uuid: $model->company_uuid ?? null,
            item_uuid: $model->item_uuid,
            from_unit_uuid: $model->from_unit_uuid ?? null,
            to_unit_uuid: $model->to_unit_uuid ?? null,
            to_qty: (float) ($model->to_qty ?? 1.0),
            conv_ratio: (float) ($model->conversion_ratio ?? 1.0),
            is_active: (bool) ($model->is_active ?? true),
            is_base_uom: (bool) ($model->is_base_uom ?? false),
            uom_code: $model->uom_code ?? null,
            barcode: $model->barcode ?? null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? ($data['branch_uuid'] ?? null),
            company_uuid: $data['company_uuid'] ?? ($data['company_uuid'] ?? null),
            item_uuid: $data['item_uuid'] ?? ($data['item_uuid'] ?? null),
            from_unit_uuid: $data['from_unit_uuid'] ?? ($data['unit_uuid'] ?? ($data['from_unit_uuid'] ?? null)),
            to_unit_uuid: $data['to_unit_uuid'] ?? ($data['to_unit_uuid'] ?? null),
            to_qty: (float) ($data['to_qty'] ?? 1.0),
            conv_ratio: (float) ($data['conv_ratio'] ?? ($data['conversion_ratio'] ?? ($data['ratio'] ?? 1.0))),
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            is_base_uom: filter_var($data['is_base_uom'] ?? ($data['is_base'] ?? false), FILTER_VALIDATE_BOOLEAN),
            uom_code: $data['uom_code'] ?? ($data['uom_name'] ?? null),
            barcode: $data['barcode'] ?? null,
            search: $data['search'] ?? null,
            sort_by: $data['sort_by'] ?? 'updated_at',
            sort_dir: $data['sort_dir'] ?? 'desc',
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
