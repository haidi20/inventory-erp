<?php

namespace App\Modules\Analytics\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class TrialBalanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
            'Tipe',
            'Saldo Awal',
            'Mutasi Debit',
            'Mutasi Kredit',
            'Saldo Akhir'
        ];
    }

    /**
     * @param \App\Modules\Finance\Journal\Entities\AccountBalanceEntity $row
     */
    public function map($row): array
    {
        return [
            $row->account_code,
            $row->account_name,
            ucfirst($row->account_type),
            (float)$row->opening_balance,
            (float)$row->total_debit,
            (float)$row->total_credit,
            (float)$row->closing_balance,
        ];
    }
}
