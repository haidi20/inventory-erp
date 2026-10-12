import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';

export class DeleteUnitUseCase {
    constructor(private unitRepository: UnitRepository) { }

    execute(uuid: string) {
        return this.unitRepository.delete(uuid);
    }
}
