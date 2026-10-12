import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { 
    RecipeModel, 
    FarmacyTransactionModel, 
    InventoryModel, 
    AccountingAccountModel, 
    ManagementContactModel,
    DashboardSummary,
    MedicationItem,
    PatientProfile,
    ReportItem,
    RecipeFilters,
    FarmacyFilters,
    FarmacyFormData,
    FarmacyImportResult,
    FarmacyMutationResult,
    FarmacyPaginatedResult,
    FarmacyTransactionHubData,
    FarmacyInventoryHubData,
    FarmacyStockAdjustmentOptions,
    FarmacyStockAdjustmentRow,
    FarmacyStockMutationRow,
    FarmacyStockOpnameRow,
    FarmacyRecipeOptions,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export interface FarmacyRepository {
    getDashboardSummary(): Promise<Either<Failure, DashboardSummary>>;
    getRecipes(filters?: RecipeFilters): Promise<Either<Failure, RecipeModel[]>>;
    getRecipeDetails(uuid: string): Promise<Either<Failure, MedicationItem[]>>;
    getPatientProfile(uuid: string): Promise<Either<Failure, PatientProfile>>;
    getTransactions(): Promise<Either<Failure, FarmacyTransactionModel[]>>;
    getInventory(): Promise<Either<Failure, InventoryModel[]>>;
    getAccounts(): Promise<Either<Failure, AccountingAccountModel[]>>;
    getContacts(): Promise<Either<Failure, ManagementContactModel[]>>;
    getReports(): Promise<Either<Failure, ReportItem[]>>;
    getTransactionHubData(): Promise<Either<Failure, FarmacyTransactionHubData>>;
    getInventoryHubData(): Promise<Either<Failure, FarmacyInventoryHubData>>;
    getStockAdjustments(): Promise<Either<Failure, FarmacyStockAdjustmentRow[]>>;
    getStockOpnames(): Promise<Either<Failure, FarmacyStockOpnameRow[]>>;
    getStockMutations(): Promise<Either<Failure, FarmacyStockMutationRow[]>>;
    getStockAdjustmentOptions(): Promise<Either<Failure, FarmacyStockAdjustmentOptions>>;
    getRecipeOptions(): Promise<Either<Failure, FarmacyRecipeOptions>>;

    paginate(filters: FarmacyFilters): Promise<Either<Failure, FarmacyPaginatedResult>>;
    save(data: FarmacyFormData): Promise<Either<Failure, FarmacyMutationResult>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(filters: FarmacyFilters): Promise<Either<Failure, void>>;
    import(file: File): Promise<Either<Failure, FarmacyImportResult>>;
    downloadImportTemplate(): Promise<Either<Failure, void>>;
}
