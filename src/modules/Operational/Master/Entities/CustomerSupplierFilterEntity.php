<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * Filter pencarian direktori customer & supplier.
 * Dipakai CustomerSupplierService/CustomerSupplierRepository; satu properti
 * `search` sesuai kontrak pemakaian aktual (validatedSearch + query LIKE).
 */
class CustomerSupplierFilterEntity
{
    public function __construct(
        public ?string $search = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
        ];
    }
}