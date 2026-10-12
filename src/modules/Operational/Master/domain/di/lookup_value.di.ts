import { LookupValueRepoImpl } from '@modules/Operational/Master/data/repositories/lookup_value.repo_impl';
import { FetchLookupValues } from '@modules/Operational/Master/domain/usecases/fetch-lookup-values.usecase';
import { SaveLookupValue } from '@modules/Operational/Master/domain/usecases/save-lookup-value.usecase';
import { DeleteLookupValue } from '@modules/Operational/Master/domain/usecases/delete-lookup-value.usecase';
import { ExportLookupValues } from '@modules/Operational/Master/domain/usecases/export-lookup-values.usecase';
import { ImportLookupValues } from '@modules/Operational/Master/domain/usecases/import-lookup-values.usecase';
import { DownloadLookupValueTemplate } from '@modules/Operational/Master/domain/usecases/download-lookup-value-template.usecase';

export interface LookupValueVMDependencies {
    fetchLookupValues: Pick<ReturnType<typeof createLookupValueDependencies>['fetchLookupValues'], 'execute'>;
    saveLookupValue: Pick<ReturnType<typeof createLookupValueDependencies>['saveLookupValue'], 'execute'>;
    deleteLookupValue: Pick<ReturnType<typeof createLookupValueDependencies>['deleteLookupValue'], 'execute'>;
    exportLookupValues: Pick<ReturnType<typeof createLookupValueDependencies>['exportLookupValues'], 'execute'>;
    importLookupValues: Pick<ReturnType<typeof createLookupValueDependencies>['importLookupValues'], 'execute'>;
    downloadTemplate: Pick<ReturnType<typeof createLookupValueDependencies>['downloadTemplate'], 'execute'>;
}

export function createLookupValueDependencies(repository = new LookupValueRepoImpl()) {
    return {
        fetchLookupValues: new FetchLookupValues(repository),
        saveLookupValue: new SaveLookupValue(repository),
        deleteLookupValue: new DeleteLookupValue(repository),
        exportLookupValues: new ExportLookupValues(repository),
        importLookupValues: new ImportLookupValues(repository),
        downloadTemplate: new DownloadLookupValueTemplate(repository),
    };
}

export const {
    fetchLookupValues,
    saveLookupValue,
    deleteLookupValue,
    exportLookupValues,
    importLookupValues,
    downloadTemplate,
} = createLookupValueDependencies();
