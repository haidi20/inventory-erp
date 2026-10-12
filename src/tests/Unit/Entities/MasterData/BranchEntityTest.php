<?php

namespace Tests\Unit\Entities\MasterData;

use App\Modules\Core\Organization\Entities\BranchEntity;
use PHPUnit\Framework\TestCase;

class BranchEntityTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array()
    {
        $data = [
            'uuid' => 'branch-uuid',
            'name' => 'Main Branch',
            'short_name' => 'MB',
            'description' => 'Secondary Branch Description',
            'is_active' => false,
            'search' => 'Main',
            'search_column' => 'name',
            'page' => 2,
            'per_page' => 50,
            'actor_uuid' => 'user-uuid'
        ];

        $entity = BranchEntity::fromArray($data);

        $this->assertInstanceOf(BranchEntity::class, $entity);
        $this->assertEquals($data['uuid'], $entity->uuid);
        $this->assertEquals($data['name'], $entity->name);
        $this->assertEquals($data['short_name'], $entity->short_name);
        $this->assertEquals($data['description'], $entity->description);
        $this->assertFalse($entity->is_active);
        $this->assertEquals('Main', $entity->search);
        $this->assertEquals('name', $entity->search_column);
        $this->assertEquals(2, $entity->page);
        $this->assertEquals(50, $entity->per_page);
        $this->assertEquals($data['actor_uuid'], $entity->actor_uuid);
    }

    /** @test */
    public function it_uses_default_values_correctly()
    {
        $entity = BranchEntity::fromArray([]);

        $this->assertInstanceOf(BranchEntity::class, $entity);
        $this->assertTrue($entity->is_active);
        $this->assertEquals(1, $entity->page);
        $this->assertEquals(15, $entity->per_page);
        $this->assertNull($entity->uuid);
    }
}
