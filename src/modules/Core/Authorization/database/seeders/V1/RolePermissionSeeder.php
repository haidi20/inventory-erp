<?php



namespace Database\Seeders\V1;



use App\Modules\Core\Organization\Models\Branch;

use App\Modules\Operational\Master\Models\Employee;

use App\Modules\Core\Authorization\Models\Permission;

use App\Modules\Core\Authorization\Models\Role;

use App\Modules\Core\Identity\Models\User;

use App\Modules\Operational\Master\Models\Supplier;

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Str;



/**

 * RolePermissionSeeder

 * 

 * Seeder tunggal untuk mengelola seluruh Izin (Permissions) dan Peran (Roles).

 * Mengonsolidasi DokterDanRoleSeeder, RbacSeeder, dan UserManagementPermissionsSeeder.

 */

class RolePermissionSeeder extends Seeder

{

    public function run(): void

    {

        // Reset cache Spatie Permission untuk memastikan sinkronisasi data segar dari database

        try {

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        } catch (\Exception $e) {

            // Abaikan error jika tabel belum ada atau koneksi bermasalah saat bootstrap

        }



        $this->command->info('Memulai konsolidasi Role dan Permission...');

        

        $this->seedDokter();

        $this->seedRolesAndPermissions();

        $this->seedAdditionalMasterData();

        

        $this->command->info('RolePermissionSeeder selesai dijalankan.');

    }



    /**

     * Seed data dokter awal

     */

    private function seedDokter(): void

    {

        $dokterList = [

            ['name' => 'dr. Ahmad Fauzi, Sp.PD',       'specialization' => 'Penyakit Dalam',        'fee' => 20000],

            ['name' => 'dr. Siti Rahayu, Sp.OG',       'specialization' => 'Kandungan & Kebidanan', 'fee' => 25000],

            ['name' => 'dr. Budi Santoso, Sp.A',       'specialization' => 'Anak',                  'fee' => 20000],

            ['name' => 'dr. Dewi Kurniawati, Sp.JP',   'specialization' => 'Jantung & Pembuluh Darah','fee'=> 30000],

            ['name' => 'dr. Hendra Wijaya, Sp.B',      'specialization' => 'Bedah Umum',             'fee' => 25000],

            ['name' => 'dr. Lestari Anggraini, Sp.S',  'specialization' => 'Saraf',                  'fee' => 25000],

            ['name' => 'dr. Muhamad Rizky, Sp.M',      'specialization' => 'Mata',                   'fee' => 20000],

            ['name' => 'dr. Novita Sari, Sp.THT',      'specialization' => 'THT',                    'fee' => 20000],

            ['name' => 'dr. Prasetyo Adi, Sp.KK',      'specialization' => 'Kulit & Kelamin',        'fee' => 20000],

            ['name' => 'dr. Rini Hartati, Sp.PK',      'specialization' => 'Patologi Klinik',        'fee' => 15000],

            ['name' => 'dr. Suparman, Sp.Rad',          'specialization' => 'Radiologi',              'fee' => 15000],

            ['name' => 'dr. Tuti Indrawati, Sp.RM',    'specialization' => 'Rehabilitasi Medik',     'fee' => 20000],

        ];



        $branch = $this->resolveDemoBranch();

        

        $count = 0;

        foreach ($dokterList as $d) {

            $exists = Employee::where('name', $d['name'])->exists();

            if (!$exists) {

                Employee::create([

                    'uuid'           => (string) Str::uuid(),

                    'branch_uuid'    => $branch->uuid,

                    'company_uuid'   => $branch->company_uuid,

                    'name'           => $d['name'],

                    'role'           => 'dokter',

                    'specialization' => $d['specialization'],

                    'fee_default'    => $d['fee'],

                    'is_active'      => true,

                ]);

                $count++;

            }

        }

        $this->command->info("Dokter ditambahkan: $count");

    }



    /**

     * Seed Roles dan Permissions serta mapping-nya

     */

    private function seedRolesAndPermissions(): void

