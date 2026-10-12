<?php

namespace App\Modules\Finance\Ledger\Exports;

use App\Modules\Finance\Ledger\Models\GeneralLedger;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class GeneralLedgerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $accountInfo;

    public function __construct(array $report)
    {
        $this->data = collect($report['transactions']);
        $this->accountInfo = $report['account_info'];
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Referensi',
            'Akun',
            'Keterangan',
            'Debit',
            'Kredit',
            'Saldo'
        ];
    }

    /**
     * @param array $ledger
     */
    public function map($ledger): array
    {
        return [
            \Carbon\Carbon::parse($ledger['date'])->format('d/m/Y'),
            $ledger['reference'],
            $this->accountInfo['code'] . ' - ' . $this->accountInfo['name'],
            $ledger['description'],
            (float)$ledger['debit'],
            (float)$ledger['credit'],
            (float)$ledger['balance'],
        ];
    }
}
