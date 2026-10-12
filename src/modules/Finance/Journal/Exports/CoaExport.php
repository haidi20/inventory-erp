<?php

namespace App\Modules\Finance\Journal\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export Chart of Accounts (COA) ke Excel.
 * Data berupa array AccountEntity hasil AccountingRepository::getAllAccounts.
 */
class CoaExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Collection|array $data)
    {
        $this->data = is_array($data) ? collect($data) : $data;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Akun',
            'Tipe',
            'Tipe Saldo',
            'Header',
            'Status',
        ];
    }

    /**
     * @param object $account AccountEntity/account row
     */
    public function map($account): array
    {
        return [
            $account->code ?? '',
            $account->name ?? '',
            $account->type ?? '',
            $account->type_saldo ?? '',
            ($account->is_header ?? false) ? 'Ya' : 'Tidak',
            ($account->is_active ?? false) ? 'Aktif' : 'Nonaktif',
        ];
    }
}