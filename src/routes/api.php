<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Loader Per Modul
|--------------------------------------------------------------------------
| File ini hanya loader. Semua route didefinisikan di masing-masing modul.
| Semua route di-prefix dengan /api/v1 secara otomatis oleh bootstrap/app.php
|
| === CORE ===
| app/Modules/Core/Identity/routes/api.php
|   - POST   auth/login
|   - POST   auth/logout, auth/refresh, GET auth/me
|   - GET/POST/PUT/DELETE settings/users (export, import, template)
|
| app/Modules/Core/Authorization/routes/api.php
|   - GET/POST/PUT/DELETE settings/roles (export, import, template)
|
| app/Modules/Core/Platform/routes/api.php
|   - GET/POST settings/backups (index, history-datatable, download, export, restore)
|
| app/Modules/Core/Organization/routes/api.php
|   - CRUD master/branches
|   - CRUD master/companies
|
| === CLINICAL ===
| app/Modules/Clinical/Registration/routes/api.php
|   - CRUD patients
|   - CRUD registrations
|
| app/Modules/Clinical/MedicalRecord/routes/api.php
|   - CRUD encounters
|   - GET/POST operational/complaints
|
| app/Modules/Clinical/Integration/routes/api.php
|   - GET  integration/satusehat/practitioner/{nik}
|   - GET  integration/satusehat/patient/{nik}
|   - GET  integration/satusehat/organization/{id}
|   - POST integration/satusehat/kyc, coverage-eligibility, encounter,
|          condition, observation, medication-request
|
| === OPERATIONAL ===
| app/Modules/Operational/Master/routes/api.php
|   - CRUD suppliers, products, employees
|   - CRUD masters/units (export, import, template)
|   - CRUD masters/item-conversions, taxes, treatments, treatment-packages
|   - CRUD masters/payment-terms, price-levels
|   - CRUD lov/types, lov/types/{uuid}/values, lov/sets
|
| app/Modules/Operational/Sales/routes/api.php
|   - GET/POST/GET sales (index, store, show)
|   - CRUD services, service-packages
|   - CRUD encounters
|
| app/Modules/Operational/Procurement/routes/api.php
|   - GET/POST/GET purchases (index, store, show)
|   - GET/POST/GET/DELETE operational/purchase-orders
|
| app/Modules/Operational/Manufacturing/routes/api.php
|   - GET/POST/GET/DELETE operational/boms
|   - GET/POST/GET/DELETE operational/assemblies
|
| === FINANCE ===
| app/Modules/Finance/Journal/routes/api.php
|   - GET/POST/GET accounts (index, store, show, update)
|   - GET/POST/GET journals (index, store, show) + export excel/pdf
|   - GET accounting/cash-flow (index, compare, export excel/pdf)
|
| app/Modules/Finance/Ledger/routes/api.php
|   - GET general-ledger (index, export excel/pdf)
|
| app/Modules/Finance/Reports/routes/api.php
|   - GET reports/income-statement (+ export excel/pdf)
|   - GET reports/balance-sheet (+ export excel/pdf)
|   - GET reports/equity, adjusted-trial-balance, ar-aging, ap-aging
|   - GET reports/inventory, fixed-assets, tax
|   - GET reports/trial-balance (+ export excel/pdf)
|
*/

Route::prefix('v1')->group(function () {
    // Core
    require __DIR__ . '/../modules/Core/Identity/routes/api.php';
    require __DIR__ . '/../modules/Core/Authorization/routes/api.php';
    require __DIR__ . '/../modules/Core/Platform/routes/api.php';
    require __DIR__ . '/../modules/Core/Organization/routes/api.php';

    // Clinical
    require __DIR__ . '/../modules/Clinical/Registration/routes/api.php';
    require __DIR__ . '/../modules/Clinical/MedicalRecord/routes/api.php';
    require __DIR__ . '/../modules/Clinical/Integration/routes/api.php';
    require __DIR__ . '/../modules/Clinical/Pharmacy/routes/api.php';
    require __DIR__ . '/../modules/Clinical/Ancillary/routes/api.php';
    require __DIR__ . '/../modules/Clinical/Classification/routes/api.php';

    // Operational
    require __DIR__ . '/../modules/Operational/Master/routes/api.php';
    require __DIR__ . '/../modules/Operational/Sales/routes/api.php';
    require __DIR__ . '/../modules/Operational/Procurement/routes/api.php';
    require __DIR__ . '/../modules/Operational/Manufacturing/routes/api.php';
    require __DIR__ . '/../modules/Operational/Inventory/routes/api.php';

    // Finance
    require __DIR__ . '/../modules/Finance/Journal/routes/api.php';
    require __DIR__ . '/../modules/Finance/Ledger/routes/api.php';
    require __DIR__ . '/../modules/Analytics/Reports/routes/api.php';

    // Analytics
    require __DIR__ . '/../modules/Analytics/Dashboard/routes/api.php';
});
