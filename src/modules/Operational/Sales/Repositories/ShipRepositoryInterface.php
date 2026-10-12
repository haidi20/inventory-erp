<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Master\Models\Shift;

/**
 * Interface ShipRepositoryInterface
 *
 * Kontrak untuk pembacaan shift operasional.
 */
interface ShipRepositoryInterface
{
    /**
     * Mengambil shift aktif yang belum ditutup pada warehouse tertentu.
     *
     * @param string $warehouseId
     * @return Shift|null
     */
    public function getOpenShift(string $warehouseId): ?Shift;
}
