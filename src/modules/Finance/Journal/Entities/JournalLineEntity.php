<?php

namespace App\Modules\Finance\Journal\Entities;

/**
 * JournalLineEntity
 * 
 * Entitas untuk mempermudah pemrosesan baris jurnal (item debet/kredit).
 */
class JournalLineEntity
{
    public function __construct(
        public string $account_uuid,
        public float $debit = 0,
        public float $credit = 0,
        public ?string $uuid = null,
        public ?string $contact_uuid = null,
        public ?string $description = null,
        public bool $is_fiscal = false,
        public ?string $fiscal_type = null, // positive/negative
        public ?string $branch_uuid = null,
        public bool $is_processed = false,
        public ?string $mapping_key = null,
        public bool $taxable = false,
        public ?string $tax_type = null,
        public ?float $tax_rate = null,
        public ?string $foreign_currency = null,
        public float $foreign_amount = 0.0,
        public float $exchange_rate = 1.0,
        // Legacy fields for Go-backwards-compatibility if needed
        public float $debit_fx = 0.0,
        public float $credit_fx = 0.0,
        public ?string $tax_uuid = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            account_uuid: $model->account_uuid,
            debit: (float) $model->debit,
            credit: (float) $model->credit,
            uuid: $model->uuid,
            contact_uuid: $model->contact_uuid,
            description: $model->description,
            is_fiscal: (bool) $model->is_fiscal,
            fiscal_type: $model->fiscal_type,
            branch_uuid: $model->branch_uuid,
            is_processed: (bool) $model->is_processed,
            mapping_key: $model->mapping_key,
            taxable: (bool) $model->taxable,
            tax_type: $model->tax_type,
            tax_rate: (float) $model->tax_rate,
            foreign_currency: $model->foreign_currency,
            foreign_amount: (float) ($model->foreign_amount ?? 0),
            exchange_rate: (float) ($model->exchange_rate ?? 1),
            debit_fx: (float) $model->debit_fx,
            credit_fx: (float) $model->credit_fx,
            tax_uuid: $model->tax_uuid,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            account_uuid: $data['account_uuid'] ?? '',
            debit: (float)($data['debit'] ?? 0),
            credit: (float)($data['credit'] ?? 0),
            uuid: $data['uuid'] ?? null,
            contact_uuid: $data['contact_uuid'] ?? null,
            description: $data['description'] ?? null,
            is_fiscal: $data['is_fiscal'] ?? false,
            fiscal_type: $data['fiscal_type'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            is_processed: $data['is_processed'] ?? false,
            mapping_key: $data['mapping_key'] ?? null,
            taxable: $data['taxable'] ?? false,
            tax_type: $data['tax_type'] ?? null,
            tax_rate: $data['tax_rate'] ?? null,
            foreign_currency: $data['foreign_currency'] ?? null,
            foreign_amount: (float) ($data['foreign_amount'] ?? 0),
            exchange_rate: (float) ($data['exchange_rate'] ?? 1),
            debit_fx: (float)($data['debit_fx'] ?? 0),
            credit_fx: (float)($data['credit_fx'] ?? 0),
            tax_uuid: $data['tax_uuid'] ?? null,
        );
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'branch_uuid' => $this->branch_uuid,
            'contact_uuid' => $this->contact_uuid,
            default => null,
        };
    }
}
