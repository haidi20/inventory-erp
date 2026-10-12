<?php

namespace Database\Factories\Master;

use App\Modules\Operational\Master\Models\PersonContact;
use App\Modules\Operational\Master\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonContactFactory extends Factory
{
    protected $model = PersonContact::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'person_uuid' => Person::factory(),
            'type' => $this->faker->randomElement(['Phone', 'Email', 'Mobile']),
            'name' => $this->faker->name(),
            'contact' => $this->faker->phoneNumber(),
            'is_primary' => false,
            'deleted_at' => 0,
        ];
    }
}
