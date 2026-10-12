<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Operational\Master\Models\Unit;

class SoftDeleteUniqueTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_reusing_unique_code_after_soft_delete()
    {
        // 1. Setup: Create dependencies
        $branch = Branch::factory()->create();
        $unit = Unit::factory()->create();
        
        // Create a product with code 'MED-001'
        $product = Product::factory()->create([
            'branch_uuid' => $branch->uuid,
            'base_unit_uuid' => $unit->uuid,
            'code' => 'MED-001'
        ]);

        $rawProduct = DB::table('items')->where('uuid', $product->uuid)->first();
        $this->assertEquals(0, $rawProduct->deleted_at);
        $this->assertDatabaseHas('items', [
            'code' => 'MED-001',
            'deleted_at' => 0
        ]);

        // 2. Action: Soft delete the product
        $product->delete();

        $this->assertSoftDeleted('items', [
            'uuid' => $product->uuid,
            'code' => 'MED-001'
        ]);
        
        // Ensure deleted_at is now a timestamp > 0
        $deletedProduct = DB::table('items')->where('uuid', $product->uuid)->first();
        $this->assertGreaterThan(0, $deletedProduct->deleted_at);

        // 3. Verification: Try to create a NEW product with the same code
        $newProduct = Product::factory()->create([
            'branch_uuid' => $branch->uuid,
            'base_unit_uuid' => $unit->uuid,
            'code' => 'MED-001'
        ]);

        $this->assertDatabaseHas('items', [
            'uuid' => $newProduct->uuid,
            'code' => 'MED-001',
            'deleted_at' => 0
        ]);

        // Success: We have two records with code 'MED-001', one deleted and one active.
        $count = DB::table('items')->where('code', 'MED-001')->count();
        $this->assertEquals(2, $count);
    }

    /** @test */
    public function it_prevents_duplicate_code_among_active_records()
    {
        $branch = Branch::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->create([
            'branch_uuid' => $branch->uuid,
            'base_unit_uuid' => $unit->uuid,
            'code' => 'ACTIVE-01'
        ]);

        // Try to create another active record with same code
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Product::factory()->create([
            'branch_uuid' => $branch->uuid,
            'base_unit_uuid' => $unit->uuid,
            'code' => 'ACTIVE-01'
        ]);
    }
}
