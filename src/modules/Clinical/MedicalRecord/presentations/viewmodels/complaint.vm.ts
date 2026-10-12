import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import { SubmitComplaintUseCase } from '@modules/Clinical/MedicalRecord/domain/usecases/submit_complaint.usecase';
import Swal from 'sweetalert2';

export const useComplaintVM = defineStore('complaint-vm', () => {
    const isFormOpen = ref(false);
    const form = useForm({
        subject: '',
        content: '',
        type: 'pengaduan',
        is_anonymous: false,
    });

    const submitUseCase = new SubmitComplaintUseCase();

    const openForm = () => {
        form.reset();
        form.clearErrors();
        isFormOpen.value = true;
    };

    const closeForm = () => {
        isFormOpen.value = false;
    };

    const submitForm = async () => {
        const result = await submitUseCase.execute(form.data());

        result.fold(
            (failure) => {
                if (failure instanceof ValidationFailure) {
                    form.setError(failure.errors);
                } else {
                    Swal.fire('Gagal!', failure.message, 'error');
                }
            },
            () => {
                Swal.fire('Berhasil!', 'Pengaduan berhasil dikirim.', 'success');
                closeForm();
            }
        );
    };

    return {
        isFormOpen,
        form,
        openForm,
        closeForm,
        submitForm,
    };
});
