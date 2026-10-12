<?php

namespace Database\Factories\Accounting;

use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JournalLineFactory extends Factory
{
    protected $model = JournalLine::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'journal_uuid' => Journal::factory(),
            'account_uuid' => Account::factory(),
            'debit' => 0,
            'credit' => 0,
            'deleted_at' => 0
        ];
    }
}
