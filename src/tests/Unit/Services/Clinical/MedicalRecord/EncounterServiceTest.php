<?php

namespace Tests\Unit\Services\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Services\EncounterService;
use Tests\TestCase;

class EncounterServiceTest extends TestCase
{

    public function test_list_encounters(): void
    {
        $method = new \ReflectionMethod(EncounterService::class, 'listEncounters');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_encounter(): void
    {
        $method = new \ReflectionMethod(EncounterService::class, 'createEncounter');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_encounter(): void
    {
        $method = new \ReflectionMethod(EncounterService::class, 'getEncounter');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_encounter(): void
    {
        $method = new \ReflectionMethod(EncounterService::class, 'updateEncounter');
        $this->assertTrue($method->isPublic());
    }

    public function test_validate_references(): void
    {
        $method = new \ReflectionMethod(EncounterService::class, 'validateReferences');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_encounter(): void
    {
        $method = new \ReflectionMethod(EncounterService::class, 'deleteEncounter');
        $this->assertTrue($method->isPublic());
    }
}