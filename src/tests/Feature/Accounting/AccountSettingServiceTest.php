<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Journal\Services\AccountSettingService;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Tests\TestCase;

class AccountSettingServiceTest extends TestCase
{
    protected User $admin;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);

        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $this->admin = User::where('email', 'admin@klinik.local')->firstOrFail();
    }

    // ==========================================
    // TESTING LAYER REPOSITORY
    // ==========================================
    
    public function test_repository_can_fetch_all_accounts(): void
    {
        $repository = new AccountSettingRepository();
        $accounts = $repository->allAccounts();
        
        $this->assertNotEmpty($accounts);
        // Pastikan terurut berdasarkan kode
        $this->assertEquals($accounts->sortBy('code')->pluck('code')->toArray(), $accounts->pluck('code')->toArray());
        
        // Cek bahwa setidaknya akun Kas (1101) ada di sana
        $this->assertTrue($accounts->contains('code', '1101'));
    }

    public function test_repository_can_find_account_by_code(): void
    {
        $repository = new AccountSettingRepository();
        
        // Test Valid Account
        $account = $repository->findAccountByCode('1101');
        $this->assertNotNull($account);
        $this->assertEquals('1101', $account->code);
        
        // Test Invalid Account
        $invalidAccount = $repository->findAccountByCode('INVALID-XYZ');
        $this->assertNull($invalidAccount);
    }

    public function test_repository_can_fetch_mappings(): void
    {
        $repository = new AccountSettingRepository();
        $mappings = $repository->getMappings();
        
        $this->assertNotEmpty($mappings);
        
        // Cek Eager Loading `account` relation terpenuhi
        $firstMapping = $mappings->first();
        $this->assertTrue($firstMapping->relationLoaded('account'));
        $this->assertNotNull($firstMapping->account);
    }

    public function test_repository_can_update_mapping(): void
    {
        $repository = new AccountSettingRepository();
        $account = $repository->findAccountByCode('1101');
        
        $key = 'test.repo.mapping';
        
        // Buat mapping baru
        $mapping = $repository->updateMapping($key, $account->uuid);
        
        $this->assertInstanceOf(AccountMapping::class, $mapping);
        $this->assertEquals($key, $mapping->key);
        $this->assertEquals($account->uuid, $mapping->account_uuid);
        
        // Update mapping ke akun lain
        $otherAccount = $repository->findAccountByCode('1102');
        $updatedMapping = $repository->updateMapping($key, $otherAccount->uuid);
        
        $this->assertEquals($otherAccount->uuid, $updatedMapping->account_uuid);
        // Pastikan ID mapping sama (tidak membuat record baru karena key adalah unique di konteks updateOrCreate)
        $this->assertEquals($mapping->uuid, $updatedMapping->uuid);
    }

    // ==========================================
    // TESTING LAYER SERVICE
    // ==========================================
    
    public function test_service_can_get_chart_of_accounts(): void
    {
        $service = app(AccountSettingService::class);
        $accounts = $service->getChartOfAccounts();
        
        $this->assertNotEmpty($accounts);
        $this->assertTrue($accounts->contains('code', '2101')); // Contoh Hutang Dagang
    }

    public function test_service_can_get_account_mappings(): void
    {
        $service = app(AccountSettingService::class);
        $mappings = $service->getAccountMappings();
        
        $this->assertNotEmpty($mappings);
        $this->assertTrue($mappings->contains('key', 'penjualan.kas')); // Default di DemoSeeder
    }

    public function test_service_can_update_mapping_by_code(): void
    {
        $service = app(AccountSettingService::class);
        $key = 'penjualan.kas';
        
        // Akun Kas Kecil (1102 dirubah dari Kas 1101)
        $newAccountCode = '1102'; 
        
        $updatedMapping = $service->updateMappingByCode($key, $newAccountCode);
        
        $this->assertNotNull($updatedMapping);
        $this->assertEquals($key, $updatedMapping->key);
        
        // Verifikasi ke DB bahwa relasi account sudah diubah ke 1102
        $this->assertEquals($newAccountCode, $updatedMapping->account->code);
        
        $mappingInDb = AccountMapping::where('key', $key)->first();
        $this->assertEquals($newAccountCode, $mappingInDb->account->code);
    }

    public function test_service_returns_null_when_updating_mapping_with_invalid_code(): void
    {
        $service = app(AccountSettingService::class);
        $key = 'penjualan.kas';
        $invalidAccountCode = '9999-NOT-EXIST';
        
        $result = $service->updateMappingByCode($key, $invalidAccountCode);
        
        $this->assertNull($result);
        
        // Verifikasi mapping di DB tidak berubah
        $mappingInDb = AccountMapping::where('key', $key)->first();
        $this->assertNotEquals($invalidAccountCode, $mappingInDb->account->code);
    }

    // ==========================================
    // TESTING NEW SERVICE FEATURES
    // ==========================================

    public function test_service_can_manage_tax_settings(): void
    {
        $service = app(AccountSettingService::class);
        
        // Cek data awal (dari DemoSeeder)
        $taxes = $service->getTaxSettings();
        $this->assertNotEmpty($taxes);
        $this->assertTrue($taxes->contains('name', 'PPN'));

        // Update/Ciptakan pajak baru
        $newTax = $service->updateTaxSetting('PPH 21', 2.5, '2101'); // 2101 = Hutang PPH
        $this->assertNotNull($newTax);
        $this->assertEquals('PPH 21', $newTax->name);
        $this->assertEquals(2.5, $newTax->percentage);
        $this->assertEquals('2101', $newTax->account->code);
    }

    public function test_service_can_manage_opening_balances(): void
    {
        $service = app(AccountSettingService::class);
        
        // Cek data awal
        $balances = $service->getOpeningBalances($this->branch->uuid);
        $this->assertNotEmpty($balances);

        // Tambah/Update saldo awal Kas Kecil
        $balance = $service->updateOpeningBalance($this->branch->uuid, '1102', 5000000);
        $this->assertNotNull($balance);
        $this->assertEquals(5000000, $balance->opening_balance);
        $this->assertEquals(5000000, $balance->closing_balance);
        $this->assertEquals('1102', Account::find($balance->account_uuid)->code);
    }

    public function test_service_reuses_opening_balance_reference_number_for_existing_balance(): void
    {
        $service = app(AccountSettingService::class);

        $firstBalance = $service->updateOpeningBalance($this->branch->uuid, '1102', 5000000);
        $firstJournal = Journal::where('branch_uuid', $this->branch->uuid)
            ->where('reference_type', JournalReferenceType::OPENING_BALANCE)
            ->where('reference_uuid', $firstBalance->uuid)
            ->firstOrFail();

        $updatedBalance = $service->updateOpeningBalance($this->branch->uuid, '1102', 6500000);
        $journals = Journal::where('branch_uuid', $this->branch->uuid)
            ->where('reference_type', JournalReferenceType::OPENING_BALANCE)
            ->where('reference_uuid', $updatedBalance->uuid)
            ->get();

        $this->assertCount(1, $journals);
        $this->assertEquals($firstJournal->uuid, $journals->first()->uuid);
        $this->assertEquals($firstJournal->reference_no, $journals->first()->reference_no);
        $this->assertEquals(6500000, $updatedBalance->opening_balance);
    }

    public function test_service_can_manage_closing_periods(): void
    {
        $service = app(AccountSettingService::class);
        
        // Cek data awal
        $periods = $service->getClosingPeriods($this->branch->uuid);
        $this->assertNotEmpty($periods); // DemoSeeder membuat periode saat ini "open"

        // Tutup periode bulan berjalan (yang memang sudah di-seed 'open' oleh DemoSeeder)
        $currentPeriod = now()->format('Y-m');
        $closedPeriod = $service->closePeriod($this->branch->uuid, $currentPeriod);
        
        $this->assertNotNull($closedPeriod);
        $this->assertEquals('closed', $closedPeriod->status);
        $this->assertNotNull($closedPeriod->closed_at);
        $this->assertEquals($currentPeriod, $closedPeriod->period);

        // Cek kembali daftar periode
        $updatedPeriods = $service->getClosingPeriods($this->branch->uuid);
        $this->assertTrue($updatedPeriods->contains('period', $currentPeriod));
    }
}
