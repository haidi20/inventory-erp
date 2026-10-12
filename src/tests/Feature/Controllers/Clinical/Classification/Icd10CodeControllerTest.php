<?php

namespace Tests\Feature\Controllers\Clinical\Classification;

use App\Modules\Clinical\Classification\Http\Controllers\Icd10CodeController;
use Tests\TestCase;

class Icd10CodeControllerTest extends TestCase
{

    public function test_index(): void
    {
        $method = new \ReflectionMethod(Icd10CodeController::class, 'index');
        $this->assertTrue($method->isPublic());
    }

    public function test_show(): void
    {
        $method = new \ReflectionMethod(Icd10CodeController::class, 'show');
        $this->assertTrue($method->isPublic());
    }

    public function test_store(): void
    {
        $method = new \ReflectionMethod(Icd10CodeController::class, 'store');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(Icd10CodeController::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_destroy(): void
    {
        $method = new \ReflectionMethod(Icd10CodeController::class, 'destroy');
        $this->assertTrue($method->isPublic());
    }
}