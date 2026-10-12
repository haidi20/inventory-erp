<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Finance\Ledger\Models\Transaction;
use App\Modules\Finance\Ledger\Models\TransactionDetail;
use App\Modules\Operational\Inventory\Models\Stock;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * TDD test penyeragaman primary key legacy id -> uuid (2026-10-09).
 *
 * Empat tabel warisan aplikasi POS lama ber-PK char('id', 36) yang diisi
 * UUID oleh HasUuids. Supaya satu konvensi dengan 120+ tabel lain, PK-nya
 * di-rename menjadi uuid, termasuk deklarasi model dan relasi Eloquent
 * yang menunjuk ke sana. Relasi ProductPurchase/ProductSale/ProductQuotation
 * ke Product/Purchase/Sale juga diperbaiki (sebelumnya menunjuk 'id'
 * padahal parent ber-PK uuid -- relasinya pasti tidak pernah match).
 */
class LegacyPrimaryKeyUuidTest extends TestCase
{
    use RefreshDatabase;

    /** Tabel yang PK-nya harus sudah rename ke uuid. */
    public static function legacyTableProvider(): array
    {
        return [
            'transactions' => ['transactions', Transaction::class],
            'transaction_details' => ['transaction_details', TransactionDetail::class],
            'shifts' => ['shifts', Shift::class],
            'stocks' => ['stocks', Stock::class],
        ];
    }

    /**
     * @dataProvider legacyTableProvider
     */
    public function test_legacy_tables_have_uuid_primary_key_column(string $table, string $modelClass): void
    {
        $this->assertTrue(
            Schema::hasColumn($table, 'uuid'),
            "Tabel {$table} harus punya kolom uuid (hasil rename dari id)."
        );
        $this->assertFalse(
            Schema::hasColumn($table, 'id'),
            "Tabel {$table} tidak boleh lagi punya kolom id."
        );

        $model = new $modelClass;
        $this->assertSame('uuid', $model->getKeyName(), "{$modelClass} harus ber-PK uuid.");
    }

    public function test_transaction_belongs_to_shift_via_uuid(): void
    {
        $companyUuid = '11111111-1111-7111-1111-111111111111';
        $branchUuid = '22222222-2222-7222-2222-222222222222';

        $location = Location::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'LOC-SH-' . uniqid(),
            'name' => 'Lokasi Shift Test',
        ]);

        $shift = Shift::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'shift_number' => 1,
            'date' => time(),
            'start_time' => time(),
            'opening_balance' => 0,
            'location_uuid' => $location->uuid,
            'user_uuid' => '33333333-3333-7333-3333-333333333333',
        ]);

        $transaction = Transaction::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'shift_uuid' => $shift->uuid,
            'location_uuid' => $location->uuid,
            'user_uuid' => '33333333-3333-7333-3333-333333333333',
            'order_type_uuid' => $location->uuid,
            'date' => time(),
            'total_amount' => 10000,
            'total_qty' => 1,
        ]);

        $this->assertSame($shift->uuid, $transaction->shift->uuid,
            'Relasi Transaction->shift harus match via kolom uuid.');
    }

    public function test_transaction_detail_belongs_to_transaction_via_uuid(): void
    {
        $companyUuid = '11111111-1111-7111-1111-111111111111';
        $branchUuid = '22222222-2222-7222-2222-222222222222';

        $location = Location::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'LOC-TD-' . uniqid(),
            'name' => 'Lokasi Detail Test',
        ]);

        $product = Product::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'ITM-TD-' . uniqid(),
            'name' => 'Item Detail Test',
        ]);

        $transaction = Transaction::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'location_uuid' => $location->uuid,
            'user_uuid' => '33333333-3333-7333-3333-333333333333',
            'order_type_uuid' => $location->uuid,
            'date' => time(),
            'total_amount' => 10000,
            'total_qty' => 1,
        ]);

        $detail = TransactionDetail::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'transaction_uuid' => $transaction->uuid,
            'product_uuid' => $product->uuid,
            'product_name' => 'Item Detail Test',
            'product_price' => 10000,
            'qty' => 1,
            'subtotal' => 10000,
        ]);

        $this->assertSame($transaction->uuid, $detail->transaction->uuid,
            'Relasi TransactionDetail->transaction harus match via kolom uuid.');
    }

    /**
     * Relasi ProductSale/ProductPurchase/ProductQuotation sebelumnya menunjuk
     * 'id' padahal parent ber-PK uuid -- pastinya tidak pernah match.
     */
    public static function brokenRelationProvider(): array
    {
        return [
            'ProductSale->product' => [\App\Modules\Operational\Master\Models\ProductSale::class, 'product', \App\Modules\Operational\Master\Models\Product::class],
            'ProductSale->sale' => [\App\Modules\Operational\Master\Models\ProductSale::class, 'sale', \App\Modules\Operational\Sales\Models\Sale::class],
            'ProductPurchase->product' => [\App\Modules\Operational\Master\Models\ProductPurchase::class, 'product', \App\Modules\Operational\Master\Models\Product::class],
            'ProductPurchase->purchase' => [\App\Modules\Operational\Master\Models\ProductPurchase::class, 'purchase', \App\Modules\Operational\Procurement\Models\Purchase::class],
            'ProductQuotation->product' => [\App\Modules\Operational\Master\Models\ProductQuotation::class, 'product', \App\Modules\Operational\Master\Models\Product::class],
        ];
    }

    /** @dataProvider brokenRelationProvider */
    public function test_product_detail_relations_resolve_via_uuid(
        string $childClass,
        string $relation,
        string $parentClass,
    ): void {
        $parent = new $parentClass;
        $this->assertSame('uuid', $parent->getKeyName(),
            "{$parentClass} harus ber-PK uuid.");

        $child = new $childClass;
        $fk = $child->{$relation}()->getForeignKeyName();
        $ownerKey = $child->{$relation}()->getOwnerKeyName();

        $this->assertSame('uuid', $ownerKey,
            "Relasi {$childClass}->{$relation} harus menunjuk kolom uuid, bukan id.");
        $this->assertStringEndsWith('_uuid', $fk);
    }
}
