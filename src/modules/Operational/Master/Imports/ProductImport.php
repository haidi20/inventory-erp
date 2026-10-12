<?php

namespace App\Modules\Operational\Master\Imports;

use App\Modules\Operational\Master\Models\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Row;

class ProductImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();
        
        $code = $data['code'] ?? $data['kode'] ?? null;
        $name = $data['name'] ?? $data['nama'] ?? null;
        
        if (!$code || !$name) {
            return;
        }

        $user = \Illuminate\Support\Facades\Auth::user();

        Product::updateOrCreate(
            [
                'code' => (string) $code,
                'branch_uuid' => current_branch_uuid(),
            ],
            [
                'name' => $name,
                'category' => $data['category'] ?? $data['kategori'] ?? null,
                'unit' => $data['unit'] ?? $data['satuan'] ?? null,
                'price_buy' => $data['price_buy'] ?? $data['harga_beli'] ?? 0,
                'price_sell' => $data['price_sell'] ?? $data['harga_jual'] ?? 0,
                'min_stock' => $data['min_stock'] ?? $data['stok_minimum'] ?? 0,
                'is_active' => true,
                'company_uuid' => current_company_uuid(),
            ]
        );
    }
}