    {

        $permissions = [

            // -- Dashboard & Utama --------------------------------
            'dashboard.view'                    => 'Lihat Dashboard',

            'himbauan.view'                     => 'Lihat Himbauan Operasional',

            

            // Inventory

            'inventory.view'                    => 'Lihat Menu Inventory',

            'inventory.stock_transfer.view'     => 'Lihat Pindah Antar Gudang',

            'inventory.stock_adjustment.view'   => 'Lihat Penyesuaian Stok',

            'inventory.stock_balance.view'      => 'Lihat Saldo Stok',

            

            // -- Menu Groups ------------------------------------
            'master.view'                       => 'Lihat Menu Master Data',

            'transaksi.view'                    => 'Lihat Menu Transaksi',

            'stok.view'                         => 'Lihat Menu Stok',

            'akuntansi.view'                    => 'Lihat Menu Accounting',

            'analitik.view'                     => 'Lihat Menu Analitik Klinik',

            'settings.view'                     => 'Lihat Menu Management User & Keamanan',

            'laporan.view'                      => 'Lihat Menu Laporan',

            'pricing.view'                      => 'Lihat Menu Daftar Harga',

            'bpjs.view'                         => 'Lihat Menu BPJS',

            'satusehat.view'                    => 'Lihat Menu SatuSehat',



            // -- Master Data -------------------------------------
            'master.pasien.view'                => 'Lihat Data Pasien',

            'master.pasien.manage'              => 'Kelola Data Pasien (CRUD)',

            'master.supplier.view'              => 'Lihat Data Supplier',

            'master.supplier.manage'            => 'Kelola Data Supplier (CRUD)',

            'master.produk.view'                => 'Lihat Data Produk/Obat',

            'master.produk.manage'              => 'Kelola Data Produk/Obat (CRUD)',

            'master.layanan.view'               => 'Lihat Data Layanan/Tindakan',

            'master.layanan.manage'             => 'Kelola Data Layanan/Tindakan (CRUD)',

            'master.paket.view'                 => 'Lihat Data Paket Layanan',

            'master.paket.manage'               => 'Kelola Data Paket Layanan (CRUD)',

            'master.pegawai.view'               => 'Lihat Data Pegawai & Dokter',

            'master.pegawai.manage'             => 'Kelola Data Pegawai & Dokter (CRUD)',

            'master.lookup.view'                => 'Lihat Daftar Nilai Master',

            'master.lookup.manage'              => 'Kelola Daftar Nilai Master (CRUD)',

            'master.manage'                     => 'Kelola Master Data Umum (Satuan, dll)',



            // -- Stok --------------------------------------------
            'stok.daftar_nilai.view'            => 'Lihat Daftar Nilai Stok',

            'stok.barang_jasa.view'             => 'Lihat Barang & Jasa',

            'stok.formula_bom.view'             => 'Lihat Formula (BOM)',

            'stok.satuan.view'                  => 'Lihat Satuan Stok',

            'stok.saldo_awal.view'              => 'Lihat Saldo Awal Stok',

            'stok.konversi_item.view'           => 'Lihat Konversi Item',

            'stok.gudang.view'                  => 'Lihat Data Gudang',



            // -- Operasional & Klinis ----------------------------
            'antrian.view'                      => 'Lihat Antrian Pasien',

            'antrian.manage'                    => 'Kelola Antrian & Pendaftaran Pasien',

            'rekam_medis.view'                  => 'Lihat Rekam Medis Pasien',

            'rekam_medis.manage'                => 'Isi & Kelola Rekam Medis',

            'tindakan.view'                     => 'Lihat Data Tindakan Medis',

            'tindakan.manage'                   => 'Input & Kelola Tindakan Medis',

            'farmasi.view'                      => 'Lihat Stok & Data Farmasi',

            'farmasi.manage'                    => 'Kelola Stok & Dispensing Obat',

            'lab.view'                          => 'Lihat Hasil & Data Laboratorium',

            'lab.manage'                        => 'Input & Kelola Data Laboratorium',

            'operational.manage'                => 'Kelola Operasional Umum',



            // -- Transaksi ---------------------------------------
            'transaksi.pembelian.view'          => 'Lihat Transaksi Pembelian',

            'transaksi.pembelian.manage'        => 'Kelola Transaksi Pembelian Supplier',

            'transaksi.hutang.view'             => 'Lihat Hutang Supplier',

            'transaksi.pemeriksaan.view'        => 'Lihat Pelayanan Poli/Dokter',

            'transaksi.tugas_dokter.view'       => 'Lihat Monitoring Tugas Dokter',

            'transaksi.permintaan_penunjang.view' => 'Lihat Permintaan Lab/Rad',

            'transaksi.hasil_penunjang.view'    => 'Lihat Hasil Penunjang RME',

            'transaksi.penjualan.view'          => 'Lihat Penjualan POS',

            'transaksi.penjualan.manage'        => 'Input & Kelola Penjualan POS (Kasir)',

            'transaksi.piutang.view'            => 'Lihat Piutang Pasien',

            'transaksi.piutang.manage'          => 'Kelola Piutang & Pembayaran Pasien',

            'finance.manage'                    => 'Kelola Keuangan Umum',



            // -- Akuntansi ---------------------------------------
            'akuntansi.coa.view'                => 'Lihat Chart of Accounts',

            'akuntansi.jurnal.view'             => 'Lihat Jurnal Umum',

            'akuntansi.buku_besar.view'         => 'Lihat Buku Besar',

            'akuntansi.neraca_saldo.view'       => 'Lihat Neraca Saldo',

            'akuntansi.neraca.view'             => 'Lihat Neraca',

            'akuntansi.laba_rugi.view'          => 'Lihat Rugi Laba',

            'akuntansi.periode.manage'          => 'Kelola Periode Akuntansi (Buka/Tutup)',

            'akuntansi.akun_induk.manage'       => 'Kelola Akun Induk (CRUD)',

            'akuntansi.sub_akun.manage'         => 'Kelola Sub Akun (CRUD)',

            'akuntansi.saldo_awal.manage'       => 'Input & Kelola Saldo Awal',

            'akuntansi.transaksi.manage'        => 'Input & Hapus Jurnal Manual',



            // -- Analitik & Laporan ------------------------------
            'analitik.overview.view'            => 'Lihat Overview Intelijen',

            'analitik.profit_dokter.view'       => 'Lihat Profit per Dokter',

            'analitik.profit_tindakan.view'     => 'Lihat Profit per Tindakan',

            'analitik.profit_pasien.view'       => 'Lihat Profit per Pasien',

            'analitik.margin_produk.view'       => 'Lihat Margin Obat/Alkes',

            'analitik.tindakan_terlaris.view'   => 'Lihat Tindakan Terlaris',

            'analitik.pasien_profitable.view'   => 'Lihat Pasien Profitable',

            

            'laporan.arus_kas.view'             => 'Lihat Laporan Arus Kas',

            'laporan.pemeriksaan.view'          => 'Lihat Laporan Pemeriksaan',

            'laporan.penjualan_pos.view'        => 'Lihat Laporan Penjualan POS',

            'laporan.pendapatan_dokter.view'    => 'Lihat Laporan Pendapatan Dokter',

            'laporan.obat_vs_jasa.view'         => 'Lihat Laporan Obat vs Jasa',

            'laporan.biaya_operasional.view'    => 'Lihat Laporan Biaya Operasional',



            // -- Integrasi & Settings ----------------------------
            'satusehat.sync.manage'             => 'SatuSehat: Sinkronisasi Data',

            'satusehat.encounter.view'          => 'SatuSehat: Lihat Data Kunjungan',

            'satusehat.fhir.view'               => 'SatuSehat: Lihat Rekam Medis FHIR',

            'satusehat.config.manage'           => 'SatuSehat: Konfigurasi Integrasi',

            'bpjs.pendaftaran.view'          => 'Lihat Pendaftaran BPJS',

            'bpjs.verifikasi.view'           => 'Lihat Verifikasi BPJS',

            'transaksi.pemeriksaan.manage'   => 'Kelola Pemeriksaan/Tindakan',

            'integration.manage'                => 'Kelola Integrasi Umum',

            'settings.user.view'                => 'Lihat Data Pengguna',

            'settings.user.manage'              => 'Kelola User (CRUD & Reset Password)',

            'settings.role.view'                => 'Lihat Role Permission',

            'settings.role.manage'              => 'Kelola Role Permission (CRUD)',

            'settings.manage'                   => 'Kelola Pengaturan Umum',

            // -- Monitoring & Observability -----------------------
            'system.monitor.view'               => 'Lihat Dashboard Monitoring (Pulse)',

        ];



        // Bersihkan permissions lama (termasuk yang soft-deleted) untuk menghindari konflik integritas data

        Permission::withTrashed()->whereIn('name', array_keys($permissions))->forceDelete();



        // Simpan / update semua permissions

        foreach ($permissions as $name => $label) {

            Permission::updateOrCreate(

                ['name' => $name, 'guard_name' => 'web'],

                ['label' => $label]

            );

        }



        // Definisi Roles

        $allPerms = array_keys($permissions);

        $roles = [

            'admin' => ['label' => 'Administrator', 'permissions' => $allPerms],

            'owner' => ['label' => 'Owner / Direktur', 'permissions' => $allPerms],

            'superadmin' => ['label' => 'Super Administrator', 'permissions' => $allPerms],

            'dokter' => [

                'label' => 'Dokter',

                'permissions' => ['dashboard.view', 'master.pasien.view', 'rekam_medis.manage', 'tindakan.manage', 'transaksi.pemeriksaan.view', 'transaksi.tugas_dokter.view'],

            ],

            'kasir' => [

                'label' => 'Kasir',

                'permissions' => ['dashboard.view', 'transaksi.penjualan.manage', 'transaksi.penjualan.view', 'transaksi.piutang.manage', 'master.lookup.view', 'master.pasien.view'],

            ],

            'perawat' => [

                'label' => 'Perawat',

                'permissions' => ['dashboard.view', 'transaksi.pemeriksaan.view', 'transaksi.pemeriksaan.manage', 'master.pasien.view', 'master.lookup.view'],

            ],

            'pendaftaran' => [

                'label' => 'Petugas Pendaftaran',

                'permissions' => ['dashboard.view', 'master.pasien.view', 'master.pasien.manage', 'master.lookup.view', 'bpjs.pendaftaran.view', 'bpjs.verifikasi.view'],

            ],

            'apoteker' => [

                'label' => 'Apoteker',

                'permissions' => ['dashboard.view', 'stok.barang_jasa.view', 'stok.daftar_nilai.view', 'master.lookup.view', 'master.lookup.manage', 'inventory.view', 'inventory.stock_balance.view', 'inventory.stock_adjustment.view', 'inventory.stock_transfer.view'],

            ],

            'lab' => [

                'label' => 'Petugas Lab',

                'permissions' => ['dashboard.view', 'master.pasien.view', 'master.lookup.view'],

            ],

            'manajer_keuangan' => [

                'label' => 'Manajer Keuangan',

                'permissions' => ['dashboard.view', 'laporan.arus_kas.view', 'laporan.pemeriksaan.view', 'laporan.penjualan_pos.view', 'laporan.pendapatan_dokter.view', 'laporan.obat_vs_jasa.view', 'laporan.biaya_operasional.view', 'akuntansi.coa.view', 'akuntansi.jurnal.view', 'akuntansi.buku_besar.view', 'analitik.overview.view', 'master.lookup.view'],

            ],

        ];

        foreach ($roles as $name => $data) {

            $role = Role::updateOrCreate(

                ['name' => $name, 'guard_name' => 'web'],

                ['label' => $data['label']]

            );

            $role->syncPermissions($data['permissions']);

        }

    }



