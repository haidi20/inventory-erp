<?php

use App\Modules\Operational\Inventory\Http\Controllers\StockController;
use App\Modules\Operational\Inventory\Http\Controllers\BarcodePrintController;
use App\Modules\Operational\Master\Http\Controllers\WebLookUpController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/inventory', [App\Modules\Operational\Inventory\Http\Controllers\InventoryIndexController::class, 'index'])->name('inventory.index');

    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/value-list', [StockController::class, 'showValueList'])->name('value-list');
        Route::get('/items-and-services', [StockController::class, 'showItemsAndServices'])->name('items-and-services');
        Route::get('/bom-formula', [StockController::class, 'showFormulaBom'])->name('bom-formula');
        Route::get('/units', [StockController::class, 'showUnitList'])->name('units');
        Route::get('/opening-balance', [StockController::class, 'showOpeningBalance'])->name('opening-balance');
        Route::get('/item-conversions', [StockController::class, 'showItemConversion'])->name('item-conversions');
        Route::get('/warehouses', [StockController::class, 'showWarehouseList'])->name('warehouses');
    });

    Route::middleware('permission:master.lookup.view')->prefix('inventory/master')->name('inventory.master.')->group(function () {
        Route::get('/daftar-nilai', [WebLookUpController::class, 'index'])->name('daftar-nilai.index');
        Route::get('/daftar-nilai/{uuid}/values', [WebLookUpController::class, 'values'])->name('daftar-nilai.values');
    });

    Route::get('/print-barcode', [BarcodePrintController::class, 'index'])->name('print-barcode.index');
});
