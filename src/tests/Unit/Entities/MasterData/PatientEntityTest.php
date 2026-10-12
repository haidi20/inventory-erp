<?php

namespace Tests\Unit\Entities\MasterData;

use App\Modules\Clinical\Registration\Entities\PatientEntity;
use PHPUnit\Framework\TestCase;

class PatientEntityTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array()
    {
        $data = [
            'uuid' => 'uuid-123',
            'id' => 1,
            'search' => ' John Doe ',
            'gender' => 'male',
            'medical_record_no' => 'MR-001',
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'phone' => '08123456789',
            'bpjs_number' => 'BPJS-123',
            'is_active' => 'true', // Test string to boolean conversion
        ];

        $entity = PatientEntity::fromArray($data);

        $this->assertInstanceOf(PatientEntity::class, $entity);
        $this->assertEquals($data['uuid'], $entity->uuid);
        $this->assertEquals(1, $entity->id);
        $this->assertEquals('John Doe', $entity->search); // Trimmed
        $this->assertEquals($data['gender'], $entity->gender);
        $this->assertEquals($data['medical_record_no'], $entity->medical_record_no);
        $this->assertEquals($data['name'], $entity->name);
        $this->assertEquals($data['birth_date'], $entity->birth_date);
        $this->assertEquals($data['phone'], $entity->phone);
        $this->assertEquals($data['bpjs_number'], $entity->bpjs_number);
        $this->assertTrue($entity->is_active);
    }

    /** @test */
    public function it_handles_null_values_correctly()
    {
        $entity = PatientEntity::fromArray([]);

        $this->assertInstanceOf(PatientEntity::class, $entity);
        $this->assertNull($entity->uuid);
        $this->assertNull($entity->id);
        $this->assertNull($entity->gender);
        $this->assertNull($entity->is_active);
    }
}
