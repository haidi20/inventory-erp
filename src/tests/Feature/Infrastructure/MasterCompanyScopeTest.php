<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Coa;
use App\Modules\Finance\Journal\Models\CoaSetup;
use App\Modules\Operational\Master\Models\ItemConversion;
use App\Modules\Operational\Master\Models\ItemUom;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupType;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\LookupValueDetail;
use App\Modules\Operational\Master\Models\PriceLevel;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\ProductAccount;
use App\Modules\Operational\Master\Models\ProductDiscountRule;
use App\Modules\Operational\Master\Models\ProductImage;
use App\Modules\Operational\Master\Models\ProductLocation;
use App\Modules\Operational\Master\Models\ProductOpeningBalance;
use App\Modules\Operational\Master\Models\ProductUnit;
use App\Modules\Operational\Master\Models\Shift;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Master\Models\UserWarehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * TDD test untuk Section 30.6 iterasi 3 (master per-company).
 *
 * Dua kelompok berdasarkan skema aktual (audit kolom per tabel):
 *  - DUAL (company_uuid + branch_uuid NOT NULL/nullable): Coa, CoaSetup,
 *    PriceLevel, ItemConversion, ItemUom, ProductAccount, ProductDiscountRule,
 *    ProductImage, ProductLocation, ProductOpeningBalance, ProductUnit,
 *    Shift, UserWarehouse -- per Section 30.5 "tambah kedua trait bila
 *    kolom sudah ada", meskipun plan awal menyebut cukup company.
 *  - COMPANY-ONLY (hanya company_uuid, tanpa kolom branch): LookupType,
 *    LookupValue, LookupValueDetail.
 *
 * EXCLUDE (didokumentasikan sebagai pengecualian di iterasi 4):
 *  - TaxSetting: tabel tax_settings TIDAK punya kolom company/branch.
 *  - ProductPurchase/ProductQuotation/ProductSale: tabel detail transaksi
 *    (product_purchases/product_quotation/product_sales) tanpa kolom scope,
 *    terfilter transitif via parent.
 *  - LookupSet: company_uuid nullable dengan semantik "null untuk lookup
 *    global" -- CompanyScope justru akan menyembunyikan lookup global.
 */
class MasterCompanyScopeTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private User $user;

    /** Fixture lintas tabel yang dipakai beberapa model (dibuat per company). */
    private array $fixtures = [];

    /** Counter nilai unik per run test. */
    private int $seq = 0;

    private function nextCode(string $prefix): string
    {
        return $prefix . '-' . (++$this->seq) . '-' . uniqid();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create(['deleted_at' => 0]);
        $this->companyB = Company::factory()->create(['deleted_at' => 0]);
        $this->branchA = Branch::factory()->create(['company_uuid' => $this->companyA->uuid]);
        $this->branchB = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);

        $this->user = User::factory()->create();

        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'is_default' => true,
        ]);
    }

    private function actingAsScopedUser(): void
    {
        $this->actingAs($this->user);
        Auth::shouldUse('web');
    }

    private function fixturesFor(string $companyUuid, string $branchUuid): array
    {
        if (isset($this->fixtures[$companyUuid])) {
            return $this->fixtures[$companyUuid];
        }

        $account = Account::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'ACC-' . substr($companyUuid, 0, 8),
            'name' => 'Kas Test',
            'type' => 'asset',
        ]);

        $unit = Unit::query()->create([
            'company_uuid' => $companyUuid,
            'code' => 'U-' . substr($companyUuid, 0, 8) . '-' . (++$this->seq),
            'name' => 'Satuan Test',
        ]);

        $location = Location::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'LOC-' . substr($companyUuid, 0, 8),
            'name' => 'Gudang Test',
        ]);

        $product = Product::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'ITM-' . substr($companyUuid, 0, 8),
            'name' => 'Item Test',
        ]);

        // LookupSet TIDAK ikut diuji (exclude) sehingga aman dibuat eager:
        // tidak menambah baris pada tabel model yang sedang diuji.
        $lookupSet = LookupSet::query()->create([
            'company_uuid' => $companyUuid,
            'name' => 'Set Test ' . substr($companyUuid, 0, 8),
        ]);

        return $this->fixtures[$companyUuid] = compact('account', 'unit', 'location', 'product', 'lookupSet');
    }

    /**
     * Daftar model master iterasi 3. Parameter kedua: apakah tabelnya
     * punya kolom branch_uuid (dual scope) atau company saja.
     */
    public static function masterModelProvider(): array
    {
        return [
            'Coa (Finance, dual)' => [Coa::class, true],
            'CoaSetup (Finance, dual)' => [CoaSetup::class, true],
            'PriceLevel (Master, dual)' => [PriceLevel::class, true],
            'ItemConversion (Master, dual)' => [ItemConversion::class, true],
            'ItemUom (Master, dual)' => [ItemUom::class, true],
            'ProductAccount (Master, dual)' => [ProductAccount::class, true],
            'ProductDiscountRule (Master, dual)' => [ProductDiscountRule::class, true],
            'ProductImage (Master, dual)' => [ProductImage::class, true],
            'ProductLocation (Master, dual)' => [ProductLocation::class, true],
            'ProductOpeningBalance (Master, dual)' => [ProductOpeningBalance::class, true],
            'ProductUnit (Master, dual)' => [ProductUnit::class, true],
            'Shift (Master, dual)' => [Shift::class, true],
            'UserWarehouse (Master, dual)' => [UserWarehouse::class, true],
            'LookupType (Master, company)' => [LookupType::class, false],
            'LookupValue (Master, company)' => [LookupValue::class, false],
            'LookupValueDetail (Master, company)' => [LookupValueDetail::class, false],
        ];
    }

    /**
     * @dataProvider masterModelProvider
     */
    public function test_master_models_are_scoped_by_company_of_logged_in_user(string $modelClass, bool $dual): void
    {
        $this->actingAsScopedUser();

        $fxA = $this->fixturesFor($this->companyA->uuid, $this->branchA->uuid);
        $fxB = $this->fixturesFor($this->companyB->uuid, $this->branchB->uuid);

        $modelA = $modelClass::query()->create($this->attributesFor($modelClass, $fxA, $this->companyA->uuid, $this->branchA->uuid, $dual));
        $modelClass::query()->create($this->attributesFor($modelClass, $fxB, $this->companyB->uuid, $this->branchB->uuid, $dual));

        // Global scope harus otomatis: query tanpa where manual hanya
        // mengembalikan data company user yang login. getKey() dipakai
        // karena beberapa model (CoaSetup/Shift) tidak ber-PK uuid.
        $this->assertSame(1, $modelClass::query()->count(), "{$modelClass} harus terfilter ke company A saja.");
        $this->assertSame($modelA->getKey(), $modelClass::query()->first()->getKey());
    }

    /**
     * @dataProvider masterModelProvider
     */
    public function test_master_models_create_fill_scope_columns_from_context(string $modelClass, bool $dual): void
    {
        $this->actingAsScopedUser();

        $fxA = $this->fixturesFor($this->companyA->uuid, $this->branchA->uuid);

        // company_uuid/branch_uuid TIDAK diisi eksplisit -- trait wajib
        // mengisinya dari konteks user login.
        $model = $modelClass::query()->create($this->attributesFor($modelClass, $fxA, null, null, $dual));

        $this->assertSame($this->companyA->uuid, $model->company_uuid, "{$modelClass} harus mengisi company_uuid dari konteks.");
        if ($dual) {
            $this->assertSame($this->branchA->uuid, $model->branch_uuid, "{$modelClass} harus mengisi branch_uuid dari konteks.");
        }
    }

    /**
     * Parent yang ikut diuji (LookupValue) tidak boleh dibuat eager di
     * fixturesFor supaya tidak menggandakan hitungan baris pada scope test.
     */
    private function ensureParent(array $fx, ?string $companyUuid, string $key, \Closure $factory): object
    {
        if (isset($fx[$key])) {
            return $fx[$key];
        }

        return $this->fixtures[$companyUuid][$key] = $factory();
    }

    /**
     * Attribute minimum untuk create baris data per model.
     * company/branch null = diserahkan ke trait (auto-fill dari konteks).
     */
    private function attributesFor(string $modelClass, array $fx, ?string $companyUuid, ?string $branchUuid, bool $dual): array
    {
        $base = $dual
            ? ['company_uuid' => $companyUuid, 'branch_uuid' => $branchUuid]
            : ['company_uuid' => $companyUuid];

        return match ($modelClass) {
            Coa::class => array_merge($base, [
                'code' => $this->nextCode('COA'),
                'name' => 'Coa Test',
                'account_uuid' => $fx['account']->uuid,
            ]),
            CoaSetup::class => array_merge($base, [
                'program_account' => $this->nextCode('PRG'),
            ]),
            PriceLevel::class => array_merge($base, [
                'code' => $this->nextCode('PLVL'),
                'name' => 'Level Test',
            ]),
            ItemConversion::class => array_merge($base, [
                'from_unit_uuid' => $fx['unit']->uuid,
                'to_unit_uuid' => $fx['unit']->uuid,
                'conversion_ratio' => 1,
            ]),
            ItemUom::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'uom_code' => 'PCS-' . (++$this->seq),
            ]),
            ProductAccount::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'account_role' => 'sales',
                'account_uuid' => $fx['account']->uuid,
            ]),
            ProductDiscountRule::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'discount_type' => 'percentage',
                'amount' => 5,
            ]),
            ProductImage::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'path' => 'images/' . uniqid() . '.png',
            ]),
            ProductLocation::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'source_type' => 'location',
                'source_id' => $fx['location']->uuid,
                'location_uuid' => $fx['location']->uuid,
            ]),
            ProductOpeningBalance::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'period_month' => '2026-10',
                'opening_qty' => 10,
            ]),
            ProductUnit::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'conversion_ratio' => 1,
            ]),
            Shift::class => array_merge($base, [
                'shift_number' => 1,
                'date' => time(),
                'start_time' => time(),
                'opening_balance' => 0,
                'location_uuid' => $fx['location']->uuid,
                'user_uuid' => $this->user->uuid,
            ]),
            UserWarehouse::class => array_merge($base, [
                'user_uuid' => $this->user->uuid,
                'location_uuid' => $fx['location']->uuid,
            ]),
            LookupType::class => array_merge($base, [
                'code' => $this->nextCode('LTYPE'),
                'name' => 'Type Test',
            ]),
            LookupValue::class => array_merge($base, [
                'lookup_set_uuid' => $fx['lookupSet']->uuid,
                'display_name' => 'Value Test ' . (++$this->seq),
            ]),
            LookupValueDetail::class => array_merge($base, [
                'lookup_value_uuid' => $this->ensureParent(
                    $fx,
                    $companyUuid,
                    'lookupValue',
                    fn (): object => LookupValue::query()->create([
                        'company_uuid' => $companyUuid,
                        'lookup_set_uuid' => $fx['lookupSet']->uuid,
                        'display_name' => 'Value Test ' . (++$this->seq),
                    ])
                )->uuid,
                'idx' => 1,
                'value' => 'detil',
            ]),
            default => $base,
        };
    }
}