    /**

     * Seed Master Data tambahan

     */

    private function seedAdditionalMasterData(): void

    {

        $branch = $this->resolveDemoBranch();

        if (!$branch) return;



        // Seed Kasir (Employee) - Dibutuhkan oleh simulasi jurnal

        // Kolom legacy branch_uuid/company_uuid sudah di-drop dari tabel users;
        // pencarian user cukup by email (user dihubungkan ke branch via pivot user_branches).
        $kasirUser = User::query()

            ->where('email', 'kasir@klinik.local')

            ->first();

        Employee::updateOrCreate(

            ['branch_uuid' => $branch->uuid, 'role' => 'kasir'],

            [

                'uuid' => (string) Str::uuid(),

                'company_uuid' => $branch->company_uuid,

                'name' => 'Kasir Utama',

                'user_uuid' => $kasirUser?->uuid,

                'is_active' => true,

                'fee_default' => 0

            ]

        );



        // Seed Supplier

        Supplier::updateOrCreate(

            ['branch_uuid' => $branch->uuid, 'name' => 'PT. Kimia Farma'],

            [

                'uuid' => (string) Str::uuid(),

                'company_uuid' => $branch->company_uuid,

                'address' => 'Jakarta, Indonesia',

                'phone' => '021-123456',

                'term_days' => 30,

                'is_active' => true

            ]

        );

    }



    private function resolveDemoBranch(): Branch

    {

        $this->call(RuntimeCompanySeeder::class);



        $sandboxBranch = Branch::query()

            ->where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)

            ->firstOrFail();



        return Branch::updateOrCreate(

            ['short_name' => 'KDM'],

            [

                'name' => 'Klinik Demo',

                'description' => 'Cabang demo yang mengikuti scope company sandbox/testing.',

                'company_uuid' => $sandboxBranch->company_uuid,

                'is_active' => true,

            ]

        );

    }

}



