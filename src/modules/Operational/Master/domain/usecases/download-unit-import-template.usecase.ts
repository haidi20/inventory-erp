import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';

export class DownloadUnitImportTemplateUseCase {
    constructor(private unitRepository: UnitRepository) { }

    execute() {
        return this.unitRepository.downloadImportTemplate();
    }
}
