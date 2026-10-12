<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DataRetentionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create();
    }

    public function test_soft_deleted_patient_is_retained_for_recovery(): void
    {
        $patient = Patient::create([
            'uuid'             => (string) Str::uuid(),
            'branch_uuid'      => $this->branch->uuid,
            'medical_record_no'=> 'RM-RET-001',
            'name'             => 'Pasien Retensi',
            'gender'           => 'P',
            'is_active'        => true,
        ]);

        $patient->delete();

        // HasSoftDeleteUnique: deleted_at menjadi Unix timestamp (non-zero), bukan NULL.
        // assertSoftDeleted memeriksa deleted_at IS NOT NULL → kompatibel.
        // Gunakan 'uuid' bukan 'id' — tabel patients tidak punya kolom 'id'.
        $this->assertSoftDeleted('patients', ['uuid' => $patient->uuid]);
        $this->assertNull(Patient::find($patient->uuid));

        $archivedPatient = Patient::withTrashed()->find($patient->uuid);

        $this->assertNotNull($archivedPatient);
        $this->assertEquals('RM-RET-001', $archivedPatient->medical_record_no);

        $archivedPatient->restore();

        $this->assertNotNull(Patient::find($patient->uuid));
    }

    public function test_soft_deleted_product_remains_available_in_archive_scope(): void
    {
        $product = Product::create([
            'uuid'         => (string) Str::uuid(),
            'branch_uuid'  => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code'         => 'RET-PRD-001',
            'name'         => 'Produk Arsip',
            'category'     => 'Obat',
            'unit'         => 'box',
            'price_buy'    => 10000,
            'price_sell'   => 15000,
            'min_stock'    => 1,
            // 'stock' dihapus — kolom 'stock' tidak ada di tabel 'items';
            // stok dikelola via tabel item_balances terpisah.
            'is_active'    => true,
        ]);

        $product->delete();

        // Gunakan 'uuid' bukan 'id'.
        $this->assertSoftDeleted('items', ['uuid' => $product->uuid]);
        $this->assertNull(Product::query()->find($product->uuid));
        $this->assertNotNull(Product::withTrashed()->find($product->uuid));
    }
}
