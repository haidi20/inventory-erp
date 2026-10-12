<?php

namespace Tests\Unit\Entities\MasterData;

use App\Modules\Operational\Master\Entities\CustomerSupplierFilterEntity;
use PHPUnit\Framework\TestCase;

class CustomerSupplierFilterEntityTest extends TestCase
{
    /** @test */
    public function it_holds_search_value()
    {
        $entity = CustomerSupplierFilterEntity::fromArray(['search' => 'klinik sehat']);

        $this->assertInstanceOf(CustomerSupplierFilterEntity::class, $entity);
        $this->assertSame('klinik sehat', $entity->search);
    }

    /** @test */
    public function it_defaults_search_to_null_when_missing()
    {
        $entity = CustomerSupplierFilterEntity::fromArray([]);

        $this->assertNull($entity->search);
    }

    /** @test */
    public function it_converts_to_array_roundtrip()
    {
        $entity = new CustomerSupplierFilterEntity(search: 'obat');

        $this->assertSame(['search' => 'obat'], $entity->toArray());
    }
}
