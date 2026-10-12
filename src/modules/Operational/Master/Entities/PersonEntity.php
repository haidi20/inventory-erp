<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * PersonEntity
 * 
 * Entitas untuk mengelola data subjek (Person) seperti Customer dan Supplier.
 */
class PersonEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $name = null,
        public ?string $code = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public bool $is_supplier = false,
        public ?string $location_uuid = null,
        public ?string $description = null,
        public bool $is_cash = false,
        public ?string $note = null,
        public float $receivable_limit = 0.0,
        public float $payable_limit = 0.0,
        public bool $is_active = true,
        public ?string $payment_term_uuid = null,
        public float $opening_balance = 0.0,
        public ?string $employee_uuid = null,
        public ?string $region = null,
        public ?string $npwp_no = null,
        public ?string $price_level_uuid = null,
        public ?string $type = null,
        public ?string $payable_account_uuid = null,
        public ?string $receivable_account_uuid = null,
        public ?string $company_type_uuid = null,
        public ?string $currency_uuid = null,
        public ?string $customer_type_uuid = null,
        public ?string $alternate_name = null,
        public bool $is_approved = false,
        public ?string $discount_percentage = null,
        public ?string $tax_code = null,
        public float $discount_amount = 0.0,
        public float $loyalty_points = 0.0,
        public array $custom_fields = [],
        public ?string $search = null,
        public ?string $search_column = null,
        public ?int $supplier_flag = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'code' => $this->code,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'is_supplier' => $this->is_supplier,
            'description' => $this->description,
            'is_cash' => $this->is_cash ? 'Y' : 'N',
            'note' => $this->note,
            'receivable_limit' => $this->receivable_limit,
            'payable_limit' => $this->payable_limit,
            'is_active' => $this->is_active ? 'Y' : 'N',
            'payment_term_uuid' => $this->payment_term_uuid,
            'opening_balance' => $this->opening_balance,
            'employee_uuid' => $this->employee_uuid,
            'region' => $this->region,
            'npwp_no' => $this->npwp_no,
            'price_level_uuid' => $this->price_level_uuid,
            'type' => $this->type,
            'payable_account_uuid' => $this->payable_account_uuid,
            'receivable_account_uuid' => $this->receivable_account_uuid,
            'company_type_uuid' => $this->company_type_uuid,
            'currency_uuid' => $this->currency_uuid,
            'customer_type_uuid' => $this->customer_type_uuid,
            'alternate_name' => $this->alternate_name,
            'is_approved' => $this->is_approved,
            'discount_percentage' => $this->discount_percentage,
            'tax_code' => $this->tax_code,
            'discount_amount' => $this->discount_amount,
            'loyalty_points' => $this->loyalty_points,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            name: $model->name,
            code: $model->code,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            is_supplier: (bool) $model->is_supplier,
            description: $model->description,
            note: $model->note,
            receivable_limit: (float) ($model->receivable_limit ?? 0),
            payable_limit: (float) ($model->payable_limit ?? 0),
            is_active: in_array($model->is_active, [true, 1, '1', 'Y', 'y'], true),
            payment_term_uuid: $model->payment_term_uuid ?? null,
            opening_balance: (float) ($model->opening_balance ?? 0),
            employee_uuid: $model->employee_uuid ?? null,
            region: $model->region,
            npwp_no: $model->npwp_no,
            price_level_uuid: $model->price_level_uuid ?? null,
            type: $model->type,
            custom_fields: method_exists($model, 'relationLoaded') && $model->relationLoaded('customFields')
                ? $model->customFields->toArray()
                : [],
        );
    }
}
