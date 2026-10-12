<?php

namespace Tests\Feature\Controllers\Operational\Sales;

use App\Modules\Operational\Sales\Http\Controllers\TransaksiController;
use Tests\TestCase;

class TransaksiControllerTest extends TestCase
{

    public function test_show_sales_list(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showSalesList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_purchase_list(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showPurchaseList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_examination_list(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showExaminationList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_receivable_list(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showReceivableList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_payable_list(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showPayableList');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_sales_create_form(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showSalesCreateForm');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_sale_detail(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showSaleDetail');
        $this->assertTrue($method->isPublic());
    }

    public function test_show_examination_form(): void
    {
        $method = new \ReflectionMethod(TransaksiController::class, 'showExaminationForm');
        $this->assertTrue($method->isPublic());
    }
}