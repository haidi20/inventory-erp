<?php

namespace App\Modules\Operational\Inventory\Repositories;

/**
 * Interface StockBalanceRepositoryInterface
 *
 * Kontrak untuk pelaporan saldo stok inventory.
 */
interface StockBalanceRepositoryInterface
{
    /**
     * Mengambil laporan saldo stok berdasarkan filter tanggal dan lokasi.
     *
     * @param array $filter
     * @return array
     */
    public function getStockBalanceReport(array $filter): array;
}
