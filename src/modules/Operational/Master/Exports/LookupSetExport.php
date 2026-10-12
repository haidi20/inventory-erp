<?php

namespace App\Modules\Operational\Master\Exports;

use App\Modules\Operational\Master\Entities\LookupDocumentEntity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LookupSetExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function collection()
    {
        return collect($this->document->items)->map(function ($item) {
            return [
                'id' => $item->id ?? null,
                'kode' => $item->program_code ?? null,
                'nama' => $item->name ?? null,
                'deskripsi' => $item->description ?? null,
                'editable' => $item->user_can_edit_flag ?? null,
            ];
        });
    }

    public function headings(): array
    {
        return $this->document->headers;
    }
}
