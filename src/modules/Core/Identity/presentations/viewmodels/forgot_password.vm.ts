import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ForgotPasswordUseCase } from '@modules/Core/Identity/domain/usecases/forgot_password.usecase';

const useCase = new ForgotPasswordUseCase();

export const useForgotPasswordVM = defineStore('forgot-password-vm', () => {
    const clientError = ref<string | null>(null);

    const form = useForm({
        email: '',
    });

    const submit = () => {
        clientError.value = null;

        const validationError = useCase.validate(form.email);
        if (validationError) {
            clientError.value = validationError;
            return;
        }

        form.post('/forgot-password', {
            onFinish: () => {
                // Inertia handles redirect + flash automatically
            },
        });
    };

    const reset = () => {
        form.reset();
        clientError.value = null;
    };

    return {
        form,
        clientError,
        submit,
        reset,
    };
});
