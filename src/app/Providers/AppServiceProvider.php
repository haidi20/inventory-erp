<?php

namespace App\Providers;

use App\Http\View\Composers\SidebarComposer;
use App\Modules\Clinical\Ancillary\Repositories\LabOrderRepository;
use App\Modules\Clinical\Ancillary\Repositories\LabOrderRepositoryInterface;
use App\Modules\Clinical\Ancillary\Repositories\RadiologyOrderRepository;
use App\Modules\Clinical\Ancillary\Repositories\RadiologyOrderRepositoryInterface;
use App\Modules\Clinical\Classification\Repositories\Icd10CodeRepository;
use App\Modules\Clinical\Classification\Repositories\Icd10CodeRepositoryInterface;
use App\Modules\Clinical\Integration\Repositories\SatusehatRepository;
use App\Modules\Clinical\Integration\Repositories\SatusehatRepositoryInterface;
use App\Modules\Clinical\Integration\Repositories\SatusehatSettingsRepository;
use App\Modules\Clinical\Integration\Repositories\SatusehatSettingsRepositoryInterface;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\MedicalRecord\Repositories\ComplaintRepositoryInterface;
use App\Modules\Clinical\MedicalRecord\Repositories\EncounterRepository;
use App\Modules\Clinical\MedicalRecord\Repositories\EncounterRepositoryInterface;
use App\Modules\Clinical\MedicalRecord\Repositories\EloquentComplaintRepository;
use App\Modules\Clinical\Registration\Repositories\PatientRepository;
use App\Modules\Clinical\Registration\Repositories\PatientRepositoryInterface;
use App\Modules\Clinical\Registration\Repositories\RegistrationRepository;
use App\Modules\Clinical\Registration\Repositories\RegistrationRepositoryInterface;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Identity\Repositories\AuthRepository;
use App\Modules\Core\Identity\Repositories\AuthRepositoryInterface;
use App\Modules\Core\Identity\Repositories\UserRepository;
use App\Modules\Core\Identity\Repositories\UserRepositoryInterface;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Repositories\BranchRepository;
use App\Modules\Core\Organization\Repositories\BranchRepositoryInterface;
use App\Modules\Core\Organization\Repositories\CompanyRepository;
use App\Modules\Core\Organization\Repositories\CompanyRepositoryInterface;
use App\Modules\Core\Platform\Repositories\BackupRepository;
use App\Modules\Core\Platform\Repositories\BackupRepositoryInterface;
use App\Modules\Core\Platform\Repositories\FileRepository;
use App\Modules\Core\Platform\Repositories\FileRepositoryInterface;
use App\Modules\Core\Platform\Repositories\ImageRepository;
use App\Modules\Core\Platform\Repositories\ImageRepositoryInterface;
use App\Modules\Core\Platform\Repositories\SettingsRepository;
use App\Modules\Core\Platform\Repositories\SettingsRepositoryInterface;
use App\Modules\Core\Platform\Repositories\SidebarNavigationMenuRepository;
use App\Modules\Finance\Journal\Models\AuditLog;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Repositories\AccountRepository;
use App\Modules\Finance\Journal\Repositories\AccountRepositoryInterface;
use App\Modules\Operational\Inventory\Models\StockTransfer;
use App\Modules\Operational\Inventory\Repositories\StockRepository;
use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;
use App\Modules\Operational\Master\Repositories\EmployeeRepository;
use App\Modules\Operational\Master\Repositories\EmployeeRepositoryInterface;
use App\Modules\Operational\Master\Repositories\ItemConversionRepository;
use App\Modules\Operational\Master\Repositories\ItemConversionRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LocationRepository;
use App\Modules\Operational\Master\Repositories\LocationRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LookupRepository;
use App\Modules\Operational\Master\Repositories\LookupRepositoryInterface;
use App\Modules\Operational\Master\Repositories\LookupSetRepository;
use App\Modules\Operational\Master\Repositories\LookupSetRepositoryInterface;
use App\Modules\Operational\Master\Repositories\MasterCustomerRepository;
use App\Modules\Operational\Master\Repositories\MasterCustomerRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PaymentTermRepository;
use App\Modules\Operational\Master\Repositories\PaymentTermRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PersonRepository;
use App\Modules\Operational\Master\Repositories\PersonRepositoryInterface;
use App\Modules\Operational\Master\Repositories\PriceLevelRepository;
use App\Modules\Operational\Master\Repositories\PriceLevelRepositoryInterface;
use App\Modules\Operational\Master\Repositories\ProductRepository;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use App\Modules\Operational\Master\Repositories\SupplierRepository;
use App\Modules\Operational\Master\Repositories\SupplierRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TaxRepository;
use App\Modules\Operational\Master\Repositories\TaxRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepository;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepositoryInterface;
use App\Modules\Operational\Master\Repositories\TreatmentRepository;
use App\Modules\Operational\Master\Repositories\TreatmentRepositoryInterface;
use App\Modules\Operational\Master\Repositories\UnitRepository;
use App\Modules\Operational\Master\Repositories\UnitRepositoryInterface;
use App\Modules\Operational\Procurement\Repositories\PosPurchaseRepository;
use App\Modules\Operational\Procurement\Repositories\PosPurchaseRepositoryInterface;
use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepository;
use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepositoryInterface;
use App\Modules\Operational\Procurement\Repositories\PurchaseRepository;
use App\Modules\Operational\Procurement\Repositories\PurchaseRepositoryInterface;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Sales\Repositories\SaleRepository;
use App\Modules\Operational\Sales\Repositories\SaleRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\SalesRepository;
use App\Modules\Operational\Sales\Repositories\SalesRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepository;
use App\Modules\Operational\Sales\Repositories\SalesTransactionRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\ServicePackageRepository;
use App\Modules\Operational\Sales\Repositories\ServicePackageRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\ServiceRepository;
use App\Modules\Operational\Sales\Repositories\ServiceRepositoryInterface;
use App\Modules\Operational\Sales\Repositories\ShipRepository;
use App\Modules\Operational\Sales\Repositories\ShipRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ModuleServiceProvider::class);

        // Core\Platform bindings
        $this->app->bind(
            BackupRepositoryInterface::class,
            BackupRepository::class
        );
        $this->app->bind(
            FileRepositoryInterface::class,
            FileRepository::class
        );
        $this->app->bind(
            ImageRepositoryInterface::class,
            ImageRepository::class
        );
        $this->app->bind(
            LookupRepositoryInterface::class,
            LookupRepository::class
        );
        $this->app->bind(
            SettingsRepositoryInterface::class,
            SettingsRepository::class
        );

        // Core\Identity bindings
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );

        // Core\Authorization bindings
        $this->app->bind(
            SidebarNavigationMenuRepository::class,
            SidebarNavigationMenuRepository::class
        );

        // Operational\Master bindings
        $this->app->bind(
            LookupSetRepositoryInterface::class,
            LookupSetRepository::class
        );
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
        $this->app->bind(
            UnitRepositoryInterface::class,
            UnitRepository::class
        );
        $this->app->bind(
            TaxRepositoryInterface::class,
            TaxRepository::class
        );
        $this->app->bind(
            PaymentTermRepositoryInterface::class,
            PaymentTermRepository::class
        );
        $this->app->bind(
            LocationRepositoryInterface::class,
            LocationRepository::class
        );
        $this->app->bind(
            SupplierRepositoryInterface::class,
            SupplierRepository::class
        );
        $this->app->bind(
            EmployeeRepositoryInterface::class,
            EmployeeRepository::class
        );
        $this->app->bind(
            TreatmentRepositoryInterface::class,
            TreatmentRepository::class
        );
        $this->app->bind(
            TreatmentPackageRepositoryInterface::class,
            TreatmentPackageRepository::class
        );
        $this->app->bind(
            ItemConversionRepositoryInterface::class,
            ItemConversionRepository::class
        );
        $this->app->bind(
            PriceLevelRepositoryInterface::class,
            PriceLevelRepository::class
        );
        $this->app->bind(
            MasterCustomerRepositoryInterface::class,
            MasterCustomerRepository::class
        );
        $this->app->bind(
            PersonRepositoryInterface::class,
            PersonRepository::class
        );

        // Operational\Sales bindings
        $this->app->bind(
            ServiceRepositoryInterface::class,
            ServiceRepository::class
        );
        $this->app->bind(
            ServicePackageRepositoryInterface::class,
            ServicePackageRepository::class
        );
        $this->app->bind(
            SaleRepositoryInterface::class,
            SaleRepository::class
        );
        $this->app->bind(
            SalesRepositoryInterface::class,
            SalesRepository::class
        );
        $this->app->bind(
            SalesTransactionRepositoryInterface::class,
            SalesTransactionRepository::class
        );
        $this->app->bind(
            ShipRepositoryInterface::class,
            ShipRepository::class
        );

        // Operational\Procurement bindings
        $this->app->bind(
            PosPurchaseRepositoryInterface::class,
            PosPurchaseRepository::class
        );
        $this->app->bind(
            PurchaseOrderRepositoryInterface::class,
            PurchaseOrderRepository::class
        );
        $this->app->bind(
            PurchaseRepositoryInterface::class,
            PurchaseRepository::class
        );

        // Operational\Inventory bindings
        $this->app->bind(
            StockRepositoryInterface::class,
            StockRepository::class
        );

        // Clinical\Integration bindings
        $this->app->bind(
            SatusehatRepositoryInterface::class,
            SatusehatRepository::class
        );

        // Clinical\Registration bindings
        $this->app->bind(
            RegistrationRepositoryInterface::class,
            RegistrationRepository::class
        );
        $this->app->bind(
            PatientRepositoryInterface::class,
            PatientRepository::class
        );

        // Core\Organization bindings
        $this->app->bind(
            CompanyRepositoryInterface::class,
            CompanyRepository::class
        );
        $this->app->bind(
            BranchRepositoryInterface::class,
            BranchRepository::class
        );

        // Clinical\Ancillary bindings
        $this->app->bind(
            LabOrderRepositoryInterface::class,
            LabOrderRepository::class
        );
        $this->app->bind(
            RadiologyOrderRepositoryInterface::class,
            RadiologyOrderRepository::class
        );

        // Clinical\Classification bindings
        $this->app->bind(
            Icd10CodeRepositoryInterface::class,
            Icd10CodeRepository::class
        );
        // Clinical\Integration\Settings bindings
        $this->app->bind(
            SatusehatSettingsRepositoryInterface::class,
            SatusehatSettingsRepository::class
        );

        // Clinical\MedicalRecord bindings
        $this->app->bind(
            AccountRepositoryInterface::class,
            AccountRepository::class
        );
        $this->app->bind(
            EncounterRepositoryInterface::class,
            EncounterRepository::class
        );
        $this->app->bind(
            ComplaintRepositoryInterface::class,
            EloquentComplaintRepository::class
        );
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();


        // Polymorphic morphMap
        Relation::morphMap([
            'User'          => User::class,
            'Branch'        => Branch::class,
            'AuditLog'      => AuditLog::class,
            'Encounter'     => Encounter::class,
            'Sale'          => Sale::class,
            'StockTransfer' => StockTransfer::class,
            'Journal'       => Journal::class,
        ]);

        // Ensure Inertia root view is always set correctly
        Inertia::setRootView('layouts.app');

        // Share $errors default agar selalu available bahkan saat middleware ShareErrorsFromSession di-disable (e.g. di test)
        View::share('errors', session('errors') ?? new ViewErrorBag());

        // View Composer for sidebar
        View::composer('layouts.app', SidebarComposer::class);

        // Super-admin bypass
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // Dashboard monitoring Laravel Pulse (/pulse) -- hanya user dengan izin.
        // Default vendor = semua orang di environment local; di sini diganti
        // eksplisit agar aman saat APP_ENV berubah ke production.
        Gate::define('viewPulse', function ($user) {
            return $user->can('system.monitor.view');
        });
    }
}
