import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';
import type { UnitFilters } from '@modules/Operational/Master/domain/models/unit.model';

export class ExportUnitsUseCase {
    constructor(private unitRepository: UnitRepository) { }

    execute(filters: UnitFilters) {
        return this.unitRepository.export(filters);
    }
}
