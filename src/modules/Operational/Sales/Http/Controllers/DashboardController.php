<?php

namespace App\Modules\Operational\Sales\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Sales\Entities\InventoryDashboardEntity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(): mixed
    {
        $user = auth()->user();

        if (!$user->can('dashboard.view')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = InventoryDashboardEntity::dummy();

        return Inertia::render('Analytics/Reports/presentations/views/DashboardIndex', $data->toArray());
    }

    public function tabData(Request $request): JsonResponse
    {
        $tab     = $request->input('tab', 'stok');
        $search  = strtolower(trim((string) $request->input('search', '')));
        $perPage = (int) $request->input('per_page', 10);
        $page    = (int) $request->input('page', 1);

        $dummy = InventoryDashboardEntity::dummy();

        $items = match ($tab) {
            'stok'       => $dummy->stokItems,
            'beli'       => $this->flattenBeliItems($dummy->beliItems),
            'omzet'      => $dummy->omzetItems,
            'omzetToday' => $dummy->omzetTodayItems,
            'hutang'     => $dummy->hutangItems,
            'piutang'    => $dummy->piutangItems,
            default      => [],
        };

        if ($search !== '') {
            $items = array_values(array_filter($items, function ($item) use ($search) {
                foreach ($item as $value) {
                    if (str_contains(strtolower((string) $value), $search)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        $total  = count($items);
        $offset = ($page - 1) * $perPage;
        $data   = array_slice($items, $offset, $perPage);

        return response()->json([
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    private function flattenBeliItems(array $beliItems): array
    {
        $rows = [];
        foreach ($beliItems as $po) {
            foreach ($po['items'] as $item) {
                $rows[] = [
                    'tanggal'      => $po['tanggal'],
                    'no_transaksi' => $po['no_transaksi'],
                    'supplier'     => $po['supplier'],
                    'kode'         => $item['kode'],
                    'nama'         => $item['nama'],
                    'qty_order'    => $item['qty_order'],
                    'qty_terima'   => $item['qty_terima'],
                    'qty_sisa'     => $item['qty_sisa'],
                ];
            }
        }
        return $rows;
    }
}
