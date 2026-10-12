<?php

namespace Database\Factories\Accounting;

use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use Illuminate\Support\Str;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::factory(),
            'company_uuid' => (string) Str::uuid(),
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->word(),
            'level' => 1,
            'type' => AccountType::ASSET,
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
            'is_header' => false,
            'is_active' => true,
            'deleted_at' => 0
        ];
    }
}
