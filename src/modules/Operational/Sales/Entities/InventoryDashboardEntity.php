<?php

namespace App\Modules\Operational\Sales\Entities;

class InventoryDashboardEntity
{
    public function __construct(
        public readonly int   $totalStok,
        public readonly int   $totalProduk,
        public readonly float $nilaiStok,
        public readonly float $totalBeli,
        public readonly float $totalJual,
        public readonly float $totalOmzetToday,
        public readonly float $totalHutang,
        public readonly float $totalPiutang,
        public readonly int   $stokMinimum,
        public readonly array $stokItems,
        public readonly array $beliItems,
        public readonly array $omzetItems,
        public readonly array $omzetTodayItems,
        public readonly array $hutangItems,
        public readonly array $piutangItems,
        public readonly array $stokChartLabels,
        public readonly array $stokMasukSeries,
        public readonly array $stokKeluarSeries,
    ) {}

    public static function dummy(): self
    {
        $stokItems = [
            ['kode' => 'AMX-500', 'nama' => 'Amoxicillin 500mg', 'nilai' => 1500000, 'minimal_stok' => 50, 'kurang' => 40],
            ['kode' => 'DEX-05',  'nama' => 'Dexamethasone 0.5mg', 'nilai' => 500000, 'minimal_stok' => 30, 'kurang' => 22],
            ['kode' => 'ANT-SYR', 'nama' => 'Antasida Sirup', 'nilai' => 400000, 'minimal_stok' => 20, 'kurang' => 15],
            ['kode' => 'BDP-01',  'nama' => 'Betadine Solution 30ml', 'nilai' => 1080000, 'minimal_stok' => 25, 'kurang' => 13],
            ['kode' => 'PCT-500', 'nama' => 'Paracetamol 500mg', 'nilai' => 2000000, 'minimal_stok' => 100, 'kurang' => 80],
        ];

        $beliItems = [
            [
                'tanggal' => '2026-09-08', 'no_transaksi' => 'PO-2026090001',
                'supplier' => 'PT Global Health',
                'items' => [
                    ['kode' => 'AMX-500', 'nama' => 'Amoxicillin 500mg', 'qty_order' => 100, 'qty_terima' => 60, 'qty_sisa' => 40],
                    ['kode' => 'DEX-05',  'nama' => 'Dexamethasone 0.5mg', 'qty_order' => 50, 'qty_terima' => 28, 'qty_sisa' => 22],
                ],
            ],
            [
                'tanggal' => '2026-09-07', 'no_transaksi' => 'PO-2026090002',
                'supplier' => 'PT Lestari Medika',
                'items' => [
                    ['kode' => 'ANT-SYR', 'nama' => 'Antasida Sirup', 'qty_order' => 30, 'qty_terima' => 15, 'qty_sisa' => 15],
                ],
            ],
            [
                'tanggal' => '2026-09-06', 'no_transaksi' => 'PO-2026090003',
                'supplier' => 'PT Pharma Utama',
                'items' => [
                    ['kode' => 'PCT-500', 'nama' => 'Paracetamol 500mg', 'qty_order' => 200, 'qty_terima' => 120, 'qty_sisa' => 80],
                ],
            ],
        ];

        $omzetItems = [
            ['tanggal' => '2026-09-08', 'no_transaksi' => 'SO-2026090001', 'customer' => 'Apotek Sehat', 'alamat' => 'Jl. Kesehatan No.1', 'nilai' => 1850000, 'terbayar' => 1000000, 'sisa' => 850000],
            ['tanggal' => '2026-09-07', 'no_transaksi' => 'SO-2026090002', 'customer' => 'Klinik Maju', 'alamat' => 'Jl. Maju No.5', 'nilai' => 3200000, 'terbayar' => 3200000, 'sisa' => 0],
            ['tanggal' => '2026-09-06', 'no_transaksi' => 'SO-2026090003', 'customer' => 'RS Harapan', 'alamat' => 'Jl. Harapan No.10', 'nilai' => 5600000, 'terbayar' => 2000000, 'sisa' => 3600000],
            ['tanggal' => '2026-09-05', 'no_transaksi' => 'SO-2026090004', 'customer' => 'Puskesmas 1', 'alamat' => 'Jl. Puskesmas No.3', 'nilai' => 900000, 'terbayar' => 900000, 'sisa' => 0],
        ];

        $omzetTodayItems = array_filter($omzetItems, fn($item) => $item['tanggal'] === '2026-09-08');

        $hutangItems = [
            ['tanggal' => '2026-09-08', 'no_transaksi' => 'PO-2026090001', 'supplier' => 'PT Global Health', 'alamat' => 'Jl. Industri No.12', 'nilai' => 5200000, 'terbayar' => 2000000, 'sisa' => 3200000],
            ['tanggal' => '2026-09-07', 'no_transaksi' => 'PO-2026090002', 'supplier' => 'PT Lestari Medika', 'alamat' => 'Jl. Medika No.7', 'nilai' => 3800000, 'terbayar' => 0, 'sisa' => 3800000],
            ['tanggal' => '2026-09-06', 'no_transaksi' => 'PO-2026090003', 'supplier' => 'PT Pharma Utama', 'alamat' => 'Jl. Farma No.3', 'nilai' => 7100000, 'terbayar' => 3000000, 'sisa' => 4100000],
        ];

        $piutangItems = [
            ['tanggal' => '2026-09-08', 'no_transaksi' => 'SO-2026090001', 'customer' => 'Apotek Sehat', 'alamat' => 'Jl. Kesehatan No.1', 'nilai' => 1850000, 'terbayar' => 1000000, 'sisa' => 850000],
            ['tanggal' => '2026-09-06', 'no_transaksi' => 'SO-2026090003', 'customer' => 'RS Harapan', 'alamat' => 'Jl. Harapan No.10', 'nilai' => 5600000, 'terbayar' => 2000000, 'sisa' => 3600000],
        ];

        return new self(
            totalStok: 1284,
            totalProduk: 342,
            nilaiStok: 187450000,
            totalBeli: 45200000,
            totalJual: 62800000,
            totalOmzetToday: 1850000,
            totalHutang: 11100000,
            totalPiutang: 4450000,
            stokMinimum: count($stokItems),
            stokItems: $stokItems,
            beliItems: $beliItems,
            omzetItems: $omzetItems,
            omzetTodayItems: array_values($omzetTodayItems),
            hutangItems: $hutangItems,
            piutangItems: $piutangItems,
            stokChartLabels: ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
            stokMasukSeries: [120, 95, 140, 88, 110, 130, 75],
            stokKeluarSeries: [80, 110, 90, 105, 95, 115, 60],
        );
    }

    public function toArray(): array
    {
        return [
            'totalStok'        => $this->totalStok,
            'totalProduk'      => $this->totalProduk,
            'nilaiStok'        => $this->nilaiStok,
            'totalBeli'        => $this->totalBeli,
            'totalJual'        => $this->totalJual,
            'totalOmzetToday'  => $this->totalOmzetToday,
            'totalHutang'      => $this->totalHutang,
            'totalPiutang'     => $this->totalPiutang,
            'stokMinimum'      => $this->stokMinimum,
            'stokItems'        => $this->stokItems,
            'beliItems'        => $this->beliItems,
            'omzetItems'       => $this->omzetItems,
            'omzetTodayItems'  => $this->omzetTodayItems,
            'hutangItems'      => $this->hutangItems,
            'piutangItems'     => $this->piutangItems,
            'stokChartLabels'  => $this->stokChartLabels,
            'stokMasukSeries'  => $this->stokMasukSeries,
            'stokKeluarSeries' => $this->stokKeluarSeries,
        ];
    }
}
