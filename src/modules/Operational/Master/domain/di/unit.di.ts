import { UnitRepoImpl } from '@modules/Operational/Master/data/repositories/unit.repo_impl';
import { FetchUnitsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-units.usecase';
import { SaveUnitUseCase } from '@modules/Operational/Master/domain/usecases/save-unit.usecase';
import { DeleteUnitUseCase } from '@modules/Operational/Master/domain/usecases/delete-unit.usecase';
import { ExportUnitsUseCase } from '@modules/Operational/Master/domain/usecases/export-units.usecase';
import { ImportUnitsUseCase } from '@modules/Operational/Master/domain/usecases/import-units.usecase';
import { DownloadUnitImportTemplateUseCase } from '@modules/Operational/Master/domain/usecases/download-unit-import-template.usecase';

export interface UnitVMDependencies {
    fetchUnitsUseCase: Pick<ReturnType<typeof createUnitDependencies>['fetchUnitsUseCase'], 'execute'>;
    saveUnitUseCase: Pick<ReturnType<typeof createUnitDependencies>['saveUnitUseCase'], 'execute'>;
    deleteUnitUseCase: Pick<ReturnType<typeof createUnitDependencies>['deleteUnitUseCase'], 'execute'>;
    exportUnitsUseCase: Pick<ReturnType<typeof createUnitDependencies>['exportUnitsUseCase'], 'execute'>;
    importUnitsUseCase: Pick<ReturnType<typeof createUnitDependencies>['importUnitsUseCase'], 'execute'>;
    downloadUnitImportTemplateUseCase: Pick<ReturnType<typeof createUnitDependencies>['downloadUnitImportTemplateUseCase'], 'execute'>;
}

export function createUnitDependencies(repository = new UnitRepoImpl()) {
    return {
        fetchUnitsUseCase: new FetchUnitsUseCase(repository),
        saveUnitUseCase: new SaveUnitUseCase(repository),
        deleteUnitUseCase: new DeleteUnitUseCase(repository),
        exportUnitsUseCase: new ExportUnitsUseCase(repository),
        importUnitsUseCase: new ImportUnitsUseCase(repository),
        downloadUnitImportTemplateUseCase: new DownloadUnitImportTemplateUseCase(repository),
    };
}

export const {
    fetchUnitsUseCase,
    saveUnitUseCase,
    deleteUnitUseCase,
    exportUnitsUseCase,
    importUnitsUseCase,
    downloadUnitImportTemplateUseCase,
} = createUnitDependencies();
