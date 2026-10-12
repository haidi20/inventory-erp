<?php

namespace Database\Factories\Master;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            // Buat company sungguhan agar FK user_companies/user_branches
            // dan semua relasi berbasis company_uuid valid.
            'company_uuid' => Company::factory(),
            'name' => $this->faker->city() . ' Branch',
            'short_name' => strtoupper($this->faker->unique()->lexify('???')),
            'is_active' => true,
        ];
    }
}
