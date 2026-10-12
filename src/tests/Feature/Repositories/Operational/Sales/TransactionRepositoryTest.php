<?php

namespace Tests\Feature\Repositories\Operational\Sales;

use App\Modules\Operational\Sales\Repositories\TransactionRepository;
use Tests\TestCase;

class TransactionRepositoryTest extends TestCase
{

    public function test_patient_exists(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'patientExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_employee_exists(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'employeeExists');
        $this->assertTrue($method->isPublic());
    }

    public function test_products_exist(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'productsExist');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_sale_page_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getSalePageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_sales_page_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getSalesPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_sales_form_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getSalesFormData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_purchase_page_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getPurchasePageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_payable_page_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getPayablePageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_sale(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'createSale');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_sale_detail(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getSaleDetail');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_receivable_page_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getReceivablePageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_examination_page_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getExaminationPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_examination_form_data(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'getExaminationFormData');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_examination(): void
    {
        $method = new \ReflectionMethod(TransactionRepository::class, 'storeExamination');
        $this->assertTrue($method->isPublic());
    }
}