<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\CustomerSupplierFilterEntity;
use App\Modules\Operational\Master\Repositories\CustomerSupplierRepository;
use Illuminate\Support\Facades\Validator;

class CustomerSupplierService
{
    public function __construct(private CustomerSupplierRepository $customerSupplierRepository)
    {
    }

    public function customers(CustomerSupplierFilterEntity $filterEntity): array
    {
        $search = $this->validatedSearch($filterEntity);
        $customers = $this->customerSupplierRepository->getCustomers(CustomerSupplierFilterEntity::fromArray([
            'search' => $search,
        ]));

        return $this->buildPage(
            'customers',
            'Customer',
            'Direktori customer klinik yang saat ini dipetakan dari data pasien aktif.',
            $search,
            [
                ['label' => 'Total Customer', 'value' => $this->number($customers->count()), 'hint' => 'Pasien aktif yang dapat dilayani'],
                ['label' => 'Customer BPJS', 'value' => $this->number($customers->where('bpjs_status', 'Terdaftar')->count()), 'hint' => 'Memiliki nomor BPJS'],
                ['label' => 'Kontak Tersedia', 'value' => $this->number($customers->filter(fn (array $item): bool => $item['phone'] !== '-')->count()), 'hint' => 'Sudah memiliki nomor telepon'],
            ],
            ['No. RM', 'Nama Customer', 'Gender', 'Telepon', 'BPJS', 'Alamat'],
            $customers->map(fn (array $item): array => [
                $item['code'],
                $item['name'],
                $item['gender'],
                $item['phone'],
                $item['bpjs_status'],
                $item['address'],
            ])->all(),
            'Belum ada data customer yang bisa ditampilkan.',
            'Untuk pengelolaan detail customer, gunakan menu Data Pasien pada master data.'
        );
    }

    public function suppliers(CustomerSupplierFilterEntity $filterEntity): array
    {
        $search = $this->validatedSearch($filterEntity);
        $suppliers = $this->customerSupplierRepository->getSuppliers(CustomerSupplierFilterEntity::fromArray([
            'search' => $search,
        ]));

        return $this->buildPage(
            'suppliers',
            'Supplier',
            'Direktori supplier untuk pembelian obat, alkes, dan kebutuhan operasional.',
            $search,
            [
                ['label' => 'Total Supplier', 'value' => $this->number($suppliers->count()), 'hint' => 'Mitra supplier terdaftar'],
                ['label' => 'Supplier Aktif', 'value' => $this->number($suppliers->where('status', 'Aktif')->count()), 'hint' => 'Siap dipakai untuk transaksi'],
                ['label' => 'Termin Rata-rata', 'value' => $this->number((int) round($suppliers->avg('term_days') ?? 0)) . ' hari', 'hint' => 'Rata-rata termin pembayaran'],
            ],
            ['Nama Supplier', 'Telepon', 'Email', 'Termin', 'Status', 'Alamat'],
            $suppliers->map(fn (array $item): array => [
                $item['name'],
                $item['phone'],
                $item['email'],
                $item['term_days'] . ' hari',
                $item['status'],
                $item['address'],
            ])->all(),
            'Belum ada data supplier yang bisa ditampilkan.',
            'Untuk pengelolaan detail supplier, gunakan menu Data Supplier pada master data.'
        );
    }

    private function buildPage(
        string $currentTab,
        string $pageTitle,
        string $pageSubtitle,
        string $search,
        array $stats,
        array $columns,
        array $rows,
        string $emptyMessage,
        string $note
    ): array {
        return [
            'currentTab' => $currentTab,
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'search' => $search,
            'stats' => $stats,
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => $emptyMessage,
            'note' => $note,
            'tabs' => [
                ['key' => 'customers', 'label' => 'Customer', 'url' => '/customer-supplier/customers'],
                ['key' => 'suppliers', 'label' => 'Supplier', 'url' => '/customer-supplier/suppliers'],
            ],
        ];
    }

    private function validatedSearch(CustomerSupplierFilterEntity $filterEntity): string
    {
        $data = Validator::make([
            'search' => $filterEntity->search,
        ], [
            'search' => ['nullable', 'string', 'max:100'],
        ])->validate();

        return trim((string) ($data['search'] ?? ''));
    }

    private function number(float|int $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
