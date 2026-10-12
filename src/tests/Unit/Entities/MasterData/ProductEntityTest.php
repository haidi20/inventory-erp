<?php

namespace Tests\Unit\Entities\MasterData;

use App\Modules\Operational\Master\Entities\ProductEntity;
use PHPUnit\Framework\TestCase;

class ProductEntityTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array_with_aliases()
    {
        $data = [
            'uuid' => 'product-uuid',
            'branch_uuid' => 'branch-uuid',
            'company_uuid' => 'company-uuid',
            'code' => 'PROD-001',
            'name' => 'Paracetamol',
            'barcode' => '123456789',
            'product_uuid' => 'CAT-01', // Alias for category
            'base_unit_uuid' => 'UNIT-01', // Alias for unit
            'min_stock' => '10.5',
            'max_stock' => '100',
            'base_price_buy' => '5000',
            'base_price_sell' => '7500',
            'storage_bin_code' => 'BIN-A1', // Alias for location
            'note' => 'Test note', // Alias for remarks
            'is_bom_enabled' => '1', // Alias for is_assembly
            'is_active' => '0'
        ];

        $entity = ProductEntity::fromArray($data);

        $this->assertInstanceOf(ProductEntity::class, $entity);
        $this->assertEquals($data['uuid'], $entity->uuid);
        $this->assertEquals($data['branch_uuid'], $entity->branch_uuid);
        $this->assertEquals($data['company_uuid'], $entity->company_uuid);
        $this->assertEquals($data['code'], $entity->code);
        $this->assertEquals($data['name'], $entity->name);
        $this->assertEquals('CAT-01', $entity->category);
        $this->assertEquals('UNIT-01', $entity->unit);
        $this->assertEquals(10.5, $entity->min_stock);
        $this->assertEquals(100.0, $entity->max_stock);
        $this->assertEquals(5000.0, $entity->price_buy);
        $this->assertEquals(7500.0, $entity->price_sell);
        $this->assertEquals('BIN-A1', $entity->location);
        $this->assertEquals('Test note', $entity->remarks);
        $this->assertTrue($entity->is_assembly);
        $this->assertFalse($entity->is_active);
    }

    /** @test */
    public function it_can_be_converted_back_to_an_array_with_legacy_keys()
    {
        $entity = new ProductEntity(
            uuid: 'uuid-1',
            branch_uuid: 'branch-1',
            code: 'CODE-1',
            name: 'NAME-1',
            price_sell: 1000.0,
            is_assembly: true
        );

        $array = $entity->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('uuid-1', $array['uuid']);
        $this->assertEquals('branch-1', $array['branch_uuid']);
        $this->assertEquals('CODE-1', $array['code']);
        $this->assertEquals(1000.0, $array['base_price_sell']);
        $this->assertTrue($array['is_bom_enabled']);
    }

    /** @test */
    public function it_handles_default_values_correctly()
    {
        $entity = ProductEntity::fromArray([]);

        $this->assertInstanceOf(ProductEntity::class, $entity);
        $this->assertTrue($entity->is_active);
        $this->assertEquals(0.0, $entity->price_buy);
        $this->assertEquals(0.0, $entity->price_sell);
        $this->assertFalse($entity->is_assembly);
        $this->assertEquals(1, $entity->page);
    }
}
