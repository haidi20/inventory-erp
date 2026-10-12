<?php

namespace Tests\Unit\Entities\MasterData;

use App\Modules\Operational\Master\Entities\EmployeeEntity;
use PHPUnit\Framework\TestCase;

class EmployeeEntityTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array()
    {
        $data = [
            'uuid' => 'employee-uuid',
            'id' => 10,
            'search' => ' Dr. Strange ',
            'name' => 'Stephen Strange',
            'nik' => '1234567890123456',
            'role' => 'doctor',
            'specialization' => 'Surgeon',
            'fee_default' => 50000.50,
            'sip_number' => 'SIP-789',
            'str_number' => 'STR-456',
            'is_active' => '1' // Test numeric-string to boolean
        ];

        $entity = EmployeeEntity::fromArray($data);

        $this->assertInstanceOf(EmployeeEntity::class, $entity); // Fixed class literal
        $this->assertEquals($data['uuid'], $entity->uuid);
        $this->assertEquals(10, $entity->id);
        $this->assertEquals('Dr. Strange', $entity->search);
        $this->assertEquals($data['name'], $entity->name);
        $this->assertEquals($data['nik'], $entity->nik);
        $this->assertEquals($data['role'], $entity->role);
        $this->assertEquals($data['specialization'], $entity->specialization);
        $this->assertEquals(50000.50, $entity->fee_default);
        $this->assertEquals($data['sip_number'], $entity->sip_number);
        $this->assertEquals($data['str_number'], $entity->str_number);
        $this->assertTrue($entity->is_active);
    }

    /** @test */
    public function it_handles_filtering_logic_in_from_array()
    {
        // filter_role should fallback to role if role_filter and filter_role are missing
        $data = ['role' => 'admin'];
        $entity = EmployeeEntity::fromArray($data);
        $this->assertEquals('admin', $entity->filter_role);

        $data = ['role' => 'doctor', 'role_filter' => 'staff'];
        $entity = EmployeeEntity::fromArray($data);
        $this->assertEquals('staff', $entity->filter_role);
    }
}
