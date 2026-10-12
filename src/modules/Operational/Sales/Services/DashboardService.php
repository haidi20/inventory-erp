<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Entities\DashboardDataEntity;
use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Operational\Sales\Repositories\DashboardRepository;

class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {}

    public function getDashboardData(UserEntity $user): DashboardDataEntity
    {
        $companyUuid = (string) (current_company_uuid() ?? '');
        $dailyRevenueStats = $this->repository->getDailyRevenueStats($companyUuid, 30);
        $monthlyVisitStats = $this->repository->getMonthlyVisitStats($companyUuid, 12);
        $monthlyRevenueDistribution = $this->repository->getMonthlyRevenueDistribution($companyUuid);

        return DashboardDataEntity::fromArray([
            'activeQueueCount' => $this->repository->getActiveQueueCount($companyUuid),
            'serviceRevenueToday' => $this->repository->getTodayServiceRevenue($companyUuid),
            'completedServiceCountToday' => $this->repository->getCompletedServiceCountToday($companyUuid),
            'posRevenueToday' => $this->repository->getTodayPosRevenue($companyUuid),
            'posItemCountToday' => $this->repository->getTodayPosItemCount($companyUuid),
            'dailyChartLabels' => $dailyRevenueStats['dailyChartLabels'],
            'dailyServiceRevenueSeries' => $dailyRevenueStats['dailyServiceRevenueSeries'],
            'dailyPosRevenueSeries' => $dailyRevenueStats['dailyPosRevenueSeries'],
            'monthlyChartLabels' => $monthlyVisitStats['monthlyChartLabels'],
            'monthlyVisitSeries' => $monthlyVisitStats['monthlyVisitSeries'],
            'serviceRevenueDistribution' => $monthlyRevenueDistribution['serviceRevenueDistribution'],
            'posRevenueDistribution' => $monthlyRevenueDistribution['posRevenueDistribution'],
            'latestRegistrations' => $this->repository->getLatestRegistrations($companyUuid, 8),
            'latestSales' => $this->repository->getLatestSales($companyUuid, 6),
            'patientCount' => $this->repository->getPatientCount($companyUuid),
            'quickMenus' => $this->buildQuickMenus($user)
        ]);
    }

    public function buildQuickMenus(UserEntity $user): array
    {
        // Internal helper to check permission on entity/fake user
        $can = fn($p) => method_exists($user, 'can') ? $user->can($p) : true; 

        $menus = [
            [
                'label' => 'Himbauan',
                'description' => 'Pengumuman dan informasi operasional.',
                'icon' => 'fa-solid fa-bullhorn',
                'url' => '/himbauan',
                'color' => 'indigo',
                'visible' => $can('himbauan.view'),
            ],
            [
                'label' => 'Layanan BPJS',
                'description' => 'Pendaftaran peserta, SEP, dan monitoring klaim.',
                'icon' => 'fa-solid fa-id-card',
                'url' => $this->firstAllowedUrl($user, [
                    'bpjs.pendaftaran.view' => '/bpjs/pendaftaran',
                    'bpjs.verifikasi.view' => '/bpjs/verifikasi',
                    'bpjs.rujukan.view' => '/bpjs/rujukan',
                    'bpjs.sep.view' => '/bpjs/sep',
                    'bpjs.monitoring_klaim.view' => '/bpjs/monitoring-klaim',
                    'settings.bpjs.manage' => '/bpjs/pengaturan',
                ]),
                'color' => 'emerald',
                'visible' => $this->hasAnyPermission($user, [
                    'bpjs.pendaftaran.view',
                    'bpjs.verifikasi.view',
                    'bpjs.rujukan.view',
                    'bpjs.sep.view',
                    'bpjs.monitoring_klaim.view',
                    'settings.bpjs.manage',
                ]),
            ],
            [
                'label' => 'Layanan SATUSEHAT',
                'description' => 'Sinkronisasi pasien, encounter, dan rekam medis nasional.',
                'icon' => 'fa-solid fa-heart-pulse',
                'url' => $this->firstAllowedUrl($user, [
                    'satusehat.sync.manage' => '/satusehat/sync',
                    'satusehat.encounter.view' => '/satusehat/encounter',
                    'satusehat.fhir.view' => '/satusehat/fhir',
                    'satusehat.config.manage' => '/satusehat/pengaturan',
                    'settings.satusehat.manage' => '/satusehat/pengaturan',
                ]),
                'color' => 'rose',
                'visible' => $this->hasAnyPermission($user, [
                    'satusehat.sync.manage',
                    'satusehat.encounter.view',
                    'satusehat.fhir.view',
                    'satusehat.config.manage',
                    'settings.satusehat.manage',
                ]),
            ],
            [
                'label' => 'Stok',
                'description' => 'Valuasi stok, barang dan jasa, satuan, saldo awal, dan gudang.',
                'icon' => 'fa-solid fa-boxes-stacked',
                'url' => $this->firstAllowedUrl($user, [
                    'stok.daftar_nilai.view' => '/stok/daftar-nilai',
                    'stok.barang_jasa.view' => '/stok/barang-dan-jasa',
                    'stok.formula_bom.view' => '/stok/formula-bom',
                    'stok.satuan.view' => '/stok/satuan',
                    'stok.saldo_awal.view' => '/stok/saldo-awal',
                    'stok.konversi_item.view' => '/stok/konversi-item',
                    'stok.gudang.view' => '/stok/gudang',
                ]),
                'color' => 'amber',
                'visible' => $this->hasAnyPermission($user, [
                    'stok.daftar_nilai.view',
                    'stok.barang_jasa.view',
                    'stok.formula_bom.view',
                    'stok.satuan.view',
                    'stok.saldo_awal.view',
                    'stok.konversi_item.view',
                    'stok.gudang.view',
                ]),
            ],
            [
                'label' => 'Transaksi',
                'description' => 'Pembelian, pemeriksaan, kasir, piutang, dan kas bank.',
                'icon' => 'fa-solid fa-receipt',
                'url' => $this->firstAllowedUrl($user, [
                    'transaksi.pembelian.view' => '/transaksi/pembelian',
                    'transaksi.hutang.view' => '/transaksi/hutang',
                    'transaksi.pemeriksaan.view' => '/transaksi/pemeriksaan',
                    'transaksi.tugas_dokter.view' => '/transaksi/tugas-dokter',
                    'transaksi.penjualan.view' => '/transaksi/penjualan',
                    'transaksi.piutang.view' => '/transaksi/piutang',
                    'transaksi.kas_bank.view' => '/transaksi/kas-bank',
                ]),
                'color' => 'blue',
                'visible' => $this->hasAnyPermission($user, [
                    'transaksi.pembelian.view',
                    'transaksi.hutang.view',
                    'transaksi.pemeriksaan.view',
                    'transaksi.tugas_dokter.view',
                    'transaksi.penjualan.view',
                    'transaksi.piutang.view',
                    'transaksi.kas_bank.view',
                ]),
            ],
            [
                'label' => 'Master Data',
                'description' => 'Pasien, obat, tindakan, supplier, dan pegawai.',
                'icon' => 'fa-solid fa-folder-open',
                'url' => $this->firstAllowedUrl($user, [
                    'master.produk.view' => '/master/products',
                    'master.paket.view' => '/master/packages',
                    'master.layanan.view' => '/master/services',
                    'master.supplier.view' => '/master/suppliers',
                    'master.pasien.view' => '/master/patients',
                    'master.pegawai.view' => '/master/employees',
                ]),
                'color' => 'slate',
                'visible' => $this->hasAnyPermission($user, [
                    'master.produk.view',
                    'master.paket.view',
                    'master.layanan.view',
                    'master.supplier.view',
                    'master.pasien.view',
                    'master.pegawai.view',
                ]),
            ],
            [
                'label' => 'Accounting',
                'description' => 'COA, jurnal umum, buku besar, neraca saldo, neraca, dan rugi laba.',
                'icon' => 'fa-solid fa-book',
                'url' => $this->firstAllowedUrl($user, [
                    'akuntansi.coa.view' => '/accounting/coa',
                    'akuntansi.jurnal.view' => '/accounting/jurnal',
                    'akuntansi.buku_besar.view' => '/accounting/bukubesar',
                    'akuntansi.neraca_saldo.view' => '/accounting/neraca-saldo',
                    'akuntansi.neraca.view' => '/accounting/neraca',
                    'akuntansi.laba_rugi.view' => '/accounting/laba-rugi',
                ]),
                'color' => 'cyan',
                'visible' => $this->hasAnyPermission($user, [
                    'akuntansi.coa.view',
                    'akuntansi.jurnal.view',
                    'akuntansi.buku_besar.view',
                    'akuntansi.neraca_saldo.view',
                    'akuntansi.neraca.view',
                    'akuntansi.laba_rugi.view',
                ]),
            ],
            [
                'label' => 'Analitik Klinik',
                'description' => 'Overview intelijen dan analisa profit klinik.',
                'icon' => 'fa-solid fa-chart-line',
                'url' => $this->firstAllowedUrl($user, [
                    'analitik.overview.view' => '/analytics/overview',
                    'analitik.profit_dokter.view' => '/analytics/profit-dokter',
                    'analitik.profit_tindakan.view' => '/analytics/profit-tindakan',
                    'analitik.profit_pasien.view' => '/analytics/profit-pasien',
                    'analitik.margin_produk.view' => '/analytics/margin-produk',
                    'analitik.tindakan_terlaris.view' => '/analytics/tindakan-terlaris',
                    'analitik.pasien_profitable.view' => '/analytics/pasien-profitable',
                ]),
                'color' => 'violet',
                'visible' => $this->hasAnyPermission($user, [
                    'analitik.overview.view',
                    'analitik.profit_dokter.view',
                    'analitik.profit_tindakan.view',
                    'analitik.profit_pasien.view',
                    'analitik.margin_produk.view',
                    'analitik.tindakan_terlaris.view',
                    'analitik.pasien_profitable.view',
                ]),
            ],
            [
                'label' => 'Management User & Keamanan',
                'description' => 'Kelola akun user dan role permission.',
                'icon' => 'fa-solid fa-users-gear',
                'url' => $this->firstAllowedUrl($user, [
                    'settings.user.view' => '/management/users',
                    'settings.role.view' => '/management/roles',
                ]),
                'color' => 'teal',
                'visible' => $this->hasAnyPermission($user, [
                    'settings.user.view',
                    'settings.role.view',
                ]),
            ],
            [
                'label' => 'Laporan',
                'description' => 'Arus kas, pemeriksaan, penjualan, dokter, dan biaya.',
                'icon' => 'fa-solid fa-file-lines',
                'url' => $this->firstAllowedUrl($user, [
                    'laporan.arus_kas.view' => '/laporan/arus-kas',
                    'laporan.pemeriksaan.view' => '/laporan/pemeriksaan',
                    'laporan.penjualan_pos.view' => '/laporan/penjualan-pos',
                    'laporan.pendapatan_dokter.view' => '/laporan/pendapatan-dokter',
                    'laporan.obat_vs_jasa.view' => '/laporan/obat-vs-jasa',
                    'laporan.biaya_operasional.view' => '/laporan/biaya-operasional',
                ]),
                'color' => 'orange',
                'visible' => $this->hasAnyPermission($user, [
                    'laporan.arus_kas.view',
                    'laporan.pemeriksaan.view',
                    'laporan.penjualan_pos.view',
                    'laporan.pendapatan_dokter.view',
                    'laporan.obat_vs_jasa.view',
                    'laporan.biaya_operasional.view',
                ]),
            ],
            [
                'label' => 'Pengaturan',
                'description' => 'Profil klinik, database management, dan administrasi sistem.',
                'icon' => 'fa-solid fa-gear',
                'url' => '/settings',
                'color' => 'sky',
                'visible' => $can('settings.view'),
            ],
        ];

        return array_values(array_filter($menus, static fn (array $menu): bool => $menu['visible'] && !empty($menu['url'])));
    }

    private function firstAllowedUrl(UserEntity $user, array $permissionMap): ?string
    {
        $can = fn($p) => method_exists($user, 'can') ? $user->can($p) : true; 
        foreach ($permissionMap as $permission => $url) {
            if ($can($permission)) {
                return $url;
            }
        }

        return null;
    }

    private function hasAnyPermission(UserEntity $user, array $permissions): bool
    {
        $can = fn($p) => method_exists($user, 'can') ? $user->can($p) : true; 
        foreach ($permissions as $permission) {
            if ($can($permission)) {
                return true;
            }
        }

        return false;
    }
}


