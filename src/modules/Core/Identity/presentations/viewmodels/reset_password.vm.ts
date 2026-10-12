import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ResetPasswordUseCase } from '@modules/Core/Identity/domain/usecases/reset_password.usecase';

const useCase = new ResetPasswordUseCase();

export const useResetPasswordVM = defineStore('reset-password-vm', () => {
    const clientError = ref<string | null>(null);
    const showPassword = ref(false);
    const showConfirmPassword = ref(false);

    const form = useForm({
        token: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const initForm = (token: string, email: string) => {
        form.token = token;
        form.email = email;
    };

    const toggleShowPassword = () => {
        showPassword.value = !showPassword.value;
    };

    const toggleShowConfirmPassword = () => {
        showConfirmPassword.value = !showConfirmPassword.value;
    };

    const submit = () => {
        clientError.value = null;

        const validationError = useCase.validate(form.password, form.password_confirmation);
        if (validationError) {
            clientError.value = validationError;
            return;
        }

        form.post('/reset-password', {
            onFinish: () => {
                // Inertia handles redirect + flash automatically
            },
        });
    };

    return {
        form,
        clientError,
        showPassword,
        showConfirmPassword,
        initForm,
        toggleShowPassword,
        toggleShowConfirmPassword,
        submit,
    };
});
