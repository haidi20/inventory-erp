<?php

namespace Database\Factories;

use App\Modules\Finance\Journal\Models\JournalLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalLineFactory extends Factory
{
    protected $model = JournalLine::class;

    public function definition(): array
    {
        return [
            'journal_uuid' => 1,
            'account_uuid' => 1,
            'debit' => 0,
            'credit' => 0,
        ];
    }
}
