<?php

namespace Tests\Feature\Repositories\Operational\Master;

use App\Modules\Operational\Master\Repositories\MasterDataRepository;
use Tests\TestCase;

class MasterDataRepositoryTest extends TestCase
{

    public function test_medical_record_no_exists(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'medicalRecordNoExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_code_exists(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'productCodeExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_service_code_exists(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'serviceCodeExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_package_code_exists(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'packageCodeExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_account_code_exists(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'accountCodeExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_patients_page_data(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'getPatientsPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_patient(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createPatient');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_suppliers_page_data(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'getSuppliersPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_supplier(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createSupplier');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_supplier(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'updateSupplier');
        $this->assertTrue($method->isPublic());
    }

    public function test_supplier_has_purchases(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'supplierHasPurchases');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_supplier(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'deleteSupplier');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_products_page_data(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'getProductsPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_product(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createProduct');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_product(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'updateProduct');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_has_transactions(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'productHasTransactions');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_product(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'deleteProduct');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_services_page_data(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'getServicesPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_service(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createService');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_service(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'updateService');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_service(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'deleteService');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_packages_page_data(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'getPackagesPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_package(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createPackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_package(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'updatePackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_package(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'deletePackage');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_employees_page_data(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'getEmployeesPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_employee(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createEmployee');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_employee(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'updateEmployee');
        $this->assertTrue($method->isPublic());
    }

    public function test_employee_has_user(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'employeeHasUser');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_employee(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'deleteEmployee');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_account(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'createAccount');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_account(): void
    {
        $method = new \ReflectionMethod(MasterDataRepository::class, 'updateAccount');
        $this->assertTrue($method->isPublic());
    }
}