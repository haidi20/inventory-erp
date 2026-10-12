<?php

namespace Database\Factories;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClosingPeriodFactory extends Factory
{
    protected $model = ClosingPeriod::class;

    public function configure(): Factory
    {
        return $this->afterCreating(function (ClosingPeriod $period) {
            if (!empty($period->company_uuid) || empty($period->branch_uuid)) {
                return;
            }

            $companyUuid = Branch::query()->withoutGlobalScopes()
                ->whereKey($period->branch_uuid)
                ->value('company_uuid');

            if ($companyUuid) {
                $period->forceFill(['company_uuid' => $companyUuid])->saveQuietly();
            }
        });
    }

    public function definition(): array
    {
        return [
            'period' => $this->faker->unique()->numerify('2026-##'),
            'name' => $this->faker->word(),
            'start_date' => strtotime($this->faker->date()),
            'end_date' => strtotime($this->faker->date()),
            'status' => 'open',
            'branch_uuid' => Branch::factory()->create()->uuid,
            'company_uuid' => null,
        ];
    }
}
