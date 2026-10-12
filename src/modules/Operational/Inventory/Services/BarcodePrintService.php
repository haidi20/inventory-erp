<?php

namespace App\Modules\Operational\Inventory\Services;

use App\Modules\Operational\Inventory\Entities\BarcodePrintFilterEntity;
use App\Modules\Operational\Inventory\Repositories\BarcodePrintRepository;
use Illuminate\Support\Facades\Validator;

class BarcodePrintService
{
    private const CODE39_PATTERNS = [
        '0' => 'nnnwwnwnn',
        '1' => 'wnnwnnnnw',
        '2' => 'nnwwnnnnw',
        '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw',
        '5' => 'wnnwwnnnn',
        '6' => 'nnwwwnnnn',
        '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn',
        '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw',
        'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn',
        'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn',
        'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw',
        'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn',
        'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww',
        'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn',
        'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn',
        'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww',
        'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn',
        'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw',
        'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn',
        'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn',
        'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw',
        '.' => 'wwnnnnwnn',
        ' ' => 'nwwnnnwnn',
        '$' => 'nwnwnwnnn',
        '/' => 'nwnwnnnwn',
        '+' => 'nwnnnwnwn',
        '%' => 'nnnwnwnwn',
        '*' => 'nwnnwnwnn',
    ];

    public function __construct(private BarcodePrintRepository $barcodePrintRepository)
    {
    }

    public function index(BarcodePrintFilterEntity $filterEntity): array
    {
        $data = Validator::make([
            'search' => $filterEntity->search,
            'limit' => $filterEntity->limit,
            'company_uuid' => $filterEntity->company_uuid,
            'branch_uuid' => $filterEntity->branch_uuid,
        ], [
            'search' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:48'],
            'company_uuid' => ['nullable', 'string', 'max:36'],
            'branch_uuid' => ['nullable', 'string', 'max:36'],
        ])->validate();

        $validatedEntity = BarcodePrintFilterEntity::fromArray($data);
        $search = trim((string) ($validatedEntity->search ?? ''));
        $limit = (int) ($validatedEntity->limit ?? 12);
        $items = $this->barcodePrintRepository->getItems(new BarcodePrintFilterEntity(
            search: $search,
            limit: $limit,
            company_uuid: $filterEntity->company_uuid,
            branch_uuid: $filterEntity->branch_uuid,
        ));

        return [
            'pageTitle' => 'Cetak Barcode',
            'pageSubtitle' => 'Cetak label item aktif menggunakan kode item sebagai sumber barcode Code39.',
            'search' => $search,
            'limit' => $limit,
            'stats' => [
                ['label' => 'Label Siap Cetak', 'value' => $this->number($items->count()), 'hint' => 'Item pada halaman saat ini'],
                ['label' => 'Stok Menipis', 'value' => $this->number($items->where('low_stock', true)->count()), 'hint' => 'Perlu perhatian operasional'],
                ['label' => 'Rata-rata Harga', 'value' => $this->currency($items->avg('price_sell') ?? 0), 'hint' => 'Harga jual item yang ditampilkan'],
            ],
            'items' => $items->map(function (array $item): array {
                return [
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'unit' => $item['unit'],
                    'price_sell' => $this->currency($item['price_sell']),
                    'stock' => $this->number($item['stock']),
                    'status' => $item['status'],
                    'barcode_segments' => $this->code39Segments($item['code']),
                ];
            })->all(),
            'emptyMessage' => 'Belum ada item aktif yang bisa dicetak barcodenya.',
            'note' => 'Jika nanti tersedia field barcode khusus pada master produk, halaman ini bisa diarahkan ke field tersebut tanpa mengubah controller.',
        ];
    }

    private function code39Segments(string $value): array
    {
        $sanitized = strtoupper(trim($value));
        $sanitized = preg_replace('/[^0-9A-Z\-\.\ \$/\+%]/', '-', $sanitized) ?: 'ITEM';
        $normalized = '*' . $sanitized . '*';
        $segments = [];

        foreach (str_split($normalized) as $character) {
            $pattern = self::CODE39_PATTERNS[$character] ?? self::CODE39_PATTERNS['-'];

            foreach (str_split($pattern) as $index => $width) {
                $segments[] = [
                    'is_bar' => $index % 2 === 0,
                    'width' => $width === 'w' ? 6 : 2,
                ];
            }

            $segments[] = [
                'is_bar' => false,
                'width' => 2,
            ];
        }

        return $segments;
    }

    private function currency(float|int $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function number(float|int $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
