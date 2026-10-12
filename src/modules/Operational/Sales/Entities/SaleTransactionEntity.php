<?php

namespace App\Modules\Operational\Sales\Entities;

class SaleTransactionEntity
{
    /**
     * @param SaleItemEntity[] $items
     */
    public function __construct(
        public ?string $patient_uuid = null,
        public ?string $cashier_uuid = null,
        public float|int|null $paid = null,
        public ?string $status = null,
        public array $items = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn (array $item): SaleItemEntity => SaleItemEntity::fromArray($item),
            array_values(array_filter($data['items'] ?? [], fn ($item) => is_array($item)))
        );

        return new self(
            patient_uuid: $data['patient_uuid'] ?? null,
            cashier_uuid: $data['cashier_uuid'] ?? null,
            paid: isset($data['paid']) && $data['paid'] !== '' ? (float) $data['paid'] : null,
            status: isset($data['status']) ? trim((string) $data['status']) : null,
            items: $items,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_uuid' => $this->patient_uuid,
            'cashier_uuid' => $this->cashier_uuid,
            'paid' => $this->paid,
            'status' => $this->status,
            'items' => array_map(
                fn (SaleItemEntity $item): array => $item->toArray(),
                $this->items
            ),
        ];
    }
}

