import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';

export class ImportUnitsUseCase {
    constructor(private unitRepository: UnitRepository) { }

    execute(file: File) {
        return this.unitRepository.import(file);
    }
}
