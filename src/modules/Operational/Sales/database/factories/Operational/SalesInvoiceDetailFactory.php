<?php

namespace Database\Factories\Operational;

use App\Modules\Finance\Ledger\Models\SalesInvoiceDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalesInvoiceDetailFactory extends Factory
{
    protected $model = SalesInvoiceDetail::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'sales_invoice_uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'item_uuid' => (string) Str::uuid(),
            'qty' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->numberBetween(5000, 100000),
            'discount' => 0,
            'total' => 0,
        ];
    }
}
