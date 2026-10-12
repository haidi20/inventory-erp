<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Entities\SalesOrderEntity;
use App\Modules\Operational\Sales\Entities\DeliveryOrderEntity;
use App\Modules\Operational\Sales\Entities\SalesInvoiceEntity;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * SalesTransactionService
 * 
 * Porting otomatis dari logic Sales domain (Go) ke Laravel Service layer.
 * Mengelola alur bisnis transaksi Penjualan mulai dari SO, DO, hingga Penagihan.
 */
class SalesTransactionService
{
    protected SalesTransactionRepositoryInterface $repository;

    public function __construct(SalesTransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    // --- Sales Order (SO) ---

    /** Get paginated Sales Orders. */
    public function indexSO(SalesOrderEntity $entity): Collection
    {
        $result = $this->repository->getPageDataSO($entity);
        return collect($result['items'] ?? []);
    }

    /** Store Sales Order. */
    public function storeSO(SalesOrderEntity $entity): SalesOrderEntity
    {
        if (empty($entity->sales_order_no)) {
            throw new \InvalidArgumentException('Nomor pesanan wajib diisi');
        }
        if (empty($entity->branch_uuid)) {
            throw new \InvalidArgumentException('Cabang wajib diisi');
        }
        if (empty($entity->person_uuid)) {
            throw new \InvalidArgumentException('Pelanggan wajib diisi');
        }
        if (empty($entity->location_uuid)) {
            throw new \InvalidArgumentException('Gudang wajib diisi');
        }
        if (empty($entity->payment_term_uuid)) {
            throw new \InvalidArgumentException('Termin pembayaran wajib diisi');
        }
        if (empty($entity->sales_order_date)) {
            throw new \InvalidArgumentException('Tanggal pesanan wajib diisi');
        }
        
        $entity->status = $entity->status ?: 'open';
        
        return $this->repository->storeSO($entity);
    }

    /** Get Sales Order by UUID. */
    public function showSO(string $uuid, string $branchUuid, string $companyUuid): ?SalesOrderEntity
    {
        return $this->repository->getSOByUuid($uuid, $branchUuid, $companyUuid);
    }

    /** Delete Sales Order. */
    public function destroySO(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        $this->repository->deleteSO($uuid, $branchUuid, $companyUuid, $actorUuid);
    }

    /** Export Sales Orders to array (for Excel/CSV generation). */
    public function exportSO(SalesOrderEntity $entity): array
    {
        return $this->repository->findSOForExport($entity);
    }

    // --- Delivery Order (DO) ---

    /** Get paginated Delivery Orders. */
    public function indexDO(DeliveryOrderEntity $entity): Collection
    {
        $result = $this->repository->getPageDataDO($entity);
        return collect($result['items'] ?? []);
    }

    /** Store Delivery Order. */
    public function storeDO(DeliveryOrderEntity $entity): DeliveryOrderEntity
    {
        if (empty($entity->delivery_no)) {
            throw new \InvalidArgumentException('Nomor pengiriman wajib diisi');
        }
        if (empty($entity->branch_uuid)) {
            throw new \InvalidArgumentException('Cabang wajib diisi');
        }
        if (empty($entity->location_uuid)) {
            throw new \InvalidArgumentException('Gudang wajib diisi');
        }
        if (empty($entity->person_uuid)) {
            throw new \InvalidArgumentException('Pelanggan wajib diisi');
        }
        if (empty($entity->details)) {
            throw new \InvalidArgumentException('Detail pengiriman tidak boleh kosong');
        }

        return $this->repository->storeDO($entity);
    }

    /** Get Delivery Order detail. */
    public function showDO(string $uuid, string $branchUuid, string $companyUuid): ?DeliveryOrderEntity
    {
        return $this->repository->getDOByUuid($uuid, $branchUuid, $companyUuid);
    }

    /** Delete Delivery Order. */
    public function destroyDO(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        $this->repository->deleteDO($uuid, $branchUuid, $companyUuid, $actorUuid);
    }

    /** Export Delivery Orders to array. */
    public function exportDO(DeliveryOrderEntity $entity): array
    {
        return $this->repository->findDOForExport($entity);
    }

    // --- Sales Invoice ---

    /** Get paginated Sales Invoices. */
    public function indexInvoice(SalesInvoiceEntity $entity): Collection
    {
        $result = $this->repository->getPageDataInvoice($entity);
        return collect($result['items'] ?? []);
    }

    /** Store Sales Invoice. */
    public function storeInvoice(SalesInvoiceEntity $entity): SalesInvoiceEntity
    {
        if (empty($entity->invoice_no)) {
            throw new \InvalidArgumentException('Nomor faktur wajib diisi');
        }
        if (empty($entity->branch_uuid)) {
            throw new \InvalidArgumentException('Cabang wajib diisi');
        }
        
        return $this->repository->storeInvoice($entity);
    }

    /** Get Sales Invoice detail. */
    public function showInvoice(string $uuid, string $branchUuid, string $companyUuid): ?SalesInvoiceEntity
    {
        return $this->repository->getInvoiceByUuid($uuid, $branchUuid, $companyUuid);
    }

    /** Get Invoice including Aging data. */
    public function getInvoiceWithAging(string $uuid, string $branchUuid, string $companyUuid): array
    {
        $invoice = $this->repository->getInvoiceByUuid($uuid, $branchUuid, $companyUuid);
        $agings = $this->repository->getAgingByInvoiceUuid($uuid, $branchUuid, $companyUuid);
        
        return [
            'invoice' => $invoice,
            'agings' => $agings
        ];
    }

    /** Delete Sales Invoice. */
    public function destroyInvoice(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void
    {
        $this->repository->deleteInvoice($uuid, $branchUuid, $companyUuid, $actorUuid);
    }

    /** Export Invoices to array. */
    public function exportInvoice(SalesInvoiceEntity $entity): array
    {
        return $this->repository->findInvoiceForExport($entity);
    }
}

