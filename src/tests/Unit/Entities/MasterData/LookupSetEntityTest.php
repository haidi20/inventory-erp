<?php

namespace Tests\Unit\Entities\MasterData;

use App\Modules\Operational\Master\Entities\LookupSetEntity;
use PHPUnit\Framework\TestCase;

class LookupSetEntityTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array()
    {
        $data = [
            'uuid' => 'set-uuid',
            'id' => 100,
            'name' => 'Gender',
            'program_code' => 'GNDR',
            'search' => 'male',
            'page' => '1',
            'per_page' => '20'
        ];

        $entity = LookupSetEntity::fromArray($data);

        $this->assertInstanceOf(LookupSetEntity::class, $entity);
        $this->assertEquals($data['uuid'], $entity->uuid);
        $this->assertEquals(100, $entity->id);
        $this->assertEquals('Gender', $entity->name);
        $this->assertEquals('GNDR', $entity->program_code);
        $this->assertEquals(1, $entity->page);
        $this->assertEquals(20, $entity->per_page);
    }

    /** @test */
    public function it_can_be_instantiated_from_a_model_object()
    {
        $model = (object) [
            'uuid' => 'model-uuid',
            'id' => 50,
            'name' => 'Mode Name',
            'program_code' => 'MCODE'
        ];

        $entity = LookupSetEntity::fromModel($model);

        $this->assertInstanceOf(LookupSetEntity::class, $entity);
        $this->assertEquals($model->uuid, $entity->uuid);
        $this->assertEquals(50, $entity->id);
        $this->assertEquals('Mode Name', $entity->name);
        $this->assertEquals('MCODE', $entity->program_code);
    }

    /** @test */
    public function it_handles_missing_keys_in_from_array()
    {
        $entity = LookupSetEntity::fromArray([]);

        $this->assertInstanceOf(LookupSetEntity::class, $entity);
        $this->assertNull($entity->uuid);
        $this->assertEquals(1, $entity->page);
        $this->assertEquals(10, $entity->per_page);
    }
}
