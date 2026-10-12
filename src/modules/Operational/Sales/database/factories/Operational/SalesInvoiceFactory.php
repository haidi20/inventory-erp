<?php

namespace Database\Factories\Operational;

use App\Modules\Finance\Ledger\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalesInvoiceFactory extends Factory
{
    protected $model = SalesInvoice::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => (string) Str::uuid(),
            'company_uuid' => (string) Str::uuid(),
            'person_uuid' => (string) Str::uuid(),
            'invoice_no' => 'INV-' . $this->faker->unique()->numerify('####'),
            'invoice_date' => time(),
            'payment_term_uuid' => (string) Str::uuid(),
            'location_uuid' => (string) Str::uuid(),
            'total_amount' => $this->faker->numberBetween(100000, 5000000),
            'status' => 'draft',
        ];
    }
}
