<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Ledger\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class TransactionDetailRepository
 *
 * Implementasi repository detail transaksi keuangan menggunakan Eloquent.
 */
class TransactionDetailRepository implements TransactionDetailRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function createTransactionDetails(string $transactionId, string $actorId, array $details): array
    {
        DB::beginTransaction();
        try {
            // Soft delete existing details for this transaction
            TransactionDetail::where('transaction_uuid', $transactionId)->delete();

            if (empty($details)) {
                DB::commit();
                return [];
            }

            $createdDetails = [];
            foreach ($details as $detail) {
                $createdDetails[] = TransactionDetail::create([
                    'uuid' => $detail['uuid'] ?? (string) Str::uuid(),
                    'transaction_uuid' => $transactionId,
                    'account_uuid' => $detail['account_uuid'],
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                    'note' => $detail['note'] ?? null,
                    'is_active' => true,
                ]);
            }

            DB::commit();
            return $createdDetails;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

