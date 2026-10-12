<?php

namespace App\Modules\Operational\Sales\Entities;

/**
 * SalesOrderEntity
 * 
 * Entitas untuk mengelola dokumen Pesanan Penjualan (Sales Order).
 */
class SalesOrderEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $sales_order_no = null,
        public ?string $sales_order_date = null,
        public string $status = 'Open',
        public ?string $description = null,
        public array $details = [],
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 10,
        public ?string $actor_uuid = null
    ) {}
}

/**
 * DeliveryOrderEntity
 * 
 * Entitas untuk mengelola dokumen Pengiriman Barang (Delivery Order).
 */
class DeliveryOrderEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $delivery_no = null,
        public ?string $delivery_date = null,
        public ?string $description = null,
        public array $details = [],
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 10,
        public ?string $actor_uuid = null
    ) {}
}

/**
 * SalesInvoiceEntity
 * 
 * Entitas untuk mengelola dokumen Faktur Penjualan (Sales Invoice).
 */
class SalesInvoiceEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $invoice_no = null,
        public ?string $invoice_date = null,
        public string $status = 'Posted',
        public array $details = [],
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 10,
        public ?string $actor_uuid = null
    ) {}
}

