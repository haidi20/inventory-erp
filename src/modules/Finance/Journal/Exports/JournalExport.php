<?php

namespace App\Modules\Finance\Journal\Exports;

use App\Modules\Finance\Journal\Models\Journal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class JournalExport implements FromCollection, WithHeadings, WithMapping
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
            'Tanggal',
            'No. Referensi',
            'Kode Transaksi',
            'Keterangan',
            'Akun',
            'Debit',
            'Kredit'
        ];
    }

    /**
     * @param Journal $journal
     */
    public function map($journal): array
    {
        $rows = [];
        foreach ($journal->lines as $line) {
            $rows[] = [
                $journal->journal_date->format('d/m/Y'),
                $journal->reference_no,
                $journal->transaction_code,
                $journal->description,
                $line->account->code . ' - ' . $line->account->name,
                (float)$line->debit,
                (float)$line->credit,
            ];
        }
        return $rows;
    }
}
