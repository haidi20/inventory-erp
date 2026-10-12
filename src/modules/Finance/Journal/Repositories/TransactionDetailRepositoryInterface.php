<?php

namespace App\Modules\Finance\Journal\Repositories;

/**
 * Interface TransactionDetailRepositoryInterface
 *
 * Kontrak untuk pengelolaan detail transaksi keuangan.
 */
interface TransactionDetailRepositoryInterface
{
    /**
     * Membuat detail transaksi secara bulk. 
     * Menghapus detail lama (soft-delete) untuk transaksi yang sama jika ada.
     */
    public function createTransactionDetails(string $transactionId, string $actorId, array $details): array;
}
