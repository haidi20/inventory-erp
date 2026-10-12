import { UserRepoImpl } from '@modules/Core/Identity/data/repositories/user.repo_impl';
import { FetchUsersUseCase } from '@modules/Core/Identity/domain/usecases/fetch-users.usecase';
import { SaveUserUseCase } from '@modules/Core/Identity/domain/usecases/save-user.usecase';
import { DeleteUserUseCase } from '@modules/Core/Identity/domain/usecases/delete-user.usecase';
import { ExportUsersUseCase } from '@modules/Core/Identity/domain/usecases/export-users.usecase';
import { ImportUsersUseCase } from '@modules/Core/Identity/domain/usecases/import-users.usecase';
import { DownloadUserImportTemplateUseCase } from '@modules/Core/Identity/domain/usecases/download-user-import-template.usecase';
 
export interface UserVMDependencies {
    fetchUsersUseCase: Pick<ReturnType<typeof createUserDependencies>['fetchUsersUseCase'], 'execute'>;
    saveUserUseCase: Pick<ReturnType<typeof createUserDependencies>['saveUserUseCase'], 'execute'>;
    deleteUserUseCase: Pick<ReturnType<typeof createUserDependencies>['deleteUserUseCase'], 'execute'>;
    exportUsersUseCase: Pick<ReturnType<typeof createUserDependencies>['exportUsersUseCase'], 'execute'>;
    importUsersUseCase: Pick<ReturnType<typeof createUserDependencies>['importUsersUseCase'], 'execute'>;
    downloadUserImportTemplateUseCase: Pick<ReturnType<typeof createUserDependencies>['downloadUserImportTemplateUseCase'], 'execute'>;
}

export function createUserDependencies(repository = new UserRepoImpl()) {
    return {
        fetchUsersUseCase: new FetchUsersUseCase(repository),
        saveUserUseCase: new SaveUserUseCase(repository),
        deleteUserUseCase: new DeleteUserUseCase(repository),
        exportUsersUseCase: new ExportUsersUseCase(repository),
        importUsersUseCase: new ImportUsersUseCase(repository),
        downloadUserImportTemplateUseCase: new DownloadUserImportTemplateUseCase(repository),
    };
}

export const {
    fetchUsersUseCase,
    saveUserUseCase,
    deleteUserUseCase,
    exportUsersUseCase,
    importUsersUseCase,
    downloadUserImportTemplateUseCase,
} = createUserDependencies();
