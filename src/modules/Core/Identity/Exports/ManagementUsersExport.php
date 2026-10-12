<?php

namespace App\Modules\Core\Identity\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ManagementUsersExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private readonly Collection $users)
    {
    }

    public function sheets(): array
    {
        return [
            new ManagementUsersDataSheet($this->users),
            new ManagementUsersGuideSheet(),
        ];
    }
}

class ManagementUsersDataSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private readonly Collection $users)
    {
    }

    public function collection(): Collection
    {
        return $this->users;
    }

    public function title(): string
    {
        return 'Data User';
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Role',
            'Pegawai Terkait',
            'Tanggal Dibuat',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->roles->pluck('label')->implode(', '),
            $user->employee?->name ?? '-',
            $user->created_at?->format('d M Y'),
        ];
    }
}

class ManagementUsersGuideSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle, WithStyles
{
    public function title(): string
    {
        return 'Penjelasan';
    }

    public function headings(): array
    {
        return ['Bagian', 'Deskripsi'];
    }

    public function collection(): Collection
    {
        return collect([
            ['Tujuan', 'Sheet "Data User" berisi hasil export user sesuai filter yang dipilih di layar.'],
            ['Kolom Nama', 'Nama lengkap user di aplikasi.'],
            ['Kolom Email', 'Email login user (unik).'],
            ['Kolom Role', 'Label role aktif yang melekat pada user.'],
            ['Kolom Pegawai Terkait', 'Nama pegawai jika akun user sudah dikaitkan ke data pegawai.'],
            ['Kolom Tanggal Dibuat', 'Tanggal pembuatan akun user dalam format d M Y.'],
            ['Catatan Filter', 'Jika filter pencarian/role aktif saat export, maka data di sheet utama mengikuti filter tersebut.'],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
