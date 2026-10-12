<?php

namespace App\Modules\Operational\Master\Exports;

use App\Modules\Operational\Master\Entities\LookupDocumentEntity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LookupSetValueExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function collection()
    {
        return collect($this->document->items)->map(function ($item) {
            $details = collect($item->details)->pluck('value', 'idx')->toArray();
            
            return [
                'display_name' => $item->display_name ?? null,
                'd1' => $details[1] ?? '',
                'd2' => $details[2] ?? '',
                'd3' => $details[3] ?? '',
                'd4' => $details[4] ?? '',
                'd5' => $details[5] ?? '',
                'd6' => $details[6] ?? '',
                'd7' => $details[7] ?? '',
                'd8' => $details[8] ?? '',
                'd9' => $details[9] ?? '',
                'd10' => $details[10] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return $this->document->headers;
    }
}
