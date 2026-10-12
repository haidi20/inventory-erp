<?php

namespace Database\Factories;

use App\Modules\Operational\Manufacturing\Models\Assembly;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssemblyFactory extends Factory
{
    protected $model = Assembly::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'document_no' => 'AS-' . $this->faker->unique()->numerify('####'),
            'date' => time(),
            'location_uuid' => (string) Str::uuid(),
            'status' => 'pending',
            'note' => $this->faker->sentence(),
        ];
    }
}
