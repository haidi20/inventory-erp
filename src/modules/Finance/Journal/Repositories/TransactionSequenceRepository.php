<?php

namespace App\Modules\Finance\Journal\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Journal\Models\TransactionSequence;

/**
 * Repository untuk penomoran transaksi otomatis.
 */
class TransactionSequenceRepository
{
    /**
     * Mendapatkan sequence dengan lock untuk update demi mencegah duplikasi.
     */
    public function getAndLock(string $branchUuid, string $typeCode, string $period): ?TransactionSequence
    {
        return TransactionSequence::where('branch_uuid', $branchUuid)
            ->where('type_code', $typeCode)
            ->where('period', $period)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Membuat sequence baru.
     */
    public function create(array $data): TransactionSequence
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return TransactionSequence::create($data);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mendapatkan sequence berdasarkan ID dengan lock.
     */
    public function findAndLock(string $uuid): ?TransactionSequence
    {
        return TransactionSequence::where('uuid', $uuid)
            ->lockForUpdate()
            ->first();
    }

    public function nextIndex(string $branchUuid, string $typeCode, string $period): int
    {
        DB::beginTransaction();
        try {
            $sequence = $this->getAndLock($branchUuid, $typeCode, $period);

            if (!$sequence) {
                $sequence = $this->create([
                    'branch_uuid' => $branchUuid,
                    'type_code' => $typeCode,
                    'period' => $period,
                    'last_index' => 0,
                ]);
                $sequence = $this->findAndLock($sequence->uuid);
            }

            $sequence->last_index += 1;
            $sequence->save();

            DB::commit();
            return (int) $sequence->last_index;
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;
        }
    }
}
