<?php

use App\Modules\Operational\Master\Http\Controllers\ProductController;
use App\Modules\Operational\Master\Http\Controllers\SupplierController;
use App\Modules\Operational\Master\Http\Controllers\EmployeeController;
use App\Modules\Operational\Master\Http\Controllers\ApiUnitController;
use App\Modules\Operational\Master\Http\Controllers\TaxController;
use App\Modules\Operational\Master\Http\Controllers\TreatmentController;
use App\Modules\Operational\Master\Http\Controllers\TreatmentPackageController;
use App\Modules\Operational\Master\Http\Controllers\ItemConversionController;
use App\Modules\Operational\Master\Http\Controllers\LookUpController;
use App\Modules\Operational\Master\Http\Controllers\PaymentTermController;
use App\Modules\Operational\Master\Http\Controllers\PriceLevelController;
use App\Modules\Operational\Master\Http\Controllers\LocationController;
use App\Modules\Operational\Master\Http\Controllers\ApiWarehouseController;
use Illuminate\Support\Facades\Route;

    Route::middleware(['auth:api', 'permission:master.manage'])->group(function () {
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('products/filter-options', [ProductController::class, 'filterOptions'])->name('products.filter-options');
    Route::get('products/options', [ProductController::class, 'options'])->name('products.options');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::apiResource('products', ProductController::class);
    Route::apiResource('employees', EmployeeController::class);

    Route::get('masters/units/export', [ApiUnitController::class, 'export']);
    Route::post('masters/units/import', [ApiUnitController::class, 'import']);
    Route::get('masters/units/import/template', [ApiUnitController::class, 'downloadTemplate']);
    Route::get('masters/units', [ApiUnitController::class, 'index']);
    Route::post('masters/units', [ApiUnitController::class, 'store']);
    Route::get('masters/units/{uuid}', [ApiUnitController::class, 'show']);
    Route::put('masters/units/{uuid}', [ApiUnitController::class, 'update']);
    Route::delete('masters/units/{uuid}', [ApiUnitController::class, 'destroy']);

    Route::get('masters/item-conversions', [ItemConversionController::class, 'index']);
    Route::post('masters/item-conversions', [ItemConversionController::class, 'store']);
    Route::delete('masters/item-conversions/{uuid}', [ItemConversionController::class, 'destroy']);

    Route::get('masters/taxes', [TaxController::class, 'index']);
    Route::post('masters/taxes', [TaxController::class, 'store']);
    Route::get('masters/taxes/{uuid}', [TaxController::class, 'show']);
    Route::put('masters/taxes/{uuid}', [TaxController::class, 'update']);
    Route::delete('masters/taxes/{uuid}', [TaxController::class, 'destroy']);

    Route::get('masters/treatments', [TreatmentController::class, 'index']);
    Route::post('masters/treatments', [TreatmentController::class, 'store']);
    Route::put('masters/treatments/{uuid}', [TreatmentController::class, 'update']);
    Route::delete('masters/treatments/{uuid}', [TreatmentController::class, 'destroy']);

    Route::get('masters/treatment-packages', [TreatmentPackageController::class, 'index']);
    Route::post('masters/treatment-packages', [TreatmentPackageController::class, 'store']);
    Route::put('masters/treatment-packages/{uuid}', [TreatmentPackageController::class, 'update']);
    Route::delete('masters/treatment-packages/{uuid}', [TreatmentPackageController::class, 'destroy']);

    Route::get('masters/payment-terms', [PaymentTermController::class, 'index']);
    Route::post('masters/payment-terms', [PaymentTermController::class, 'store']);
    Route::get('masters/payment-terms/{uuid}', [PaymentTermController::class, 'show']);
    Route::put('masters/payment-terms/{uuid}', [PaymentTermController::class, 'update']);
    Route::delete('masters/payment-terms/{uuid}', [PaymentTermController::class, 'destroy']);

    Route::get('masters/price-levels', [PriceLevelController::class, 'index']);
    Route::post('masters/price-levels', [PriceLevelController::class, 'store']);
    Route::put('masters/price-levels/{uuid}', [PriceLevelController::class, 'update']);
    Route::delete('masters/price-levels/{uuid}', [PriceLevelController::class, 'destroy']);
});

