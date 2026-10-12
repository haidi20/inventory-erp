<?php

namespace App\Modules\Core\Identity\Exports;

use App\Modules\Core\Authorization\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new UserImportTemplateInputSheet(),
            new UserImportTemplateRoleSheet(),
            new UserImportTemplateGuideSheet(),
        ];
    }
}

class UserImportTemplateInputSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return collect([]);
    }

    public function title(): string
    {
        return 'Input User';
    }

    public function headings(): array
    {
        return ['nama', 'email', 'password', 'role'];
    }
}

class UserImportTemplateRoleSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Role::query()
            ->orderBy('label')
            ->get(['label', 'name'])
            ->map(fn (Role $role) => [
                'label' => $role->label,
                'name' => $role->name,
            ]);
    }

    public function title(): string
    {
        return 'Data Role';
    }

    public function headings(): array
    {
        return ['Label Role', 'Nama Role'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class UserImportTemplateGuideSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([
            ['nama', 'Wajib', 'String', 'Nama lengkap user'],
            ['email', 'Wajib', 'String', 'Email unik user'],
            ['password', 'Wajib untuk user baru, opsional untuk user existing', 'String', 'Jika email belum ada di sistem maka password wajib diisi. Jika email sudah ada, password boleh dikosongkan agar password lama tetap dipakai.'],
            ['role', 'Opsional', 'String', 'Gunakan label role atau nama role yang tersedia di sheet "Data Role".'],
            ['', '', '', ''],
            ['CONTOH DATA:', '', '', ''],
            ['nama', 'email', 'password', 'role'],
            ['Dokter Contoh', 'dokter.contoh@example.com', 'password123', 'Dokter'],
            ['Admin Contoh', 'admin.contoh@example.com', '', 'Administrator'],
        ]);
    }

    public function title(): string
    {
        return 'Panduan';
    }

    public function headings(): array
    {
        return ['Kolom', 'Status', 'Tipe Data', 'Keterangan / Contoh'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']]],
            7 => ['font' => ['bold' => true]],
        ];
    }
}
