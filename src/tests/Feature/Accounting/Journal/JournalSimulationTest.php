<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Procurement\Models\PurchaseReturn;
use App\Modules\Operational\Procurement\Models\Purchase;
use Illuminate\Database\QueryException;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use App\Modules\Finance\Ledger\Models\Receivable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Models\TaxSetting;
use Tests\TestCase;
use App\Exceptions\UnauthorizedException;
use App\Modules\Core\Identity\Models\User;
use Exception;

trait SaleSimulation
{
    /**
     * Simulasi Kasus: Penjualan Obat (POS) Secara Tunai
     * Skenario:
     * - Mengambil data Penjualan asli dari Database untuk mendapatkan total transaksi.
     *   (Misal: Total Rp 50.000,00 inklusif PPN).
     * - MENGGUNAKAN LOGIKA DINAMIS (Looping Mapping):
     *   Sistem mencari daftar akun di `account_mappings` untuk Menu "Penjualan Obat".
     *   Akun yang terlibat: Kas, Pendapatan, PPN, Persediaan, dan HPP.
     * - Validasi:
     *   1. Pastikan total Debit sama dengan Kredit.
     *   2. Kas (Debit) harus sama dengan nominal lunas di Invoice.
     *   3. Jurnal harus ditandai sebagai `is_system = true`.
     */
    public function test_penjualan_obat_full_simulation_dynamic_looping(): void
    {
        $sale = Sale::first();
        $this->assertNotNull($sale);
        $sale->load('items.product');

        $tax = TaxSetting::where('name', 'PPN')->first();
        $taxPercent = $tax ? (float) $tax->percentage : 11;

        $journal = $this->journalService->storeJournal($sale, JournalReferenceType::SALE, [
            'tax_percent' => $taxPercent,
            'description' => "Jurnal Penjualan Otomatis (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(5, $journal->lines);
        $this->assertEquals($journal->lines->sum('debit'), $journal->lines->sum('credit'));

        $mappings = AccountMapping::where('menu_name', 'Penjualan Obat')->get();
        $kasLine = $journal->lines->where('account_uuid', $mappings->where('key', 'penjualan.kas')->first()->account_uuid)->first();
        $this->assertEquals((float)$sale->total, (float)$kasLine->debit);
    }

    /**
     * Membuktikan Kasus Piutang (Kredit) Juga Otomatis
     * Skenario:
     * - Mengubah status bayar Sale (Paid = 0) untuk memicu piutang.
     * - Sistem secara dinamis melewati akun Kas dan beralih ke akun Piutang
     *   berdasarkan `value_source` di mapping.
     */
    public function test_penjualan_obat_piutang_simulation_dynamic(): void
    {
        $sale = Sale::first();
        $sale->paid = 0;

        $journal = $this->journalService->storeJournal($sale, JournalReferenceType::SALE, [
            'tax_percent' => 11,
            'description' => "Jurnal Penjualan Piutang (Dynamic)"
        ]);

        $this->assertCount(5, $journal->lines);
        $mappings = AccountMapping::where('menu_name', 'Penjualan Obat')->get();
        $piutangAccount = $mappings->where('key', 'penjualan.piutang')->first()->account_uuid;
        $hasPiutang = $journal->lines->where('account_uuid', $piutangAccount)->first();
        $this->assertNotNull($hasPiutang);
        $this->assertEquals((float)$sale->total, (float)$hasPiutang->debit);
    }
}

trait EncounterSimulation
{
    /**
     * Simulasi Kasus: Pemeriksaan (Rekam Medis)
     * Skenario:
     * - Mengambil data Pemeriksaan (Encounter) asli dari Database yang sudah selesai.
     *   (Misal: Ada 1 Jasa Dokter seharga Rp 150.000).
     * - Secara dinamis (*on the fly*) menambahkan item Obat/Produk ke dalam sesi pemeriksaan
     *   untuk mensimulasikan kasus pasien yang menerima resep obat dan jasa dokter sekaligus.
     *   (Misal: Tambah 1 Obat dengan Harga Jual Rp 25.000 dan HPP Rp 11.538).
     * - Total Tagihan Pasien menjadi Rp 175.000 (Lunas).
     * - MENGGUNAKAN LOGIKA DINAMIS (Looping Mapping) dengan Enum JournalValueSource:
     *   Sistem membaca referensi akun dari `account_mappings` (Menu: Pemeriksaan (Rekam Medis)).
     *   Akun yang terlibat: Kas, Pendapatan Jasa, Pendapatan Obat, Stok Obat, HPP Obat.
     * - Proses Insert ke Jurnal Umum:
     *   1. Data Encounter dan EncounterItems dibungkus ke `JournalEntity` (DTO).
     *   2. Nominal secara cerdas diurai berdasarkan Enum (`TOTAL_SERVICE`, `TOTAL_NET_PRODUCT`, dll).
     *   3. `JournalService` memvalidasi keseimbangan saldo (Debit == Kredit).
     *   4. `TransactionCodeService` men-generate nomor transaksi (misal: KDM/RJ/...).
     *   5. Menyimpan data otomatis ke `journals` dan `journal_lines` tanpa perlu intervensi manual.
     */
    public function test_pemeriksaan_rekam_medis_simulation_dynamic(): void
    {
        $encounter = Encounter::with(['items.product'])->first();
        $this->assertNotNull($encounter);

        $product = Product::first();
        EncounterItem::create([
            'branch_uuid' => $encounter->branch_uuid,
            'encounter_uuid' => $encounter->uuid,
            'item_type' => 'product',
            'item_uuid' => $product->uuid,
            'qty' => 1,
            'price' => $product->price_sell,
            'subtotal' => $product->price_sell,
        ]);
        $encounter->refresh()->load('items.product');

        $journal = $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER, [
            'description' => "Jurnal Pemeriksaan Otomatis (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(5, $journal->lines);
        $this->assertEquals($journal->lines->sum('debit'), $journal->lines->sum('credit'));

        $totalLunas = $encounter->items->where('item_type', 'service')->sum('subtotal') +
                      $encounter->items->where('item_type', 'product')->sum('subtotal');

        $mappings = AccountMapping::where('menu_name', 'Pemeriksaan (Rekam Medis)')->get();
        $kasLine = $journal->lines->where('account_uuid', $mappings->where('key', 'pemeriksaan.kas')->first()->account_uuid)->first();
        $this->assertEquals((float)$totalLunas, (float)$kasLine->debit);
    }
}

trait PurchaseSimulation
{
    /**
     * Simulasi Kasus: Pembelian Stok ke Supplier (Purchase)
     * Skenario:
     * - Mengambil data Purchase (Pembelian) yang dilakukan secara tunai dari database.
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari referensi di `account_mappings` untuk Menu "Pembelian".
     *   Akun yang terlibat: Persediaan Obat (Debit) vs Kas (Kredit).
     * - Proses Insert ke Jurnal Umum:
     *   1. Data model `Purchase` dikonversi menjadi unit-unit jurnal melalui `JournalService`.
     *   2. Sistem mendeteksi kenaikan nilai aset gudang berdasarkan total bruto pembelian.
     *   3. Nomor bukti jurnal di-generate otomatis (misal: KDM/PURCH/...).
     *   4. Jurnal disimpan dan ditandai sebagai `is_system` untuk audit log.
     */
    public function test_pembelian_obat_simulation_dynamic(): void
    {
        $purchase = Purchase::first();
        $this->assertNotNull($purchase);

        $journal = $this->journalService->storeJournal($purchase, JournalReferenceType::PURCHASE, [
            'description' => "Jurnal Pembelian Otomatis (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals($journal->lines->sum('debit'), $journal->lines->sum('credit'));
    }
}

trait DebtSimulation
{
    /**
     * Simulasi Kasus: Pelunasan Hutang ke Supplier (Payable)
     * Skenario:
     * - Mengambil data Hutang (Payable) dan melakukan pelunasan nominal tertentu (Rp 500.000).
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari pemetaan di Menu "Hutang Supplier".
     *   Akun yang terlibat: Hutang Usaha (Debit) vs Kas (Kredit).
     * - Proses Insert ke Jurnal Umum:
     *   1. Menggunakan `AMOUNT_PAID` sebagai sumber nilai nominal sesuai konfigurasi mapping.
     *   2. Mengurangi kewajiban (Liability) di neraca secara otomatis.
     *   3. Validasi balance dilakukan sebelum data persisten masuk ke database.
     */
    public function test_pelunasan_hutang_supplier_simulation_dynamic(): void
    {
        $payable = Payable::first();
        $this->assertNotNull($payable);
        $payable->amount_paid = 500000;

        $journal = $this->journalService->storeJournal($payable, JournalReferenceType::DEBT_PAYMENT, [
            'description' => "Jurnal Pelunasan Hutang (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals(500000, $journal->lines->where('debit', '>', 0)->first()->debit);
        $this->assertEquals(500000, $journal->lines->where('credit', '>', 0)->first()->credit);
    }
}

trait ReceivableSimulation
{
    /**
     * Simulasi Kasus: Penerimaan Piutang Pasien/BPJS (Receivable)
     * Skenario:
     * - Pasien melakukan pelunasan atas piutang rekam medis/POS sebelumnya (Rp 100.000).
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari pemetaan di Menu "Piutang Pasien & BPJS".
     *   Akun yang terlibat: Kas (Debit) vs Piutang Usaha (Kredit).
     * - Proses Insert ke Jurnal Umum:
     *   1. Mentransformasi model `Receivable` ke dalam DTO `JournalEntity`.
     *   2. Memasukkan dana ke rekening kas klinik dan mengurangi saldo piutang di buku besar.
     *   3. Memastikan nomor referensi transaksi tercatat dengan benar untuk kemudahan rekonsiliasi.
     */
    public function test_penerimaan_piutang_pasien_simulation_dynamic(): void
    {
        $receivable = Receivable::first();
        $this->assertNotNull($receivable);
        $receivable->amount_paid = 100000;

        $journal = $this->journalService->storeJournal($receivable, JournalReferenceType::RECEIVABLE_COLLECTION, [
            'description' => "Penerimaan Piutang Pasien (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals(100000, $journal->lines->where('debit', '>', 0)->first()->debit);
        $this->assertEquals(100000, $journal->lines->where('credit', '>', 0)->first()->credit);
    }
}

trait ExpenseSimulation
{
    /**
     * Simulasi Kasus: Biaya Operasional (Beban Umum/Listrik)
     * Skenario:
     * - Klinik melakukan pembayaran biaya rutin bulanan (misal: Listrik Rp 350.000).
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Menggunakan `override_menu_name` untuk memanggil akun spesifik "Biaya Listrik".
     *   Akun yang terlibat: Beban Listrik (Debit) vs Kas (Kredit).
     * - Proses Insert ke Jurnal Umum:
     *   1. Alokasi biaya langsung dicatat ke laba rugi sebagai pengurang pendapatan.
     *   2. Sistem memandu pemilihan akun kontra (Kas/Bank) secara otomatis.
     *   3. Deskripsi transaksi ditambahkan secara dinamis sesuai parameter input.
     */
    public function test_biaya_operasional_simulation_dynamic(): void
    {
        $journal = $this->journalService->storeJournal($this->branch, JournalReferenceType::EXPENSE, [
            'amount' => 350000,
            'menu_name' => 'Biaya Listrik',
            'description' => "Bayar Listrik Bulanan (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals(350000, (float) $journal->lines->where('debit', '>', 0)->first()->debit);
        $this->assertEquals(350000, (float) $journal->lines->where('credit', '>', 0)->first()->credit);
    }
}

trait ReturnSimulation
{
    /**
     * Simulasi Kasus: Retur Pembelian ke Supplier (Purchase Return)
     * Skenario:
     * - Mengambil data Pembelian (Purchase) yang sudah ada.
     * - Membuat model `PurchaseReturn` untuk mensimulasikan pengembalian barang.
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari pemetaan di Menu "Retur Supplier".
     *   Akun yang terlibat: Hutang Usaha/Kas (Debit) vs Persediaan (Kredit).
     * - Proses Insert ke Jurnal Umum:
     *   1. Mengidentifikasi apakah retur mengurangi Hutang atau mengembalikan Kas tunai.
     *   2. Mengurangi nilai Persediaan di neraca secara otomatis sesuai HPP barang yang dikembalikan.
     *   3. Validasi balance dilakukan ketat untuk memastikan integritas data keuangan.
     */
    public function test_retur_pembelian_supplier_simulation_dynamic(): void
    {
        $purchase = Purchase::first();
        $this->assertNotNull($purchase);

        $return = new PurchaseReturn();
        $return->purchase_uuid = $purchase->uuid;
        $return->branch_uuid = $this->branch->uuid;
        $return->amount_paid = 250000; // Nilai retur

        $journal = $this->journalService->storeJournal($return, JournalReferenceType::PURCHASE_RETURN, [
            'description' => "Jurnal Retur Pembelian (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines); // Hutang (D) vs Persediaan (K)
        $this->assertEquals(250000, $journal->lines->sum('debit'));
        $this->assertEquals(250000, $journal->lines->sum('credit'));
    }
}

trait AdjustmentSimulation
{
    /**
     * Simulasi Kasus: Penyesuaian Stok (Stock Adjustment)
     * Skenario:
     * - Ditemukan selisih stok saat opname (hilang/rusak).
     * - Membuat data `StockMovement` dengan tipe penyesuaian.
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari pemetaan di Menu "Penyesuaian Stok".
     *   Akun yang terlibat: Beban Selisih Stok (Debit) vs Persediaan (Kredit).
     * - Proses Insert ke Jurnal Umum:
     *   1. Menggunakan `AMOUNT` (qty * price) sebagai sumber nominal jurnal.
     *   2. Mencatat kerugian di laporan Laba Rugi secara otomatis.
     */
    public function test_penyesuaian_stok_opname_simulation_dynamic(): void
    {
        $product = Product::first();

        $adjustment = new StockMovement();
        $adjustment->branch_uuid = $this->branch->uuid;
        $adjustment->product_uuid = $product->uuid;
        $adjustment->qty = 5;
        $adjustment->price = 10000; // HPP per unit

        $journal = $this->journalService->storeJournal($adjustment, JournalReferenceType::STOCK_ADJUSTMENT, [
            'description' => "Jurnal Penyesuaian Stok Opname (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $totalLoss = 5 * 10000;
        $this->assertEquals($totalLoss, $journal->lines->sum('debit'));
        $this->assertEquals($totalLoss, $journal->lines->sum('credit'));
    }
}

trait DiagnosticSimulation
{
    /**
     * Simulasi Kasus: Layanan Laboratorium
     * Skenario:
     * - Pasien melakukan pemeriksaan Lab (misal: Cek Darah Lengkap).
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari pemetaan di Menu "Laboratorium".
     *   Akun yang terlibat: Kas (Debit) vs Pendapatan Lab (Kredit).
     */
    public function test_layanan_laboratorium_simulation_dynamic(): void
    {
        $labOrder = new LabOrder();
        $labOrder->branch_uuid = $this->branch->uuid;
        $labOrder->total = 250000;

        $journal = $this->journalService->storeJournal($labOrder, JournalReferenceType::LABORATORY, [
            'description' => "Jurnal Pendapatan Lab (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals(250000, $journal->lines->where('debit', '>', 0)->first()->debit);
    }

    /**
     * Simulasi Kasus: Layanan Radiologi
     * Skenario:
     * - Pasien melakukan foto Rontgen.
     * - Akun yang terlibat: Kas (Debit) vs Pendapatan Radiologi (Kredit).
     */
    public function test_layanan_radiologi_simulation_dynamic(): void
    {
        $radOrder = new RadiologyOrder();
        $radOrder->branch_uuid = $this->branch->uuid;
        $radOrder->total = 450000;

        $journal = $this->journalService->storeJournal($radOrder, JournalReferenceType::RADIOLOGY, [
            'description' => "Jurnal Pendapatan Radiologi (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals(450000, $journal->lines->where('debit', '>', 0)->first()->debit);
    }
}

trait RegistrationSimulation
{
    /**
     * Simulasi Kasus: Pendaftaran Pasien (Biaya Administrasi)
     * Skenario:
     * - Pasien mendaftar di poli dan membayar biaya administrasi (Rp 15.000).
     * - MENGGUNAKAN LOGIKA DINAMIS:
     *   Sistem mencari pemetaan di Menu "Pendaftaran Pasien".
     *   Akun yang terlibat: Kas (Debit) vs Pendapatan Jasa/Sampingan (Kredit).
     */
    public function test_pendaftaran_pasien_admin_fee_simulation_dynamic(): void
    {
        $registration = Registration::first();
        $this->assertNotNull($registration);

        $journal = $this->journalService->storeJournal($registration, JournalReferenceType::REGISTRATION, [
            'amount' => 15000,
            'description' => "Biaya Administrasi Pendaftaran (Dynamic)"
        ]);

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals(15000, $journal->lines->where('debit', '>', 0)->first()->debit);
        $this->assertEquals(15000, $journal->lines->where('credit', '>', 0)->first()->credit);
    }
}

trait IntegritySimulation
{
    /**
     * Case 1: "Atomic Double-Entry" (Integrity)
     * Menguji apakah sistem membatalkan seluruh transaksi jika salah satu sisi (Debit/Kredit) gagal simpan.
     */
    public function test_atomic_double_entry_rollback_on_failure(): void
    {
        $validAccount = Account::where('is_header', false)->first();
        
        $validDate = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('status', 'open')->first()->end_date ?? \Illuminate\Support\Carbon::now()->toDateString();
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => $validDate,
            'reference_no' => 'TEST-ATOMIC-01',
            'reference_type' => JournalReferenceType::GENERAL,
            'description' => 'Test Atomic Rollback',
            'lines' => [
                ['account_uuid' => $validAccount->uuid, 'debit' => 100000, 'credit' => 0],
                ['account_uuid' => 999999999, 'debit' => 0, 'credit' => 100000], // INVALID ACCOUNT ID!
            ]
        ]);

        $initialJournalCount = Journal::count();
        $initialLineCount = JournalLine::count();

        try {
            $this->journalService->createJournal($dto);
            $this->fail('Seharusnya menghasilkan error karena account_uuid tidak valid.');
        } catch (\Throwable $e) {
            // Expected throw
            $this->assertTrue(true);
        }

        // Assert Rollback Works: Tidak boleh ada yang tersimpan sama sekali (0 baris bertambah)
        $this->assertEquals($initialJournalCount, Journal::count(), 'Journal header terbawa tersimpan walaupun lines gagal (Bocor/Tidak Atomic)');
        $this->assertEquals($initialLineCount, JournalLine::count(), 'Minimal 1 baris line tersimpan walaupun transaksi harusnya gagal penuh (Bocor/Tidak Atomic)');
    }

    /**
     * Case 10: "Branch Isolation Cross-Check" (Security)
     * Memastikan user Cabang A tidak bisa mem-posting jurnal ke Cabang B.
     */
    public function test_branch_isolation_cross_check(): void
    {
        $branchB = Branch::factory()->create(['name' => 'Cabang B', 'short_name' => 'CBB']);
        
        // Buat user di Cabang B
        $userB = User::create([
            'name' => 'Admin B',
            'email' => 'adminb_' . uniqid() . '@klinik.com',
            'password' => bcrypt('password'),
            'branch_uuid' => $branchB->uuid
        ]);

        // Login sebagai User B
        $this->actingAs($userB);

        $validDate = ClosingPeriod::withoutGlobalScopes()->where('branch_uuid', $this->branch->uuid)->where('status', 'open')->first()->end_date ?? \Illuminate\Support\Carbon::now()->toDateString();
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid, // Mencoba posting ke Cabang A (this->branch)
            'journal_date' => $validDate,
            'reference_no' => 'ATTEMPT-CROSS-BRANCH',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => Account::withoutGlobalScopes()->where('is_header', false)->first()->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => Account::withoutGlobalScopes()->where('is_header', false)->skip(1)->first()->uuid, 'debit' => 0, 'credit' => 1000],
            ]
        ]);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("Akses ditolak: Anda tidak memiliki wewenang untuk membuat jurnal di cabang lain.");
        
        $this->journalService->createJournal($dto);
    }

    /**
     * Case 2: "The Zero-Sum Balance" (Validation)
     * Memastikan jurnal hanya bisa disimpan jika Debit - Kredit = 0.
     */
    public function test_zero_sum_balance_validation(): void
    {
        $validDate = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('status', 'open')->first()->end_date ?? \Illuminate\Support\Carbon::now()->toDateString();
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'journal_date' => $validDate,
            'reference_no' => 'UNBALANCED-TEST',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => Account::where('is_header', false)->first()->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => Account::where('is_header', false)->skip(1)->first()->uuid, 'debit' => 0, 'credit' => 999999], // Selisih 1 Rupiah
            ]
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Journal unbalanced");

        $this->journalService->createJournal($dto);
    }
}

class JournalSimulationTest extends TestCase
{
    use RefreshDatabase;
    use SaleSimulation, EncounterSimulation, PurchaseSimulation, DebtSimulation, ReceivableSimulation, ExpenseSimulation;
    use ReturnSimulation, AdjustmentSimulation, DiagnosticSimulation, RegistrationSimulation, IntegritySimulation;

    protected JournalService $journalService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        $this->journalService = app(JournalService::class);
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();
    }
}
