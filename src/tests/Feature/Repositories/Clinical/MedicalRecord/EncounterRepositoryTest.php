<?php

namespace Tests\Feature\Repositories\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Repositories\EncounterRepository;
use Tests\TestCase;

class EncounterRepositoryTest extends TestCase
{

    public function test_list(): void
    {
        $method = new \ReflectionMethod(EncounterRepository::class, 'list');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(EncounterRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_update(): void
    {
        $method = new \ReflectionMethod(EncounterRepository::class, 'update');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(EncounterRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(EncounterRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}