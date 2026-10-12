<?php

namespace Database\Factories\Operational;

use App\Modules\Operational\Master\Models\PersonCustomField;
use App\Modules\Operational\Master\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonCustomFieldFactory extends Factory
{
    protected $model = PersonCustomField::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'person_uuid' => Person::factory(),
            'field_key' => $this->faker->word(),
            'field_value' => $this->faker->sentence(),
            'field_type' => 'string',
            'deleted_at' => 0,
        ];
    }
}
