<?php

namespace App\Modules\Operational\Inventory\Entities;

use App\Modules\Operational\Sales\Enums\TransactionStatus;

class StockTransferEntity
{
    public function __construct(
        public ?string $uuid = null,
        public string $branch_uuid = '',
        public ?string $company_uuid = null,
        public string $source_location_uuid = '',
        public string $destination_location_uuid = '',
        public ?string $document_no = null,
        public int $date = 0,
        public TransactionStatus $status = TransactionStatus::DRAFT,
        public ?string $notes = null,
        public array $items = [],
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'source_location_uuid' => $this->source_location_uuid,
            'destination_location_uuid' => $this->destination_location_uuid,
            'document_no' => $this->document_no,
            'date' => $this->date,
            'status' => $this->status->value,
            'notes' => $this->notes,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            source_location_uuid: $model->source_location_uuid,
            destination_location_uuid: $model->destination_location_uuid,
            document_no: $model->no_pag ?? null,
            date: (int) $model->date,
            status: $model->status instanceof TransactionStatus ? $model->status : (TransactionStatus::tryFrom($model->status) ?? TransactionStatus::DRAFT),
            notes: $model->notes,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? '',
            company_uuid: $data['company_uuid'] ?? null,
            source_location_uuid: $data['source_location_uuid'] ?? '',
            destination_location_uuid: $data['destination_location_uuid'] ?? '',
            document_no: $data['document_no'] ?? null,
            date: isset($data['date']) ? (int) $data['date'] : time(),
            status: ($data['status'] ?? null) instanceof TransactionStatus ? $data['status'] : (is_string($data['status'] ?? null) ? (TransactionStatus::tryFrom($data['status']) ?? TransactionStatus::DRAFT) : TransactionStatus::DRAFT),
            notes: $data['notes'] ?? null,
            items: $data['items'] ?? [],
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}

