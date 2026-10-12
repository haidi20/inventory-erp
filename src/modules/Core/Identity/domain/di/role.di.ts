import { RoleRepoImpl } from '@modules/Core/Identity/data/repositories/role.repo_impl';
import { FetchRolesUseCase } from '@modules/Core/Identity/domain/usecases/fetch-roles.usecase';
import { SaveRoleUseCase } from '@modules/Core/Identity/domain/usecases/save-role.usecase';
import { DeleteRoleUseCase } from '@modules/Core/Identity/domain/usecases/delete-role.usecase';
import { ExportRolesUseCase } from '@modules/Core/Identity/domain/usecases/export-roles.usecase';
import { ImportRolesUseCase } from '@modules/Core/Identity/domain/usecases/import-roles.usecase';
import { DownloadRoleImportTemplateUseCase } from '@modules/Core/Identity/domain/usecases/download-role-import-template.usecase';
 
export interface RoleVMDependencies {
    fetchRolesUseCase: Pick<ReturnType<typeof createRoleDependencies>['fetchRolesUseCase'], 'execute'>;
    saveRoleUseCase: Pick<ReturnType<typeof createRoleDependencies>['saveRoleUseCase'], 'execute'>;
    deleteRoleUseCase: Pick<ReturnType<typeof createRoleDependencies>['deleteRoleUseCase'], 'execute'>;
    exportRolesUseCase: Pick<ReturnType<typeof createRoleDependencies>['exportRolesUseCase'], 'execute'>;
    importRolesUseCase: Pick<ReturnType<typeof createRoleDependencies>['importRolesUseCase'], 'execute'>;
    downloadRoleImportTemplateUseCase: Pick<ReturnType<typeof createRoleDependencies>['downloadRoleImportTemplateUseCase'], 'execute'>;
}

export function createRoleDependencies(repository = new RoleRepoImpl()) {
    return {
        fetchRolesUseCase: new FetchRolesUseCase(repository),
        saveRoleUseCase: new SaveRoleUseCase(repository),
        deleteRoleUseCase: new DeleteRoleUseCase(repository),
        exportRolesUseCase: new ExportRolesUseCase(repository),
        importRolesUseCase: new ImportRolesUseCase(repository),
        downloadRoleImportTemplateUseCase: new DownloadRoleImportTemplateUseCase(repository),
    };
}

export const {
    fetchRolesUseCase,
    saveRoleUseCase,
    deleteRoleUseCase,
    exportRolesUseCase,
    importRolesUseCase,
    downloadRoleImportTemplateUseCase,
} = createRoleDependencies();
