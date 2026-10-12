<?php

namespace Database\Factories\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'transaction_code' => $this->faker->unique()->numerify('GJ-2026-#####'),
            'reference_no' => $this->faker->unique()->numerify('REF-2026-#####'),
            'journal_date' => time(),
            'status' => JournalStatus::POSTED,
            'deleted_at' => 0
        ];
    }
}
