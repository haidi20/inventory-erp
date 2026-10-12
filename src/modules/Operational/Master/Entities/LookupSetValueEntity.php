<?php

namespace App\Modules\Operational\Master\Entities;

class LookupSetValueEntity
{
    public function __construct(
        public ?string $uuid = null,
        public int|string|null $lookup_set_uuid = null,
        public ?string $program_code1 = null,
        public ?string $display_name = null,
        public ?string $description = null,
        public int $seq = 0,
        public string $active_flag = 'Y',
        public string $primary_flag = 'N',
        public float $price_up_beli = 0.0,
        public float $diskon_pilih = 0.0,
        public float $persen1 = 0.0,
        public float $persen2 = 0.0,
        public array $details = [],
        public ?string $company_uuid = null,
        public ?string $search = null,
        public int $start = 0,
        public int $length = 10,
        public ?string $actor_uuid = null,
        public ?int $start_date = null,
        public ?int $end_date = null,
        public ?float $max_stock = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            lookup_set_uuid: $data['lookup_set_uuid'] ?? null,
            program_code1: $data['program_code1'] ?? ($data['program_code'] ?? null),
            display_name: $data['display_name'] ?? null,
            description: $data['description'] ?? null,
            seq: (int) ($data['seq'] ?? 0),
            active_flag: (string) ($data['active_flag'] ?? 'Y'),
            primary_flag: (string) ($data['primary_flag'] ?? 'N'),
            price_up_beli: (float) ($data['price_up_beli'] ?? 0),
            diskon_pilih: (float) ($data['diskon_pilih'] ?? 0),
            persen1: (float) ($data['persen1'] ?? 0),
            persen2: (float) ($data['persen2'] ?? 0),
            details: (array) ($data['details'] ?? []),
            company_uuid: $data['company_uuid'] ?? null,
            search: $data['search'] ?? null,
            start: (int) ($data['start'] ?? 0),
            length: (int) ($data['length'] ?? 10),
            actor_uuid: $data['actor_uuid'] ?? null,
            start_date: isset($data['start_date']) ? (int) $data['start_date'] : null,
            end_date: isset($data['end_date']) ? (int) $data['end_date'] : null,
            max_stock: isset($data['max_stock']) ? (float) $data['max_stock'] : null,
        );
    }

    public static function fromModel(object $model): self
    {
        return new self(
            uuid: $model->uuid ?? null,
            lookup_set_uuid: $model->lookup_set_uuid ?? null,
            program_code1: $model->program_code ?? null,
            display_name: $model->display_name ?? $model->value ?? null,
            description: $model->description ?? null,
            seq: (int) ($model->seq ?? $model->order_priority ?? 0),
            active_flag: (string) ($model->active_flag ?? 'Y'),
            primary_flag: (string) ($model->primary_flag ?? 'N'),
            price_up_beli: (float) ($model->price_up_beli ?? 0),
            diskon_pilih: (float) ($model->diskon_pilih ?? 0),
            persen1: (float) ($model->persen1 ?? 0),
            persen2: (float) ($model->persen2 ?? 0),
            details: isset($model->details) && is_iterable($model->details)
                ? collect($model->details)->map(fn ($detail) => [
                    'idx' => (int) ($detail->idx ?? $detail['idx'] ?? 0),
                    'value' => $detail->value ?? $detail['value'] ?? null,
                ])->all()
                : [],
            company_uuid: $model->company_uuid ?? null,
            start_date: isset($model->start_date) ? (int) $model->start_date : null,
            end_date: isset($model->end_date) ? (int) $model->end_date : null,
            max_stock: isset($model->max_stock) ? (float) $model->max_stock : null,
        );
    }
}