<?php

namespace Database\Factories\Master;

use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => 'Company ' . $this->faker->unique()->uuid(),
            'is_active' => true,
        ];
    }
}