// LOV read routes — requires master.lookup.view (view-only roles dapat akses)
Route::middleware(['auth:api', 'permission:master.lookup.view'])->group(function () {
    Route::prefix('lov')->name('lov.')->group(function () {
        Route::get('/types', [LookUpController::class, 'typeIndex'])->name('types.index');
        Route::get('/types/{uuid}/values', [LookUpController::class, 'typeValues'])->name('values.index');
        Route::get('/sets', [LookUpController::class, 'setIndex'])->name('sets.index');
        // Static routes harus sebelum {uuid} agar tidak salah match
        Route::get('/sets/export', [LookUpController::class, 'setExport'])->name('sets.export');
        Route::get('/sets/import/template', [LookUpController::class, 'downloadSetTemplate'])->name('sets.import.template');
        Route::get('/sets/import/template-values', [LookUpController::class, 'downloadValueTemplate'])->name('sets.import.template-values');
        Route::get('/sets/{uuid}', [LookUpController::class, 'setShow'])->name('sets.show');
        Route::get('/sets/{uuid}/values', [LookUpController::class, 'setValues'])->name('sets.values.index');
    });
});

// LOV write routes — requires master.lookup.manage
Route::middleware(['auth:api', 'permission:master.lookup.manage'])->group(function () {
    Route::prefix('lov')->name('lov.write.')->group(function () {
        Route::post('/types', [LookUpController::class, 'typeStore'])->name('types.store');
        Route::post('/types/{uuid}/values', [LookUpController::class, 'typeValueStore'])->name('values.store');
        Route::delete('/types/{uuid}', [LookUpController::class, 'typeDestroy'])->name('types.destroy');
        Route::delete('/types/{uuid}/values/{valueUuid}', [LookUpController::class, 'typeValueDestroy'])->name('types.values.destroy');
        Route::delete('/values/{uuid}', [LookUpController::class, 'typeValueDestroy'])->name('values.destroy');
        Route::post('/sets/import', [LookUpController::class, 'setImport'])->name('sets.import');
        Route::post('/sets', [LookUpController::class, 'setStore'])->name('sets.store');
        Route::put('/sets/{uuid}', [LookUpController::class, 'setUpdate'])->name('sets.update');
        Route::delete('/sets/{uuid}', [LookUpController::class, 'setDestroy'])->name('sets.destroy');
        Route::post('/sets/{setId}/values', [LookUpController::class, 'setValueStore'])->name('sets.values.store');
        Route::put('/sets/{setId}/values/{uuid}', [LookUpController::class, 'setValueUpdate'])->name('sets.values.update');
        Route::delete('/sets/values/{uuid}', [LookUpController::class, 'setValueDestroy'])->name('sets.values.destroy');
        Route::get('/sets/{setId}/values/export', [LookUpController::class, 'setValueExport'])->name('sets.values.export');
        Route::post('/sets/{setId}/values/import', [LookUpController::class, 'setValueImport'])->name('sets.values.import');
    });
});


// Warehouse (Gudang) API routes
Route::middleware(['auth:api', 'permission:master.manage'])->group(function () {
    Route::get('master/warehouses', [ApiWarehouseController::class, 'index'])->name('api.warehouses.index');
    Route::post('master/warehouses', [ApiWarehouseController::class, 'store'])->name('api.warehouses.store');
    Route::get('master/warehouses/{uuid}', [ApiWarehouseController::class, 'show'])->name('api.warehouses.show');
    Route::put('master/warehouses/{uuid}', [ApiWarehouseController::class, 'update'])->name('api.warehouses.update');
    Route::delete('master/warehouses/{uuid}', [ApiWarehouseController::class, 'destroy'])->name('api.warehouses.destroy');
});
