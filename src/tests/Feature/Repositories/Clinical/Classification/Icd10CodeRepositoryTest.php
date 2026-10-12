<?php

namespace Tests\Feature\Repositories\Clinical\Classification;

use App\Modules\Clinical\Classification\Repositories\Icd10CodeRepository;
use Tests\TestCase;

class Icd10CodeRepositoryTest extends TestCase
{

    public function test_get_page_data(): void
    {
        $method = new \ReflectionMethod(Icd10CodeRepository::class, 'getPageData');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_uuid(): void
    {
        $method = new \ReflectionMethod(Icd10CodeRepository::class, 'findByUuid');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_code(): void
    {
        $method = new \ReflectionMethod(Icd10CodeRepository::class, 'findByCode');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(Icd10CodeRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(Icd10CodeRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}