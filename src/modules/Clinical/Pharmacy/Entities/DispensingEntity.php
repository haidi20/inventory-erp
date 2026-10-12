<?php

namespace App\Modules\Clinical\Pharmacy\Entities;

class DispensingEntity
{
    /**
     * @param DispensingItemEntity[] $items
     */
    public function __construct(
        public ?string $uuid = null,
        public ?string $dispensing_no = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $patient_uuid = null,
        public ?string $dispensed_by_uuid = null,
        public ?string $notes = null,
        public float|int|null $total = null,
        public ?string $dispensed_at = null,
        public array $items = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(function ($item) {
            return $item instanceof DispensingItemEntity ? $item : DispensingItemEntity::fromArray($item);
        }, $data['items'] ?? []);

        return new self(
            uuid: $data['uuid'] ?? null,
            dispensing_no: $data['dispensing_no'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            patient_uuid: $data['patient_uuid'] ?? null,
            dispensed_by_uuid: $data['dispensed_by_uuid'] ?? null,
            notes: $data['notes'] ?? null,
            total: isset($data['total']) ? (float) $data['total'] : null,
            dispensed_at: $data['dispensed_at'] ?? null,
            items: $items,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'dispensing_no'     => $this->dispensing_no,
            'branch_uuid'       => $this->branch_uuid,
            'company_uuid'      => $this->company_uuid,
            'patient_uuid'      => $this->patient_uuid,
            'dispensed_by_uuid' => $this->dispensed_by_uuid,
            'notes'             => $this->notes,
            'total'             => $this->total,
            'dispensed_at'      => $this->dispensed_at,
        ], fn($v) => $v !== null);
    }
}

