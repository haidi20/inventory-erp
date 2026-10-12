<?php

namespace Tests\Feature\Controllers\Clinical\Pharmacy;

use App\Modules\Clinical\Pharmacy\Http\Controllers\FarmacyController;
use Tests\TestCase;

class FarmacyControllerTest extends TestCase
{

    public function test_dashboard(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'dashboard');
        $this->assertTrue($method->isPublic());
    }

    public function test_recipe(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'recipe');
        $this->assertTrue($method->isPublic());
    }

    public function test_transaction(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'transaction');
        $this->assertTrue($method->isPublic());
    }

    public function test_accounting(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'accounting');
        $this->assertTrue($method->isPublic());
    }

    public function test_inventory(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'inventory');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_adjustment(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockAdjustment');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_adjustment_create(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockAdjustmentCreate');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_adjustment_edit(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockAdjustmentEdit');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_opname(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockOpname');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_opname_create(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockOpnameCreate');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_opname_edit(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockOpnameEdit');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_mutation(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockMutation');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_mutation_create(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockMutationCreate');
        $this->assertTrue($method->isPublic());
    }

    public function test_stock_mutation_edit(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'stockMutationEdit');
        $this->assertTrue($method->isPublic());
    }

    public function test_opening_balance(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'openingBalance');
        $this->assertTrue($method->isPublic());
    }

    public function test_opening_balance_create(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'openingBalanceCreate');
        $this->assertTrue($method->isPublic());
    }

    public function test_opening_balance_edit(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'openingBalanceEdit');
        $this->assertTrue($method->isPublic());
    }

    public function test_service_deposit(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'serviceDeposit');
        $this->assertTrue($method->isPublic());
    }

    public function test_residue_return(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'residueReturn');
        $this->assertTrue($method->isPublic());
    }

    public function test_management(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'management');
        $this->assertTrue($method->isPublic());
    }

    public function test_report(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'report');
        $this->assertTrue($method->isPublic());
    }

    public function test_index(): void
    {
        $method = new \ReflectionMethod(FarmacyController::class, 'index');
        $this->assertTrue($method->isPublic());
    }
}