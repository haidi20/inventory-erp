<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Master\Models\ItemConversion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * ItemConversionFactory
 * 
 * Factory untuk data konversi satuan barang.
 */
class ItemConversionFactory extends Factory
{
    protected $model = ItemConversion::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'item_uuid' => (string) Str::uuid(),
            'from_unit_uuid' => (string) Str::uuid(),
            'to_unit_uuid' => (string) Str::uuid(),
            'conversion_ratio' => 10.0,
            'is_active' => true,
        ];
    }
}
