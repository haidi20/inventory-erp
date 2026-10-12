<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Master\Models\PaymentTerm;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentTermFactory extends Factory
{
    protected $model = PaymentTerm::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => Company::factory(),
            'name' => $this->faker->sentence(2),
            'due_days' => $this->faker->numberBetween(0, 30),
            'is_active' => true,
            'deleted_at' => 0
        ];
    }
}
