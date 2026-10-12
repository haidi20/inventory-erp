<?php

namespace App\Modules\Core\Platform\Entities;

class DatabaseBackupHistoryTableEntity
{
    public function __construct(
        public ?int $draw = 1,
        public ?int $start = 0,
        public ?int $length = 10,
        public ?string $search = null,
        public ?string $history_type = 'manual',
        public ?int $order_column = 3,
        public ?string $order_direction = 'desc',
    ) {
    }

    public static function fromArray(array $data): self
    {
        $searchValue = null;
        if (isset($data['search']) && is_array($data['search'])) {
            $searchValue = $data['search']['value'] ?? null;
        } else {
            $searchValue = $data['search'] ?? null;
        }

        $orderColumn = $data['order'][0]['column'] ?? $data['order_column'] ?? 3;
        $orderDirection = $data['order'][0]['dir'] ?? $data['order_direction'] ?? 'desc';

        return new self(
            draw: is_scalar($data['draw'] ?? null) ? (int) $data['draw'] : 1,
            start: is_scalar($data['start'] ?? null) ? (int) $data['start'] : 0,
            length: is_scalar($data['length'] ?? null) ? (int) $data['length'] : 10,
            search: is_scalar($searchValue) ? trim((string) $searchValue) : null,
            history_type: is_scalar($data['history_type'] ?? null) ? (string) $data['history_type'] : 'manual',
            order_column: is_scalar($orderColumn) ? (int) $orderColumn : 3,
            order_direction: is_scalar($orderDirection) ? strtolower((string) $orderDirection) : 'desc',
        );
    }
}

