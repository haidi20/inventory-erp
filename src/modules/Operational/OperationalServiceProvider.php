<?php

namespace App\Modules\Operational;

use Illuminate\Support\ServiceProvider;
use App\Modules\Operational\Master\Repositories\LookupRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LookupRepository;
use App\Modules\Operational\Master\Repositories\LookupSetRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LookupSetRepository;
use App\Modules\Operational\Master\Repositories\EmployeeRepositoryInterface;
use App\Modules\Operational\Master\Repositories\EmployeeRepository;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use App\Modules\Operational\Master\Repositories\ProductRepository;
use App\Modules\Operational\Master\Repositories\SupplierRepositoryInterface;
use App\Modules\Operational\Master\Repositories\SupplierRepository;
use App\Modules\Operational\Master\Repositories\UnitRepositoryInterface;
use App\Modules\Operational\Master\Repositories\UnitRepository;
use App\Modules\Operational\Master\Repositories\TaxRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TaxRepository;
use App\Modules\Operational\Master\Repositories\LocationRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LocationRepository;
use App\Modules\Operational\Master\Repositories\PersonRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PersonRepository;
use App\Modules\Operational\Master\Repositories\PaymentTermRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PaymentTermRepository;
use App\Modules\Operational\Master\Repositories\PriceLevelRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PriceLevelRepository;
use App\Modules\Operational\Master\Repositories\ItemPriceRepositoryInterface;
use App\Modules\Operational\Master\Repositories\ItemPriceRepository;
use App\Modules\Operational\Master\Repositories\ItemConversionRepositoryInterface;
use App\Modules\Operational\Master\Repositories\ItemConversionRepository;
use App\Modules\Operational\Master\Repositories\TreatmentRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TreatmentRepository;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepository;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepository;
use App\Modules\Operational\Sales\Repositories\SalesRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\SalesRepository;
use App\Modules\Operational\Sales\Repositories\ShipRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\ShipRepository;
use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepositoryInterface;
use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepository;
use App\Modules\Operational\Procurement\Repositories\PurchaseRepositoryInterface;
use App\Modules\Operational\Procurement\Repositories\PurchaseRepository;
use App\Modules\Operational\Procurement\Repositories\PurchaseReturnRepositoryInterface;
use App\Modules\Operational\Procurement\Repositories\PurchaseReturnRepository;
use App\Modules\Operational\Inventory\Repositories\StockTransferRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockTransferRepository;
use App\Modules\Operational\Inventory\Repositories\StockBalanceRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockBalanceRepository;
use App\Modules\Operational\Inventory\Repositories\StockAdjustmentRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockAdjustmentRepository;
use App\Modules\Operational\Manufacturing\Repositories\BomRepositoryInterface;
use App\Modules\Operational\Manufacturing\Repositories\BomRepository;
use App\Modules\Operational\Manufacturing\Repositories\AssemblyRepositoryInterface;
use App\Modules\Operational\Manufacturing\Repositories\AssemblyRepository;

class OperationalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Master
        $this->app->bind(LookupRepositoryInterface::class, LookupRepository::class);
        $this->app->bind(LookupSetRepositoryInterface::class, LookupSetRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
        $this->app->bind(TaxRepositoryInterface::class, TaxRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);
        $this->app->bind(PaymentTermRepositoryInterface::class, PaymentTermRepository::class);
        $this->app->bind(PriceLevelRepositoryInterface::class, PriceLevelRepository::class);
        $this->app->bind(ItemPriceRepositoryInterface::class, ItemPriceRepository::class);
        $this->app->bind(ItemConversionRepositoryInterface::class, ItemConversionRepository::class);
        $this->app->bind(TreatmentRepositoryInterface::class, TreatmentRepository::class);
        $this->app->bind(TreatmentPackageRepositoryInterface::class, TreatmentPackageRepository::class);
        // Sales
        $this->app->bind(SalesTransactionRepositoryInterface::class, SalesTransactionRepository::class);
        $this->app->bind(SalesRepositoryInterface::class, SalesRepository::class);
        $this->app->bind(ShipRepositoryInterface::class, ShipRepository::class);
        // Procurement
        $this->app->bind(PurchaseOrderRepositoryInterface::class, PurchaseOrderRepository::class);
        $this->app->bind(PurchaseRepositoryInterface::class, PurchaseRepository::class);
        $this->app->bind(PurchaseReturnRepositoryInterface::class, PurchaseReturnRepository::class);
        // Inventory
        $this->app->bind(StockTransferRepositoryInterface::class, StockTransferRepository::class);
        $this->app->bind(StockBalanceRepositoryInterface::class, StockBalanceRepository::class);
        $this->app->bind(StockAdjustmentRepositoryInterface::class, StockAdjustmentRepository::class);
        // Manufacturing
        $this->app->bind(BomRepositoryInterface::class, BomRepository::class);
        $this->app->bind(AssemblyRepositoryInterface::class, AssemblyRepository::class);
    }

    public function boot(): void {}
}
