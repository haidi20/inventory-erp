<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;

/**
 * PaymentTermRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola data Termin Pembayaran (Payment Term).
 */
interface PaymentTermRepositoryInterface
{
    /**
     * Mendapatkan data halaman termin pembayaran dengan filter dan pencarian.
     * 
     * @param PaymentTermEntity $entity
     * @return array
     */
    public function getPageData(PaymentTermEntity $entity): array;

    /**
     * Mencari termin pembayaran berdasarkan UUID, difilter per company.
     *
     * @param PaymentTermEntity $entity
     * @return PaymentTermEntity|null
     */
    public function findByUuid(PaymentTermEntity $entity): ?PaymentTermEntity;

    /**
     * Menyimpan atau memperbarui data termin pembayaran.
     * 
     * @param PaymentTermEntity $entity
     * @return PaymentTermEntity
     */
    public function save(PaymentTermEntity $entity): PaymentTermEntity;

    /**
     * Menghapus data termin pembayaran (Soft Delete).
     * 
     * @param PaymentTermEntity $entity
     * @return void
     */
    public function delete(PaymentTermEntity $entity): void;

    /**
     * Mendapatkan daftar termin pembayaran untuk kebutuhan ekspor data.
     * 
     * @param PaymentTermEntity $entity
     * @return array<PaymentTermEntity>
     */
    public function listForExport(PaymentTermEntity $entity): array;
}
