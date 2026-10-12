<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Clinical\MedicalRecord\Models\Complaint;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\RecurringJournal;
use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Operational\Inventory\Models\ItemCustomField;
use App\Modules\Operational\Inventory\Models\ItemDiscount;
use App\Modules\Operational\Inventory\Models\ItemDiscountRule;
use App\Modules\Operational\Manufacturing\Models\Bom;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Operational\Master\Models\MasterCustomer;
use App\Modules\Operational\Master\Models\MasterProduct;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\PersonBalance;
use App\Modules\Operational\Master\Models\PersonSaldo;
use App\Modules\Operational\Master\Models\ProductPrice;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use App\Modules\Operational\Master\Models\SupplierRecord;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Models\SaleItem;
use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;
use Tests\TestCase;

class RemainingModelScopeTraitTest extends TestCase
{
    /**
     * @dataProvider remainingModelProvider
     */
    public function test_remaining_models_use_expected_multi_org_traits(string $modelClass, array $expectedTraits): void
    {
        $actualTraits = class_uses_recursive($modelClass);

        foreach ($expectedTraits as $trait) {
            $this->assertArrayHasKey($trait, $actualTraits, "{$modelClass} harus memakai {$trait}.");
        }
    }

    public static function remainingModelProvider(): array
    {
        return [
            'Complaint' => [Complaint::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Encounter' => [Encounter::class, [HasCompanyScope::class, HasBranchScope::class]],
            'EncounterItem' => [EncounterItem::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Patient' => [Patient::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Registration' => [Registration::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Account' => [Account::class, [HasCompanyScope::class, HasBranchScope::class]],
            'RecurringJournal' => [RecurringJournal::class, [HasCompanyScope::class, HasBranchScope::class]],
            'AccountBalance' => [AccountBalance::class, [HasCompanyScope::class, HasBranchScope::class]],
            'ClosingPeriod' => [ClosingPeriod::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Payable' => [Payable::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Receivable' => [Receivable::class, [HasCompanyScope::class, HasBranchScope::class]],
            'ItemCustomField' => [ItemCustomField::class, [HasCompanyScope::class, HasBranchScope::class]],
            'ItemDiscount' => [ItemDiscount::class, [HasCompanyScope::class, HasBranchScope::class]],
            'ItemDiscountRule' => [ItemDiscountRule::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Bom' => [Bom::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Employee' => [Employee::class, [HasCompanyScope::class, HasBranchScope::class]],
            'MasterCustomer' => [MasterCustomer::class, [HasCompanyScope::class, HasBranchScope::class]],
            'MasterProduct' => [MasterProduct::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Person' => [Person::class, [HasCompanyScope::class, HasBranchScope::class]],
            'PersonBalance' => [PersonBalance::class, [HasCompanyScope::class, HasBranchScope::class]],
            'PersonSaldo' => [PersonSaldo::class, [HasCompanyScope::class, HasBranchScope::class]],
            'ProductPrice' => [ProductPrice::class, [HasCompanyScope::class, HasBranchScope::class]],
            'ServicePackageItem' => [ServicePackageItem::class, [HasCompanyScope::class, HasBranchScope::class]],
            'SupplierRecord' => [SupplierRecord::class, [HasCompanyScope::class, HasBranchScope::class]],
            'Sale' => [Sale::class, [HasCompanyScope::class, HasBranchScope::class]],
            'SaleItem' => [SaleItem::class, [HasCompanyScope::class, HasBranchScope::class]],
        ];
    }

    public function test_scope_helper_does_not_read_legacy_user_columns(): void
    {
        $files = [
            base_path('app/Http/Middleware/HandleInertiaRequests.php'),
            base_path('modules/Core/Identity/Http/Controllers/WebUserController.php'),
            base_path('modules/Finance/Journal/Http/Controllers/JournalController.php'),
            base_path('modules/Operational/Sales/Services/CashBankService.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertStringNotContainsString('->company_uuid', $contents, "{$file} masih membaca kolom legacy users.");
            $this->assertStringNotContainsString('->branch_uuid', $contents, "{$file} masih membaca kolom legacy users.");
        }
    }
}
