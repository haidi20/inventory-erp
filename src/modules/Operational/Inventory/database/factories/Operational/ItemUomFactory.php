<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Master\Models\ItemUom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * ItemUomFactory
 * 
 * Factory untuk data Satuan Barang (UOM) khusus per item.
 * Digunakan untuk mendukung pengujian TDD dml integrasi Konversi Satuan.
 */
class ItemUomFactory extends Factory
{
    protected $model = ItemUom::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'item_uuid' => (string) Str::uuid(),
            'uom_code' => $this->faker->unique()->lexify('??????'),
            'to_qty' => 1.0,
            'is_base_uom' => false,
            'barcode' => $this->faker->ean13(),
        ];
    }
}
