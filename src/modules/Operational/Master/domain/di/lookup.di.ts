import { LookupRepoImpl } from '@modules/Operational/Master/data/repositories/lookup.repo_impl';
import { FetchLookupsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-lookups.usecase';
import { SaveLookupUseCase } from '@modules/Operational/Master/domain/usecases/save-lookup.usecase';
import { DeleteLookupUseCase } from '@modules/Operational/Master/domain/usecases/delete-lookup.usecase';
import { ExportLookupsUseCase } from '@modules/Operational/Master/domain/usecases/export-lookups.usecase';
import { ImportLookupsUseCase } from '@modules/Operational/Master/domain/usecases/import-lookups.usecase';
import { DownloadLookupImportTemplateUseCase } from '@modules/Operational/Master/domain/usecases/download-lookup-import-template.usecase';

export interface LookupVMDependencies {
    fetchLookupsUseCase: Pick<ReturnType<typeof createLookupDependencies>['fetchLookupsUseCase'], 'execute'>;
    saveLookupUseCase: Pick<ReturnType<typeof createLookupDependencies>['saveLookupUseCase'], 'execute'>;
    deleteLookupUseCase: Pick<ReturnType<typeof createLookupDependencies>['deleteLookupUseCase'], 'execute'>;
    exportLookupsUseCase: Pick<ReturnType<typeof createLookupDependencies>['exportLookupsUseCase'], 'execute'>;
    importLookupsUseCase: Pick<ReturnType<typeof createLookupDependencies>['importLookupsUseCase'], 'execute'>;
    downloadLookupImportTemplateUseCase: Pick<ReturnType<typeof createLookupDependencies>['downloadLookupImportTemplateUseCase'], 'execute'>;
}

export function createLookupDependencies(repository = new LookupRepoImpl()) {
    return {
        fetchLookupsUseCase: new FetchLookupsUseCase(repository),
        saveLookupUseCase: new SaveLookupUseCase(repository),
        deleteLookupUseCase: new DeleteLookupUseCase(repository),
        exportLookupsUseCase: new ExportLookupsUseCase(repository),
        importLookupsUseCase: new ImportLookupsUseCase(repository),
        downloadLookupImportTemplateUseCase: new DownloadLookupImportTemplateUseCase(repository),
    };
}

export const {
    fetchLookupsUseCase,
    saveLookupUseCase,
    deleteLookupUseCase,
    exportLookupsUseCase,
    importLookupsUseCase,
    downloadLookupImportTemplateUseCase,
} = createLookupDependencies();
