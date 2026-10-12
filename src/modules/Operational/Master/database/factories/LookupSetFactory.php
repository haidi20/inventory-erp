<?php

namespace Database\Factories;

use App\Modules\Operational\Master\Models\LookupSet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LookupSetFactory extends Factory
{
    protected $model = LookupSet::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'id' => $this->faker->numberBetween(1, 9999),
            'lookup_set_uuid' => $this->faker->numberBetween(1, 9999),
            'code' => strtoupper($this->faker->unique()->lexify('SET_????')),
            'name' => $this->faker->unique()->word(),
            'source' => 'app',
            'program_code' => strtoupper($this->faker->unique()->lexify('????')),
            'company_uuid' => (string) Str::uuid(),
            'user_can_edit_flag' => 'Y',
            'is_active' => true,
        ];
    }
}
