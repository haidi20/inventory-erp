<?php

namespace App\Modules\Finance\Ledger\Repositories;

use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceFilterEntity;

class OpeningBalanceRepository implements OpeningBalanceRepositoryInterface
{
    public function getOpeningBalanceHeader(string $uuid): ?object
    {
        return DB::table('opening_balance_headers')->where('uuid', $uuid)->whereNull('deleted_at')->first();
    }

    public function getOpeningBalanceHeaders(OpeningBalanceFilterEntity $filter): array
    {
        $query = DB::table('opening_balance_headers')->whereNull('deleted_at');
        
        if ($filter->branch_uuid) {
            $query->where('branch_uuid', $filter->branch_uuid);
        }

        if ($filter->type) {
            $query->where('type', $filter->type);
        }

        $total = $query->count();
        $data = $query->offset(($filter->page - 1) * $filter->per_page)
                      ->limit($filter->per_page)
                      ->orderBy('date', 'desc')
                      ->get()
                      ->toArray();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $filter->per_page,
            'current_page' => $filter->page
        ];
    }

    public function createOpeningBalanceHeader(OpeningBalanceEntity $entity): ?object
    {
        DB::beginTransaction();
        try {
            $data = [
                'uuid' => $entity->uuid,
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'transaction_no' => $entity->transaction_no,
                'transaction_date' => $entity->transaction_date,
                'period_month' => $entity->period_month,
                'type' => $entity->type,
                'note' => $entity->note,
                'is_posted' => $entity->is_posted,
                'created_at' => time(),
                'updated_at' => time(),
                'created_by' => $entity->actor_uuid,
            ];
            DB::table('opening_balance_headers')->insert($data);
            DB::commit();
            return $this->getOpeningBalanceHeader($entity->uuid);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOpeningBalanceHeader(OpeningBalanceEntity $entity): bool
    {
        DB::beginTransaction();
        try {
            $data = [
                'is_posted' => $entity->is_posted,
                'updated_at' => time(),
                'updated_by' => $entity->actor_uuid,
            ];
            DB::commit();
            return DB::table('opening_balance_headers')->where('uuid', $entity->uuid)->update($data) >= 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findMappingByKey(string $key): ?object
    {
        return DB::table('account_mappings')->where('mapping_key', $key)->whereNull('deleted_at')->first();
    }

    public function getOpeningBalances(string $branchUuid): array
    {
        return DB::table('account_balances')
            ->where('branch_uuid', $branchUuid)
            ->whereNull('deleted_at')
            ->get()
            ->toArray();
    }

    public function getItemOpeningBalances(string $branchUuid): array
    {
        return [];
    }

    public function updateOpeningBalance(string $branchUuid, string $accountCode, float $amount): bool
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return DB::table('account_balances')
                ->updateOrInsert(
                    ['branch_uuid' => $branchUuid, 'account_code' => $accountCode],
                    ['opening_balance' => $amount, 'updated_at' => time()]
                );
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
