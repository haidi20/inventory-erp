<?php

namespace Database\Factories;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Enums\AccountType;
use App\Modules\Finance\Journal\Enums\FinancialStatementType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->word(),
            'level' => 1,
            'type' => AccountType::ASSET,
            'financial_statement' => FinancialStatementType::BALANCE_SHEET,
            'is_header' => false,
            'is_active' => true,
        ];
    }
}
