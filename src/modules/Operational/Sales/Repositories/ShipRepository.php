<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Master\Models\Shift;

/**
 * Class ShipRepository
 *
 * Implementasi repository shift operasional menggunakan Eloquent.
 */
class ShipRepository implements ShipRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getOpenShift(string $warehouseId): ?Shift
    {
        return Shift::where('location_uuid', $warehouseId)
            ->where('is_closed', false)
            ->first();
    }
}

