import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';
import type { UnitFormData } from '@modules/Operational/Master/domain/models/unit.model';

export class SaveUnitUseCase {
    constructor(private unitRepository: UnitRepository) { }

    execute(data: UnitFormData) {
        return this.unitRepository.save(data);
    }
}
