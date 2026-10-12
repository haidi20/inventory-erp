<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\Tax;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'uuid'       => (string) Str::uuid(),
            'branch_uuid'  => Branch::factory(),
            'company_uuid' => Company::factory(),
            'tax_code'   => strtoupper($this->faker->unique()->lexify('TAX-??')),
            'percentage' => $this->faker->numberBetween(5, 15),
            'is_active'  => true,
            'is_primary' => false,
            'is_pph'     => false,
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
