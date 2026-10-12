<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Operational\Inventory\Models\ItemOpeningBalance;
use App\Modules\Operational\Master\Models\PersonBalance;
use App\Modules\Operational\Master\Models\PersonSaldo;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class InitialBalanceRepository
 *
 * Implementasi repository saldo awal (Initial Balance) menggunakan Eloquent.
 */
class InitialBalanceRepository implements InitialBalanceRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getInitialBalances(array $filter): array
    {
        // Relasi 'unit' tidak ada pada model (tabel item_opening_balances tidak
        // punya kolom unit_uuid); eager-load hanya relasi yang benar-benar ada.
        $query = ItemOpeningBalance::with(['item', 'warehouse']);

        if (!empty($filter['branch_uuid'])) {
            $query->where('branch_uuid', $filter['branch_uuid']);
        }
        if (!empty($filter['company_uuid'])) {
            $query->where('company_uuid', $filter['company_uuid']);
        }
        if (!empty($filter['location_uuid'])) {
            $query->where('location_uuid', $filter['location_uuid']);
        }

        $total = $query->count();
        $page = $filter['page'] ?? 1;
        $perPage = $filter['per_page'] ?? 15;

        $items = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'items' => $items->toArray(),
            'total' => $total
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByItem(string $itemUuid, string $unitUuid, string $warehouseUuid): ?ItemOpeningBalance
    {
        return ItemOpeningBalance::where('item_uuid', $itemUuid)
            ->where('unit_uuid', $unitUuid)
            ->where('location_uuid', $warehouseUuid)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function saveItemBalance(array $data): ?OpeningBalanceEntity
    {
        $uuid = $data['uuid'] ?? (string) Str::uuid();

        $balance = ItemOpeningBalance::updateOrCreate(
            [
                'item_uuid' => $data['item_uuid'],
                'unit_uuid' => $data['unit_uuid'],
                'location_uuid' => $data['location_uuid'],
                'branch_uuid' => $data['branch_uuid'],
                'company_uuid' => $data['company_uuid']
            ],
            [
                'qty' => $data['qty'],
                'price' => $data['price'] ?? 0,
                'total' => $data['total'] ?? 0,
                'date' => $data['date'] ?? date('Y-m-d'),
            ]
        );
        return OpeningBalanceEntity::fromModel($balance);
    }

    /**
     * @inheritDoc
     */
    public function getPersonBalances(array $filter): array
    {
        $query = PersonBalance::with(['person']);

        if (!empty($filter['branch_uuid'])) {
            $query->where('branch_uuid', $filter['branch_uuid']);
        }
        if (!empty($filter['person_type'])) {
            $query->join('persons', 'persons.uuid', '=', 'person_balances.person_uuid')
                ->where('persons.type', $filter['person_type']);
        }

        $total = $query->count();
        $page = $filter['page'] ?? 1;
        $perPage = $filter['per_page'] ?? 15;

        $items = $query->orderBy('person_balances.created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'items' => $items->toArray(),
            'total' => $total
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPersonSaldo(string $personUuid, string $branchUuid): array
    {
        $saldo = PersonSaldo::where('person_uuid', $personUuid)
            ->where('branch_uuid', $branchUuid)
            ->first();

        if (!$saldo) {
            return [
                'total_balance' => 0,
                'total_used' => 0,
                'remaining_balance' => 0
            ];
        }

        return [
            'total_balance' => $saldo->total_balance,
            'total_used' => $saldo->total_used,
            'remaining_balance' => $saldo->total_balance - $saldo->total_used
        ];
    }

    /**
     * @inheritDoc
     */
    public function savePersonBalance(array $data): ?OpeningBalanceEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $data['uuid'] ?? (string) Str::uuid();

            $balance = PersonBalance::updateOrCreate(
                ['uuid' => $uuid, 'branch_uuid' => $data['branch_uuid'], 'company_uuid' => $data['company_uuid']],
                [
                    'person_uuid' => $data['person_uuid'],
                    'balance_amount' => $data['balance_amount'],
                    'date' => $data['date'] ?? date('Y-m-d'),
                    'notes' => $data['notes'] ?? null,
                ]
            );

            // Update Saldo (Simplified, usually in BE this happens in service but keeping parity with repository logic if any)
            $existingSaldo = PersonSaldo::where('person_uuid', $data['person_uuid'])
                ->where('branch_uuid', $data['branch_uuid'])
                ->first();

            if ($existingSaldo) {
                $existingSaldo->update([
                    'total_balance' => PersonBalance::where('person_uuid', $data['person_uuid'])
                        ->where('branch_uuid', $data['branch_uuid'])
                        ->sum('balance_amount')
                ]);
            } else {
                PersonSaldo::create([
                    'uuid' => (string) Str::uuid(),
                    'person_uuid' => $data['person_uuid'],
                    'branch_uuid' => $data['branch_uuid'],
                    'company_uuid' => $data['company_uuid'],
                    'total_balance' => $data['balance_amount'],
                    'total_used' => 0
                ]);
            }

            DB::commit();
            return OpeningBalanceEntity::fromModel($balance);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
