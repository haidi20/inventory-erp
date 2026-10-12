<?php

namespace Database\Factories\Operational;

use App\Modules\Finance\Ledger\Models\SalesInvoiceAging;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalesInvoiceAgingFactory extends Factory
{
    protected $model = SalesInvoiceAging::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'sales_invoice_uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'due_date' => time() + (30 * 24 * 60 * 60),
            'amount' => $this->faker->numberBetween(100000, 1000000),
            'remaining' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
}
