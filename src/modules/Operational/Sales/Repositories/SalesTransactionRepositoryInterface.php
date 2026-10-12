<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Sales\Entities\SalesOrderEntity;
use App\Modules\Operational\Sales\Entities\DeliveryOrderEntity;
use App\Modules\Operational\Sales\Entities\SalesInvoiceEntity;
use App\Modules\Operational\Sales\Models\SalesOrder;
use App\Modules\Operational\Sales\Models\DeliveryOrder;
use App\Modules\Finance\Ledger\Models\SalesInvoice;

/**
 * SalesTransactionRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola siklus transaksi Penjualan (SO, DO, Invoice).
 */
interface SalesTransactionRepositoryInterface
{
    // Sales Order
    public function storeSO(SalesOrderEntity $entity): SalesOrderEntity;
    public function getPageDataSO(SalesOrderEntity $entity): array;
    public function getSOByUuid(string $uuid, string $branchUuid, string $companyUuid): ?SalesOrderEntity;
    public function getSOByOrderNo(string $orderNo, string $branchUuid, string $companyUuid): ?SalesOrderEntity;
    public function deleteSO(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void;

    // Delivery Order
    public function storeDO(DeliveryOrderEntity $entity): DeliveryOrderEntity;
    public function getPageDataDO(DeliveryOrderEntity $entity): array;
    public function getDOByUuid(string $uuid, string $branchUuid, string $companyUuid): ?DeliveryOrderEntity;
    public function getDOByDeliveryNo(string $deliveryNo, string $branchUuid, string $companyUuid): ?DeliveryOrderEntity;
    public function deleteDO(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void;

    // Sales Invoice
    public function storeInvoice(SalesInvoiceEntity $entity): SalesInvoiceEntity;
    public function getPageDataInvoice(SalesInvoiceEntity $entity): array;
    public function getInvoiceByUuid(string $uuid, string $branchUuid, string $companyUuid): ?SalesInvoiceEntity;
    public function getInvoiceByInvoiceNo(string $invoiceNo, string $branchUuid, string $companyUuid): ?SalesInvoiceEntity;
    public function deleteInvoice(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void;

    // Aging & Helpers
    public function storeAging(array $agingData): void;
    public function getAgingByInvoiceUuid(string $invoiceUuid, string $branchUuid, string $companyUuid): array;
    public function findSOForExport(SalesOrderEntity $entity): array;
    public function findDOForExport(DeliveryOrderEntity $entity): array;
    public function findInvoiceForExport(SalesInvoiceEntity $entity): array;
}

