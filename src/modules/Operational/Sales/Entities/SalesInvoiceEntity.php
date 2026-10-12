<?php

namespace App\Modules\Operational\Sales\Entities;

/**
 * SalesInvoiceEntity
 * 
 * Data Transfer Object untuk transaksi Sales Invoice.
 */
class SalesInvoiceEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $person_uuid = null,
        public ?string $payment_term_uuid = null,
        public ?string $location_uuid = null,
        public ?string $invoice_no = null,
        public ?int $invoice_date = null,
        public ?string $status = null,
        public array $details = [],
        public array $agings = [],
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
            'payment_term_uuid' => $this->payment_term_uuid,
            'location_uuid' => $this->location_uuid,
            'invoice_no' => $this->invoice_no,
            'invoice_date' => $this->invoice_date,
            'status' => $this->status,
            'details' => $this->details,
            'agings' => $this->agings,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            person_uuid: $model->person_uuid,
            payment_term_uuid: $model->payment_term_uuid,
            location_uuid: $model->location_uuid,
            invoice_no: $model->invoice_no,
            invoice_date: (int) $model->invoice_date,
            status: $model->status,
            details: $model->relationLoaded('details') ? $model->details->toArray() : [],
            agings: $model->relationLoaded('agings') ? $model->agings->toArray() : [],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            person_uuid: $data['person_uuid'] ?? null,
            payment_term_uuid: $data['payment_term_uuid'] ?? null,
            location_uuid: $data['location_uuid'] ?? null,
            invoice_no: $data['invoice_no'] ?? null,
            invoice_date: isset($data['invoice_date']) ? (int) $data['invoice_date'] : null,
            status: $data['status'] ?? null,
            details: $data['details'] ?? [],
            agings: $data['agings'] ?? [],
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

