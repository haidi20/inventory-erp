<?php

namespace Tests\Unit\Services\Operational\Sales;

use App\Modules\Operational\Sales\Services\TransactionService;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{

    public function test_purchase_list(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'purchaseList');
        $this->assertTrue($method->isPublic());
    }

    public function test_payable_list(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'payableList');
        $this->assertTrue($method->isPublic());
    }

    public function test_examination_list(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'examinationList');
        $this->assertTrue($method->isPublic());
    }

    public function test_examination_form(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'examinationForm');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_examination(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'storeExamination');
        $this->assertTrue($method->isPublic());
    }

    public function test_sales_list(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'salesList');
        $this->assertTrue($method->isPublic());
    }

    public function test_sales_form(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'salesForm');
        $this->assertTrue($method->isPublic());
    }

    public function test_store_sale(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'storeSale');
        $this->assertTrue($method->isPublic());
    }

    public function test_sale_detail(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'saleDetail');
        $this->assertTrue($method->isPublic());
    }

    public function test_receivable_list(): void
    {
        $method = new \ReflectionMethod(TransactionService::class, 'receivableList');
        $this->assertTrue($method->isPublic());
    }
}