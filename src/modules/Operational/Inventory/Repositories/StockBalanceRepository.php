<?php

namespace App\Modules\Operational\Inventory\Repositories;

use App\Modules\Operational\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Class StockBalanceRepository
 *
 * Implementasi repository pelaporan saldo stok menggunakan raw query untuk performa agregasi.
 */
class StockBalanceRepository implements StockBalanceRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getStockBalanceReport(array $filter): array
    {
        $branchUuid = $filter['branch_uuid'];
        $companyUuid = $filter['company_uuid'];
        $warehouseUuid = $filter['location_uuid'] ?? null;
        $dateStart = $filter['date_start'] ?? null;
        $dateEnd = $filter['date_end'] ?? null;

        // Subquery untuk saldo awal (movements sebelum dateStart)
        $openingQuery = DB::table('stock_movements')
            ->select('product_uuid', 'location_uuid', DB::raw('SUM(qty) as saldo_awal'))
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->where('deleted_at', 0);
            
        if ($dateStart) {
            $openingQuery->where('created_at', '<', $dateStart);
        } else {
            $openingQuery->whereRaw('1 = 0');
        }
        if ($warehouseUuid) {
            $openingQuery->where('location_uuid', $warehouseUuid);
        }
        $openingQuery->groupBy('product_uuid', 'location_uuid');
 
        // Main Query
        $mainQuery = DB::table('items as i')
            ->select('i.uuid as product_uuid', 'i.code', 'i.name', 'i.barcode')
            ->selectRaw('u.name as unit_name')
            ->selectRaw('w.name as warehouse_name')
            ->selectRaw('COALESCE(op.saldo_awal, 0) as opening_balance')
            ->selectRaw('SUM(CASE WHEN m.qty > 0 THEN m.qty ELSE 0 END) as qty_in')
            ->selectRaw('SUM(CASE WHEN m.qty < 0 THEN ABS(m.qty) ELSE 0 END) as qty_out')
            ->selectRaw('COALESCE(op.saldo_awal, 0) + SUM(m.qty) as current_balance')
            ->leftJoin('stock_movements as m', function ($join) use ($dateStart, $dateEnd) {
                $join->on('i.uuid', '=', 'm.product_uuid');
                if ($dateStart) $join->where('m.created_at', '>=', $dateStart);
                if ($dateEnd) $join->where('m.created_at', '<=', $dateEnd);
                $join->where('m.deleted_at', 0);
            })
            ->leftJoin('units as u', function($join) {
                $join->on('i.base_unit_uuid', '=', 'u.uuid')
                     ->where('u.deleted_at', 0);
            })
            ->leftJoin('locations as w', function($join) {
                $join->on('m.location_uuid', '=', 'w.uuid')
                     ->where('w.deleted_at', 0);
            })
            ->leftJoinSub($openingQuery, 'op', function ($join) {
                $join->on('i.uuid', '=', 'op.product_uuid')
                    ->on('m.location_uuid', '=', 'op.location_uuid');
            })
            ->where('i.branch_uuid', $branchUuid)
            ->where('i.company_uuid', $companyUuid)
            ->where('i.deleted_at', 0);
 
        if ($warehouseUuid) {
            $mainQuery->where('m.location_uuid', $warehouseUuid);
        }
 
        if (!empty($filter['search'])) {
            $s = '%' . strtolower($filter['search']) . '%';
            $mainQuery->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(i.name) LIKE ?', [$s])
                    ->orWhereRaw('LOWER(i.code) LIKE ?', [$s]);
            });
        }

        $items = $mainQuery->groupBy('i.uuid', 'i.code', 'i.name', 'i.barcode', 'u.name', 'w.name', 'op.saldo_awal')
            ->orderBy('i.name', 'asc')
            ->get();

        return $items->toArray();
    }
}
