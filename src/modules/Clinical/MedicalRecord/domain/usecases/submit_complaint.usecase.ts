import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { ComplaintRepoImpl, ComplaintFormData } from '@modules/Clinical/MedicalRecord/data/repositories/complaint.repo_impl';

export class SubmitComplaintUseCase {
    constructor(private repository: ComplaintRepoImpl = new ComplaintRepoImpl()) {}

    async execute(data: ComplaintFormData): Promise<Either<Failure, void>> {
        return await this.repository.submit(data);
    }
}
