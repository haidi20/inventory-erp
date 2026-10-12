<?php

namespace Tests\Feature\Controllers\Operational\Master;

use App\Modules\Operational\Master\Http\Controllers\WebLookUpController;
use Tests\TestCase;

class WebLookUpControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_products(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'products');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_prices(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'productPrices');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_groups(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'productGroups');
        $this->assertTrue($method->isPublic());
    }

    public function test_product_categories(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'productCategories');
        $this->assertTrue($method->isPublic());
    }

    public function test_brands(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'brands');
        $this->assertTrue($method->isPublic());
    }

    public function test_business_units(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'businessUnits');
        $this->assertTrue($method->isPublic());
    }

    public function test_account_groups(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'accountGroups');
        $this->assertTrue($method->isPublic());
    }

    public function test_payment_methods(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'paymentMethods');
        $this->assertTrue($method->isPublic());
    }

    public function test_advance_classifications(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'advanceClassifications');
        $this->assertTrue($method->isPublic());
    }

    public function test_fixed_asset_categories(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'fixedAssetCategories');
        $this->assertTrue($method->isPublic());
    }

    public function test_pharmacy_settings(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'pharmacySettings');
        $this->assertTrue($method->isPublic());
    }

    public function test_values(): void
    {
        $method = new \ReflectionMethod(WebLookUpController::class, 'values');
        $this->assertTrue($method->isPublic());
    }
}