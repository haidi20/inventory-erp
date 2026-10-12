<?php

namespace Database\Factories\Accounting;

use App\Modules\Finance\Journal\Models\Asset;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $branch = Branch::factory()->create();

        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $branch->uuid,
            'company_uuid' => $branch->company_uuid,
            'code' => $this->faker->unique()->bothify('AST-####'),
            'name' => $this->faker->words(2, true),
            'quantity' => 1,
            'asset_value' => $this->faker->randomFloat(2, 1000, 100000),
            'life_year' => $this->faker->numberBetween(1, 10),
            'yearly_depreciation' => $this->faker->randomFloat(2, 100, 10000),
            'is_active' => true,
            'deleted_at' => 0,
        ];
    }
}
