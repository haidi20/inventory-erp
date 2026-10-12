<?php

namespace App\Modules\Analytics\Reports\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BalanceSheetExport implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data
    ) {}

    public function view(): View
    {
        return view('reports.exports.balance_sheet', $this->data);
    }

    public function title(): string
    {
        return 'Neraca';
    }
}
