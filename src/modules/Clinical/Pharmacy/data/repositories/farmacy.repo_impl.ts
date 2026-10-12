import { type Either, left, right } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import { 
    type RecipeModel, 
    type FarmacyTransactionModel, 
    type InventoryModel, 
    type AccountingAccountModel, 
    type ManagementContactModel,
    type DashboardSummary,
    type MedicationItem,
    type PatientProfile,
    type ReportItem,
    type FarmacyFilters,
    type FarmacyFormData,
    type FarmacyImportResult,
    type FarmacyModel,
    type FarmacyMutationResult,
    type FarmacyPaginatedResult,
    type FarmacyTransactionHubData,
    type FarmacyInventoryHubData,
    type RecipeFilters,
    type FarmacyRecipeOptions,
    type FarmacyStockAdjustmentOptions,
    type FarmacyStockAdjustmentRow,
    type FarmacyStockMutationRow,
    type FarmacyStockOpnameRow,
    PelayananStatus
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import { 
    MOCK_RECIPES, 
    MOCK_TRANSACTIONS, 
    MOCK_INVENTORY, 
    MOCK_ACCOUNTS, 
    MOCK_CONTACTS,
    MOCK_DASHBOARD_SUMMARY,
    MOCK_REPORTS,
    MOCK_RECIPE_DETAILS,
    MOCK_PATIENT_PROFILE,
    FARMACY_FALLBACK_RECENT_ACTIVITIES,
    FARMACY_PURCHASE_MENUS,
    FARMACY_SALES_MENUS,
    FARMACY_TRANSACTION_DEFAULTS,
    FARMACY_STOCK_ACTIVITIES,
    FARMACY_STOCK_MENUS,
    MOCK_STOCK_ADJUSTMENT_ROWS,
    MOCK_STOCK_OPNAME_ROWS,
    MOCK_STOCK_MUTATION_ROWS,
    MOCK_ADJUSTMENT_BUSINESS_UNITS,
    MOCK_ADJUSTMENT_WAREHOUSES,
    MOCK_ADJUSTMENT_PRODUCTS,
    MOCK_ADJUSTMENT_BATCHES,
    MOCK_PHARMACISTS,
    MOCK_RECIPE_WAREHOUSES,
    MOCK_RECIPE_BUSINESS_UNITS
} from '@modules/Clinical/Pharmacy/data/sources/farmacy_mock_data';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Terjadi kesalahan tidak terduga.';

export class FarmacyRepoImpl implements FarmacyRepository {
    async getDashboardSummary(): Promise<Either<Failure, DashboardSummary>> {
        try {
            return right(MOCK_DASHBOARD_SUMMARY);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getRecipes(filters?: RecipeFilters): Promise<Either<Failure, RecipeModel[]>> {
        try {
            let recipes = [...MOCK_RECIPES];
            if (filters?.search) {
                const search = filters.search.toLowerCase();
                recipes = recipes.filter(r => 
                    r.patient_name.toLowerCase().includes(search) || 
                    r.uuid.toLowerCase().includes(search)
                );
            }
            return right(recipes);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getRecipeDetails(uuid: string): Promise<Either<Failure, MedicationItem[]>> {
        try {
            return right(MOCK_RECIPE_DETAILS);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getPatientProfile(uuid: string): Promise<Either<Failure, PatientProfile>> {
        try {
            return right(MOCK_PATIENT_PROFILE);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getTransactions(): Promise<Either<Failure, FarmacyTransactionModel[]>> {
        try {
            return right(MOCK_TRANSACTIONS);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getInventory(): Promise<Either<Failure, InventoryModel[]>> {
        try {
            return right(MOCK_INVENTORY);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getAccounts(): Promise<Either<Failure, AccountingAccountModel[]>> {
        try {
            return right(MOCK_ACCOUNTS);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getContacts(): Promise<Either<Failure, ManagementContactModel[]>> {
        try {
            return right(MOCK_CONTACTS);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getReports(): Promise<Either<Failure, ReportItem[]>> {
        try {
            return right(MOCK_REPORTS);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getTransactionHubData(): Promise<Either<Failure, FarmacyTransactionHubData>> {
        try {
            const activities = FARMACY_FALLBACK_RECENT_ACTIVITIES.map(a => ({
                ...a,
                amount: a.amount.toString()
            }));

            return right({
                salesMenus: FARMACY_SALES_MENUS,
                purchaseMenus: FARMACY_PURCHASE_MENUS,
                stats: FARMACY_TRANSACTION_DEFAULTS,
                activities: activities,
            });
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getInventoryHubData(): Promise<Either<Failure, FarmacyInventoryHubData>> {
        try {
            return right({
                menus: FARMACY_STOCK_MENUS,
                activities: FARMACY_STOCK_ACTIVITIES,
            });
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getStockAdjustments(): Promise<Either<Failure, FarmacyStockAdjustmentRow[]>> {
        try {
            return right(MOCK_STOCK_ADJUSTMENT_ROWS.map((row) => ({ ...row })));
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getStockOpnames(): Promise<Either<Failure, FarmacyStockOpnameRow[]>> {
        try {
            return right(MOCK_STOCK_OPNAME_ROWS.map((row) => ({ ...row })));
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getStockMutations(): Promise<Either<Failure, FarmacyStockMutationRow[]>> {
        try {
            return right(MOCK_STOCK_MUTATION_ROWS.map((row) => ({ ...row })));
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getStockAdjustmentOptions(): Promise<Either<Failure, FarmacyStockAdjustmentOptions>> {
        try {
            return right({
                businessUnits: MOCK_ADJUSTMENT_BUSINESS_UNITS,
                warehouses: MOCK_ADJUSTMENT_WAREHOUSES,
                products: MOCK_ADJUSTMENT_PRODUCTS,
                batches: MOCK_ADJUSTMENT_BATCHES
            });
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async getRecipeOptions(): Promise<Either<Failure, FarmacyRecipeOptions>> {
        try {
            return right({
                pharmacists: MOCK_PHARMACISTS,
                warehouses: MOCK_RECIPE_WAREHOUSES,
                businessUnits: MOCK_RECIPE_BUSINESS_UNITS
            });
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async paginate(filters: FarmacyFilters): Promise<Either<Failure, FarmacyPaginatedResult>> {
        try {
            return right({
                data: [],
                meta: {
                    current_page: filters.page ?? 1,
                    per_page: filters.per_page ?? 5,
                    total: 0,
                    last_page: 1,
                    from: 0,
                    to: 0,
                    links: []
                },
                summary: {
                    total: 0,
                    dokter: 0,
                    other: 0,
                },
            });
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async save(data: FarmacyFormData): Promise<Either<Failure, FarmacyMutationResult>> {
        try {
            const savedData: FarmacyModel = {
                uuid: data.uuid ?? 'mock-farmacy',
                name: data.name,
                email: data.email,
                role_uuid: data.role_uuids[0] ?? '',
                role_name: '',
                role_label: '',
                role_uuids: data.role_uuids,
                roles: [],
                employee_uuid: data.employee_uuid,
                employee_name: '',
                created_at_label: '',
                avatar_initial: data.name.slice(0, 1).toUpperCase(),
                avatar_color: 'slate',
                is_current_farmacy: false,
            };

            return right({
                message: 'Data farmasi berhasil disimpan.',
                data: savedData,
            });
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async delete(uuid: string): Promise<Either<Failure, void>> {
        try {
            void uuid;
            return right(undefined);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async export(filters: FarmacyFilters): Promise<Either<Failure, void>> {
        try {
            void filters;
            return right(undefined);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async import(file: File): Promise<Either<Failure, FarmacyImportResult>> {
        try {
            void file;
            return right({
                message: 'Import farmasi berhasil diproses.',
                imported: 0,
                skipped: 0,
            });
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }

    async downloadImportTemplate(): Promise<Either<Failure, void>> {
        try {
            return right(undefined);
        } catch (error) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
