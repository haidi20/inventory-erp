<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * TaxEntity
 * 
 * Entitas untuk mengelola data pajak (Tax) dalam sistem.
 * Properti legacy dipetakan sebagai UUID untuk konsistensi dengan skema database.
 */
class TaxEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $tax_code = null,
        public float $percentage = 0.0,
        public ?string $legacy_tax_type_uuid = null,
        public ?string $legacy_sale_account_uuid = null,
        public ?string $legacy_purchase_account_uuid = null,
        public bool $is_active = true,
        public bool $is_primary = false,
        public bool $is_income_tax = false,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?bool $filter_active = null,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'tax_code' => $this->tax_code,
            'percentage' => $this->percentage,
            'legacy_tax_type_uuid' => $this->legacy_tax_type_uuid,
            'legacy_sale_account_uuid' => $this->legacy_sale_account_uuid,
            'legacy_purchase_account_uuid' => $this->legacy_purchase_account_uuid,
            'is_active' => $this->is_active,
            'is_primary' => $this->is_primary,
            'is_income_tax' => $this->is_income_tax,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            tax_code: $model->tax_code,
            percentage: (float) $model->percentage,
            legacy_tax_type_uuid: $model->legacy_tax_type_uuid,
            legacy_sale_account_uuid: $model->legacy_sale_account_uuid,
            legacy_purchase_account_uuid: $model->legacy_purchase_account_uuid,
            is_active: (bool) $model->is_active,
            is_primary: (bool) ($model->is_primary ?? false),
            is_income_tax: (bool) ($model->is_income_tax ?? false),
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            tax_code: $data['tax_code'] ?? null,
            percentage: (float) ($data['percentage'] ?? 0.0),
            legacy_tax_type_uuid: $data['legacy_tax_type_uuid'] ?? null,
            legacy_sale_account_uuid: $data['legacy_sale_account_uuid'] ?? null,
            legacy_purchase_account_uuid: $data['legacy_purchase_account_uuid'] ?? null,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            is_primary: filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            is_income_tax: filter_var($data['is_income_tax'] ?? false, FILTER_VALIDATE_BOOLEAN),
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
        );
    }
}
