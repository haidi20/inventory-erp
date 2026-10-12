<?php

namespace Tests\Unit\Services\Clinical\Classification;

use App\Modules\Clinical\Classification\Services\Icd10CodeService;
use Tests\TestCase;

class Icd10CodeServiceTest extends TestCase
{

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(Icd10CodeService::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(Icd10CodeService::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(Icd10CodeService::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(Icd10CodeService::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}