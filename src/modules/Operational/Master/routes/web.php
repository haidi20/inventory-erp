<?php

use App\Modules\Operational\Master\Http\Controllers\WebProductController;
use App\Modules\Operational\Master\Http\Controllers\WebSupplierController;
use App\Modules\Operational\Master\Http\Controllers\WebEmployeeController;
use App\Modules\Operational\Master\Http\Controllers\WebTreatmentController;
use App\Modules\Operational\Master\Http\Controllers\WebTreatmentPackageController;
use App\Modules\Operational\Master\Http\Controllers\CustomerSupplierController;
use App\Modules\Operational\Master\Http\Controllers\PriceListController;
use App\Modules\Operational\Master\Http\Controllers\WebLookUpController;
use App\Modules\Operational\Master\Http\Controllers\LocationController;
use App\Modules\Operational\Master\Http\Controllers\UnitController;
use App\Modules\Operational\Master\Http\Controllers\WebTaxController;
use App\Modules\Operational\Master\Http\Controllers\WebPaymentTermController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::prefix('master')->name('master.')->group(function () {

        // Redirect index ke products
        Route::get('/', fn() => redirect('/master/products'))->name('index');

        // Produk
        Route::get('/products', [WebProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [WebProductController::class, 'create'])->name('products.create');
        Route::get('/products/{uuid}/edit', [WebProductController::class, 'edit'])->name('products.edit');

        // Supplier
        Route::get('/supplier', [WebSupplierController::class, 'index'])->name('supplier.index');

        // Karyawan
        Route::get('/employees', [WebEmployeeController::class, 'index'])->name('employees.index');

        // Tindakan & Paket
        Route::get('/treatments', [WebTreatmentController::class, 'index'])->name('treatments.index');
        Route::get('/treatment-packages', [WebTreatmentPackageController::class, 'index'])->name('treatment-packages.index');
        Route::get('/services', [WebTreatmentController::class, 'index'])->name('services.index');
        Route::get('/packages', [WebTreatmentPackageController::class, 'index'])->name('packages.index');

        // Pasien (Clinical domain - Inertia redirect)
        Route::get('/patients', fn() => redirect('/pendaftaran/pasien'))->name('patients.index');

        // Gudang, Satuan, Pajak, Termin, Lookup
        Route::get('/warehouses', [LocationController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [LocationController::class, 'store'])->name('warehouses.store');
        Route::put('/warehouses/{uuid}', [LocationController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{uuid}', [LocationController::class, 'destroy'])->name('warehouses.destroy');

        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::get('/taxes', [WebTaxController::class, 'index'])->name('taxes.index');
        Route::get('/payment-terms', [WebPaymentTermController::class, 'index'])->name('payment-terms.index');

        // Lookup & turunannya
        Route::get('/lookup', [WebLookUpController::class, 'index'])->name('lookup.index');
        Route::get('/brands', [WebLookUpController::class, 'brands'])->name('brands.index');
        Route::get('/product-groups', [WebLookUpController::class, 'productGroups'])->name('product-groups.index');
        Route::get('/product-categories', [WebLookUpController::class, 'productCategories'])->name('product-categories.index');
        Route::get('/product-prices', [WebLookUpController::class, 'productPrices'])->name('product-prices.index');
        Route::get('/account-groups', [WebLookUpController::class, 'accountGroups'])->name('account-groups.index');
        Route::get('/business-units', [WebLookUpController::class, 'businessUnits'])->name('business-units.index');
        Route::get('/fixed-asset-categories', [WebLookUpController::class, 'fixedAssetCategories'])->name('fixed-asset-categories.index');
        Route::get('/payment-methods', [WebLookUpController::class, 'paymentMethods'])->name('payment-methods.index');
        Route::get('/advance-classifications', [WebLookUpController::class, 'advanceClassifications'])->name('advance-classifications.index');
    });

    // Customer & Supplier
    Route::prefix('customer-supplier')->name('customer-supplier.')->group(function () {
        Route::get('/customers', [CustomerSupplierController::class, 'customers'])->name('customers');
        Route::get('/suppliers', [CustomerSupplierController::class, 'suppliers'])->name('suppliers');
    });

    Route::get('/price-list', [PriceListController::class, 'index'])->name('price-list.index');

    // Canonical lookup routes
    Route::prefix('masters')->name('masters.')->group(function () {
        Route::get('/lookups', [WebLookUpController::class, 'index'])->name('lookups');
        Route::get('/lookups/sets/{uuid}/values', [WebLookUpController::class, 'values'])->name('lookups.values');
    });
});
