<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * TDD Relationship Test
 * Generated automatically from Database Migration definitions.
 */
class DatabaseModelRelationshipsTest extends TestCase
{
    public function test_users_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for User
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Core\\Identity\\Models\\User';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Core\\Identity\\Models\\User';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\User';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\User';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'User' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        // Multi-org design: relasi User<->Branch adalah BelongsToMany via pivot user_branches.
        // Kolom legacy users.branch_uuid sudah di-drop (lihat a22e93043), sehingga
        // relasi BelongsTo branch() legacy tidak lagi valid.
        $this->assertTrue(method_exists($model, 'branches'), 'User is missing belongsToMany method branches()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $model->branches(), 'branches() should return BelongsToMany relation');
    }
    public function test_sessions_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Session
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\Session';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Session';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Session';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Session';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Session' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'user'), 'Session is missing belongsTo method user()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->user(), 'user() should return BelongsTo relation');
    }
    public function test_audit_logs_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for AuditLog
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\AuditLog';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\AuditLog';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\AuditLog';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\AuditLog';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'AuditLog' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'AuditLog is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'user'), 'AuditLog is missing belongsTo method user()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->user(), 'user() should return BelongsTo relation');
    }
    public function test_employees_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Employee
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\Employee';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\Employee';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Employee';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Employee';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Employee' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Employee is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'user'), 'Employee is missing belongsTo method user()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->user(), 'user() should return BelongsTo relation');
    }
    public function test_patients_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Patient
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Clinical\\Registration\\Models\\Patient';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Clinical\\Registration\\Models\\Patient';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Patient';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Patient';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Patient' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Patient is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_suppliers_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Supplier
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\Supplier';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\Supplier';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Supplier';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Supplier';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Supplier' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Supplier is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_products_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Product
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\Product';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\Product';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Product';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Product';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Product' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Product is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_services_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Service
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\Service';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\Service';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Service';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Service';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Service' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Service is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_service_packages_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for ServicePackage
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\ServicePackage';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\ServicePackage';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\ServicePackage';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\ServicePackage';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'ServicePackage' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'ServicePackage is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_service_package_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for ServicePackageItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\ServicePackageItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\ServicePackageItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\ServicePackageItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\ServicePackageItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'ServicePackageItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'ServicePackageItem is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'servicePackage'), 'ServicePackageItem is missing belongsTo method servicePackage()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->servicePackage(), 'servicePackage() should return BelongsTo relation');
    }
    public function test_registrations_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Registration
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Clinical\\Registration\\Models\\Registration';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Registration';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Clinical\\Registration\\Models\\Registration';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Registration';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Registration' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Registration is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'patient'), 'Registration is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'doctor'), 'Registration is missing belongsTo method doctor()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->doctor(), 'doctor() should return BelongsTo relation');
    }
    public function test_encounters_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Encounter
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Clinical\\MedicalRecord\\Models\\Encounter';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Encounter';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Clinical\\MedicalRecord\\Models\\Encounter';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Encounter';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Encounter' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Encounter is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'registration'), 'Encounter is missing belongsTo method registration()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->registration(), 'registration() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'patient'), 'Encounter is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'doctor'), 'Encounter is missing belongsTo method doctor()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->doctor(), 'doctor() should return BelongsTo relation');
    }
    public function test_encounter_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for EncounterItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Clinical\\MedicalRecord\\Models\\EncounterItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\EncounterItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Clinical\\MedicalRecord\\Models\\EncounterItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\EncounterItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'EncounterItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'EncounterItem is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'encounter'), 'EncounterItem is missing belongsTo method encounter()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->encounter(), 'encounter() should return BelongsTo relation');
    }
    public function test_sales_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Sale
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Operational\\Sales\\Models\\Sale';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Sale';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Sales\\Models\\Sale';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Sale';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Sale' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Sale is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'registration'), 'Sale is missing belongsTo method registration()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->registration(), 'registration() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'patient'), 'Sale is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'cashier'), 'Sale is missing belongsTo method cashier()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->cashier(), 'cashier() should return BelongsTo relation');
    }
    public function test_sale_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for SaleItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Operational\\Sales\\Models\\SaleItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\SaleItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Sales\\Models\\SaleItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\SaleItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'SaleItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'SaleItem is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'sale'), 'SaleItem is missing belongsTo method sale()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->sale(), 'sale() should return BelongsTo relation');
    }
    public function test_purchases_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Purchase
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Operational\\Procurement\\Models\\Purchase';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Purchase';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Procurement\\Models\\Purchase';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Purchase';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Purchase' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Purchase is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'supplier'), 'Purchase is missing belongsTo method supplier()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->supplier(), 'supplier() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'receivedBy'), 'Purchase is missing belongsTo method receivedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->receivedBy(), 'receivedBy() should return BelongsTo relation');
    }
    public function test_purchase_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for PurchaseItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Operational\\Procurement\\Models\\PurchaseItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\PurchaseItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Procurement\\Models\\PurchaseItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\PurchaseItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'PurchaseItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'PurchaseItem is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'purchase'), 'PurchaseItem is missing belongsTo method purchase()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->purchase(), 'purchase() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'product'), 'PurchaseItem is missing belongsTo method product()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->product(), 'product() should return BelongsTo relation');
    }
    public function test_receivables_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Receivable
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\Receivable';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Receivable';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Receivable';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Receivable';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Receivable' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Receivable is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_payables_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Payable
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\Payable';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Payable';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Payable';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Payable';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Payable' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Payable is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_stock_movements_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for StockMovement
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Operational\\Inventory\\Models\\StockMovement';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\StockMovement';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Operational\\Inventory\\Models\\StockMovement';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\StockMovement';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'StockMovement' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'StockMovement is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'product'), 'StockMovement is missing belongsTo method product()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->product(), 'product() should return BelongsTo relation');
    }
    public function test_accounts_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Account
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\Account';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Account';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Account';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\Account';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Account' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'parent'), 'Account is missing belongsTo method parent()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->parent(), 'parent() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'Account is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'Account is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'Account is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_account_mappings_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for AccountMapping
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\AccountMapping';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\AccountMapping';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\AccountMapping';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\AccountMapping';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'AccountMapping' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'account'), 'AccountMapping is missing belongsTo method account()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->account(), 'account() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'AccountMapping is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'AccountMapping is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'AccountMapping is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_tax_settings_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for TaxSetting
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Operational\\Master\\Models\\TaxSetting';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\TaxSetting';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\TaxSetting';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\TaxSetting';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'TaxSetting' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'account'), 'TaxSetting is missing belongsTo method account()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->account(), 'account() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'TaxSetting is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'TaxSetting is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'TaxSetting is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_closing_periods_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for ClosingPeriod
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Ledger\\Models\\ClosingPeriod';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\ClosingPeriod';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\ClosingPeriod';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Finance\\Ledger\\Models\\ClosingPeriod';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'ClosingPeriod' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'ClosingPeriod is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'ClosingPeriod is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'ClosingPeriod is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'ClosingPeriod is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_journals_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Journal
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\Journal';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Journal';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Journal';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\Journal';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Journal' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'Journal is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'closingPeriod'), 'Journal is missing belongsTo method closingPeriod()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->closingPeriod(), 'closingPeriod() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'Journal is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'Journal is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'Journal is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_journal_lines_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for JournalLine
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\JournalLine';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\JournalLine';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\JournalLine';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\JournalLine';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'JournalLine' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'JournalLine is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'journal'), 'JournalLine is missing belongsTo method journal()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->journal(), 'journal() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'account'), 'JournalLine is missing belongsTo method account()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->account(), 'account() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'JournalLine is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'JournalLine is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'JournalLine is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_account_balances_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for AccountBalance
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Ledger\\Models\\AccountBalance';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\AccountBalance';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\AccountBalance';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\AccountBalance';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'AccountBalance' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'AccountBalance is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'account'), 'AccountBalance is missing belongsTo method account()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->account(), 'account() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'createdBy'), 'AccountBalance is missing belongsTo method createdBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->createdBy(), 'createdBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'updatedBy'), 'AccountBalance is missing belongsTo method updatedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->updatedBy(), 'updatedBy() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'deletedBy'), 'AccountBalance is missing belongsTo method deletedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->deletedBy(), 'deletedBy() should return BelongsTo relation');
    }
    public function test_recurring_journals_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for RecurringJournal
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\RecurringJournal';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\RecurringJournal';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\RecurringJournal';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\RecurringJournal';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'RecurringJournal' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'RecurringJournal is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
    public function test_journal_attachments_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for JournalAttachment
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Finance\\Journal\\Models\\JournalAttachment';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\JournalAttachment';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\JournalAttachment';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\JournalAttachment';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'JournalAttachment' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'journal'), 'JournalAttachment is missing belongsTo method journal()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->journal(), 'journal() should return BelongsTo relation');
    }
    public function test_encounter_vitals_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for EncounterVital
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\EncounterVital';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\EncounterVital';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\EncounterVital';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\EncounterVital';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'EncounterVital' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'encounter'), 'EncounterVital is missing belongsTo method encounter()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->encounter(), 'encounter() should return BelongsTo relation');
    }
    public function test_encounter_physicals_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for EncounterPhysical
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\EncounterPhysical';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\EncounterPhysical';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\EncounterPhysical';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\EncounterPhysical';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'EncounterPhysical' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'encounter'), 'EncounterPhysical is missing belongsTo method encounter()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->encounter(), 'encounter() should return BelongsTo relation');
    }
    public function test_encounter_diagnoses_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for EncounterDiagnose
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\EncounterDiagnose';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\EncounterDiagnose';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\EncounterDiagnose';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\EncounterDiagnose';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'EncounterDiagnose' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'encounter'), 'EncounterDiagnose is missing belongsTo method encounter()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->encounter(), 'encounter() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'icd10Code'), 'EncounterDiagnose is missing belongsTo method icd10Code()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->icd10Code(), 'icd10Code() should return BelongsTo relation');
    }
    public function test_prescriptions_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Prescription
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\Prescription';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Prescription';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Prescription';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Prescription';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Prescription' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'encounter'), 'Prescription is missing belongsTo method encounter()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->encounter(), 'encounter() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'patient'), 'Prescription is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'doctor'), 'Prescription is missing belongsTo method doctor()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->doctor(), 'doctor() should return BelongsTo relation');
    }
    public function test_dispensings_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for Dispensing
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Modules\\Clinical\\Pharmacy\\Models\\Dispensing';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\Dispensing';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\Dispensing';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\Dispensing';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'Dispensing' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'patient'), 'Dispensing is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'dispensedBy'), 'Dispensing is missing belongsTo method dispensedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->dispensedBy(), 'dispensedBy() should return BelongsTo relation');
    }
    public function test_dispensing_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for DispensingItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\DispensingItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\DispensingItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\DispensingItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\DispensingItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'DispensingItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'dispensing'), 'DispensingItem is missing belongsTo method dispensing()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->dispensing(), 'dispensing() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'product'), 'DispensingItem is missing belongsTo method product()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->product(), 'product() should return BelongsTo relation');
    }
    public function test_purchase_returns_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for PurchaseReturn
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\PurchaseReturn';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\PurchaseReturn';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\PurchaseReturn';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\PurchaseReturn';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'PurchaseReturn' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'purchase'), 'PurchaseReturn is missing belongsTo method purchase()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->purchase(), 'purchase() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'supplier'), 'PurchaseReturn is missing belongsTo method supplier()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->supplier(), 'supplier() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'returnedBy'), 'PurchaseReturn is missing belongsTo method returnedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->returnedBy(), 'returnedBy() should return BelongsTo relation');
    }
    public function test_purchase_return_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for PurchaseReturnItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\PurchaseReturnItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\PurchaseReturnItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\PurchaseReturnItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\PurchaseReturnItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'PurchaseReturnItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'return'), 'PurchaseReturnItem is missing belongsTo method return()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->return(), 'return() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'product'), 'PurchaseReturnItem is missing belongsTo method product()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->product(), 'product() should return BelongsTo relation');
    }
    public function test_lab_orders_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for LabOrder
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\LabOrder';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\LabOrder';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\LabOrder';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\LabOrder';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'LabOrder' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'patient'), 'LabOrder is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'doctor'), 'LabOrder is missing belongsTo method doctor()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->doctor(), 'doctor() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'resultedBy'), 'LabOrder is missing belongsTo method resultedBy()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->resultedBy(), 'resultedBy() should return BelongsTo relation');
    }
    public function test_lab_order_items_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for LabOrderItem
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\LabOrderItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\LabOrderItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\LabOrderItem';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\LabOrderItem';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'LabOrderItem' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'labOrder'), 'LabOrderItem is missing belongsTo method labOrder()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->labOrder(), 'labOrder() should return BelongsTo relation');
    }
    public function test_radiology_orders_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for RadiologyOrder
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\RadiologyOrder';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\RadiologyOrder';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\RadiologyOrder';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\RadiologyOrder';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'RadiologyOrder' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'patient'), 'RadiologyOrder is missing belongsTo method patient()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->patient(), 'patient() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'doctor'), 'RadiologyOrder is missing belongsTo method doctor()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->doctor(), 'doctor() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'radiologist'), 'RadiologyOrder is missing belongsTo method radiologist()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->radiologist(), 'radiologist() should return BelongsTo relation');
    }
    public function test_general_ledgers_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for GeneralLedger
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\GeneralLedger';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\GeneralLedger';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\GeneralLedger';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\GeneralLedger';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'GeneralLedger' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'GeneralLedger is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'account'), 'GeneralLedger is missing belongsTo method account()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->account(), 'account() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'journal'), 'GeneralLedger is missing belongsTo method journal()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->journal(), 'journal() should return BelongsTo relation');
        $this->assertTrue(method_exists($model, 'journalLine'), 'GeneralLedger is missing belongsTo method journalLine()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->journalLine(), 'journalLine() should return BelongsTo relation');
    }
    public function test_transaction_sequences_model_has_expected_relationships()
    {
        // Mock or instantiate mapping for TransactionSequence
        // Ensure the class exists before asserting
        $modelClass = '\\App\\Models\\TransactionSequence';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Master\\TransactionSequence';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Operational\\TransactionSequence';
        if (!class_exists($modelClass)) $modelClass = '\\App\\Models\\Accounting\\TransactionSequence';
        if (!class_exists($modelClass)) $this->markTestSkipped('Model ' . 'TransactionSequence' . ' does not exist yet.');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $this->assertTrue(method_exists($model, 'branch'), 'TransactionSequence is missing belongsTo method branch()');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $model->branch(), 'branch() should return BelongsTo relation');
    }
}

