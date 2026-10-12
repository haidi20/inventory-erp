<?php

namespace Database\Factories;

use App\Modules\Operational\Manufacturing\Models\Bom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BomFactory extends Factory
{
    protected $model = Bom::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'document_no' => 'BM-' . $this->faker->unique()->numerify('####'),
            'item_uuid' => (string) Str::uuid(),
            'unit_uuid' => (string) Str::uuid(),
            'qty' => 1,
            'start_date' => time(),
            'end_date' => time() + (86400 * 30),
            'note' => $this->faker->sentence(),
            'is_active' => true,
            'is_primary' => false,
        ];
    }
}
