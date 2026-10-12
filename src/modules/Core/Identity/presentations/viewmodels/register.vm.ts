import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { RegisterFormData } from './register.state';

export const useRegisterVM = defineStore('register-vm', () => {
    const form = useForm<RegisterFormData>({
        name: '',
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
        company_name: '',
        timezone: 'Asia/Makassar',
    });

    const showPassword = ref(false);
    const showConfirmation = ref(false);

    const submit = () => form.post('/register');

    const togglePasswordVisibility = () => {
        showPassword.value = !showPassword.value;
    };

    const toggleConfirmationVisibility = () => {
        showConfirmation.value = !showConfirmation.value;
    };

    return {
        form,
        showPassword,
        showConfirmation,
        submit,
        togglePasswordVisibility,
        toggleConfirmationVisibility,
    };
});
