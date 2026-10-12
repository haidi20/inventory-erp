import { FarmacyRepoImpl } from '@modules/Clinical/Pharmacy/data/repositories/farmacy.repo_impl';
import { GetDashboardSummaryUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-dashboard-summary.usecase';
import { GetRecipesUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-recipes.usecase';
import { GetRecipeDetailsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-recipe-details.usecase';
import { GetPatientProfileUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-patient-profile.usecase';
import { GetTransactionsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-transactions.usecase';
import { GetInventoryUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-inventory.usecase';
import { GetInventoryHubDataUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-inventory-hub-data.usecase';
import { GetAccountsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-accounts.usecase';
import { GetContactsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-contacts.usecase';
import { GetReportsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-reports.usecase';
import { GetTransactionHubDataUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-transaction-hub-data.usecase';
import { GetStockAdjustmentsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-stock-adjustments.usecase';
import { GetStockOpnamesUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-stock-opnames.usecase';
import { GetStockMutationsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-stock-mutations.usecase';
import { GetAdjustmentOptionsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-adjustment-options.usecase';
import { GetRecipeOptionsUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/get-recipe-options.usecase';

// Base generic CRUD usecases from duplication
import { fetchFarmacyUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/fetch-farmacy.usecase';
import { saveFarmacyUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/save-farmacy.usecase';
import { deleteFarmacyUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/delete-farmacy.usecase';
import { exportFarmacyUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/export-farmacy.usecase';
import { importFarmacyUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/import-farmacy.usecase';
import { downloadFarmacyImportTemplateUseCase } from '@modules/Clinical/Pharmacy/domain/usecases/download-farmacy-import-template.usecase';

export interface FarmacyVMDependencies {
    getDashboardSummaryUseCase: GetDashboardSummaryUseCase;
    getRecipesUseCase: GetRecipesUseCase;
    getRecipeDetailsUseCase: GetRecipeDetailsUseCase;
    getPatientProfileUseCase: GetPatientProfileUseCase;
    getTransactionsUseCase: GetTransactionsUseCase;
    getInventoryUseCase: GetInventoryUseCase;
    getInventoryHubDataUseCase: GetInventoryHubDataUseCase;
    getAccountsUseCase: GetAccountsUseCase;
    getContactsUseCase: GetContactsUseCase;
    getReportsUseCase: GetReportsUseCase;
    getTransactionHubDataUseCase: GetTransactionHubDataUseCase;
    getStockAdjustmentsUseCase: GetStockAdjustmentsUseCase;
    getStockOpnamesUseCase: GetStockOpnamesUseCase;
    getStockMutationsUseCase: GetStockMutationsUseCase;
    getAdjustmentOptionsUseCase: GetAdjustmentOptionsUseCase;
    getRecipeOptionsUseCase: GetRecipeOptionsUseCase;

    fetchFarmacyUseCase: Pick<InstanceType<typeof fetchFarmacyUseCase>, 'execute'>;
    saveFarmacyUseCase: Pick<InstanceType<typeof saveFarmacyUseCase>, 'execute'>;
    deleteFarmacyUseCase: Pick<InstanceType<typeof deleteFarmacyUseCase>, 'execute'>;
    exportFarmacyUseCase: Pick<InstanceType<typeof exportFarmacyUseCase>, 'execute'>;
    importFarmacyUseCase: Pick<InstanceType<typeof importFarmacyUseCase>, 'execute'>;
    downloadFarmacyImportTemplateUseCase: Pick<InstanceType<typeof downloadFarmacyImportTemplateUseCase>, 'execute'>;
}

export function createFarmacyDependencies(repository = new FarmacyRepoImpl()): FarmacyVMDependencies {
    return {
        getDashboardSummaryUseCase: new GetDashboardSummaryUseCase(repository),
        getRecipesUseCase: new GetRecipesUseCase(repository),
        getRecipeDetailsUseCase: new GetRecipeDetailsUseCase(repository),
        getPatientProfileUseCase: new GetPatientProfileUseCase(repository),
        getTransactionsUseCase: new GetTransactionsUseCase(repository),
        getInventoryUseCase: new GetInventoryUseCase(repository),
        getInventoryHubDataUseCase: new GetInventoryHubDataUseCase(repository),
        getAccountsUseCase: new GetAccountsUseCase(repository),
        getContactsUseCase: new GetContactsUseCase(repository),
        getReportsUseCase: new GetReportsUseCase(repository),
        getTransactionHubDataUseCase: new GetTransactionHubDataUseCase(repository),
        getStockAdjustmentsUseCase: new GetStockAdjustmentsUseCase(repository),
        getStockOpnamesUseCase: new GetStockOpnamesUseCase(repository),
        getStockMutationsUseCase: new GetStockMutationsUseCase(repository),
        getAdjustmentOptionsUseCase: new GetAdjustmentOptionsUseCase(repository),
        getRecipeOptionsUseCase: new GetRecipeOptionsUseCase(repository),

        fetchFarmacyUseCase: new fetchFarmacyUseCase(repository),
        saveFarmacyUseCase: new saveFarmacyUseCase(repository),
        deleteFarmacyUseCase: new deleteFarmacyUseCase(repository),
        exportFarmacyUseCase: new exportFarmacyUseCase(repository),
        importFarmacyUseCase: new importFarmacyUseCase(repository),
        downloadFarmacyImportTemplateUseCase: new downloadFarmacyImportTemplateUseCase(repository),
    };
}

export const farmacyDependencies = createFarmacyDependencies();
