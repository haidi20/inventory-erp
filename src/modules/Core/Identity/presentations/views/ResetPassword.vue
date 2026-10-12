<script setup lang="ts">
import { onMounted } from 'vue';
import { useResetPasswordVM } from '@modules/Core/Identity/presentations/viewmodels/reset_password.vm';

// Props dari Inertia â€” passed by PasswordResetController::showReset()
const props = defineProps<{
    token: string;
    email?: string;
}>();

const vm = useResetPasswordVM();

onMounted(() => {
    vm.initForm(props.token, props.email ?? '');
});
</script>

<template>
    <div class="auth-page">
        <div class="auth-card">
            <!-- Brand -->
            <div class="auth-brand">
                <div class="auth-logo">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <div class="auth-title">INVENTORY</div>
                    <div class="auth-subtitle">Buat Password Baru</div>
                </div>
            </div>

            <!-- Client-side error -->
            <div v-if="vm.clientError" class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ vm.clientError }}
            </div>

            <!-- Server validation errors from Inertia -->
            <div v-if="vm.form.errors.email || vm.form.errors.password || vm.form.errors.token" class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ vm.form.errors.email || vm.form.errors.password || vm.form.errors.token }}
            </div>

            <form @submit.prevent="vm.submit">
                <!-- Email -->
                <div class="form-field">
                    <label class="field-label">Email</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelope"></i>
                        <input
                            v-model="vm.form.email"
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="nama@email.com"
                            :class="{ 'is-invalid': vm.form.errors.email }"
                            data-testid="input-email"
                        />
                    </div>
                </div>

                <!-- Password baru -->
                <div class="form-field">
                    <label class="field-label">Password Baru</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input
                            v-model="vm.form.password"
                            :type="vm.showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            :class="{ 'is-invalid': vm.form.errors.password }"
                            data-testid="input-password"
                        />
                        <button
                            type="button"
                            class="toggle-password"
                            @click="vm.toggleShowPassword"
                            :title="vm.showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        >
                            <i :class="vm.showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>
                    <div class="field-hint">Minimal 8 karakter</div>
                </div>

                <!-- Konfirmasi password -->
                <div class="form-field">
                    <label class="field-label">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input
                            v-model="vm.form.password_confirmation"
                            :type="vm.showConfirmPassword ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            data-testid="input-password-confirmation"
                        />
                        <button
                            type="button"
                            class="toggle-password"
                            @click="vm.toggleShowConfirmPassword"
                            :title="vm.showConfirmPassword ? 'Sembunyikan' : 'Tampilkan'"
                        >
                            <i :class="vm.showConfirmPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn-submit"
                    :disabled="vm.form.processing"
                    data-testid="btn-submit"
                >
                    <i v-if="vm.form.processing" class="fa-solid fa-spinner fa-spin"></i>
                    <i v-else class="fa-solid fa-key"></i>
                    {{ vm.form.processing ? 'Menyimpan...' : 'Reset Password' }}
                </button>
            </form>

            <a href="/login" class="auth-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
            </a>
        </div>
    </div>
</template>

<style scoped>
.auth-page {
    font-family: "Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial;
    min-height: 100vh;
    background: #fff7f1;
    display: grid;
    place-items: center;
    padding: 24px;
}

.auth-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(58, 36, 19, 0.10);
}

/* â”€â”€ Brand â”€â”€ */
.auth-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}
.auth-logo {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: radial-gradient(circle at 30% 30%, #ffe6cf, #f29a4b);
    display: grid;
    place-items: center;
    color: #fff;
    font-size: 20px;
}
.auth-title { font-weight: 800; font-size: 15px; color: #3a2413; }
.auth-subtitle { font-size: 12px; color: #9a7a62; margin-top: 2px; }

/* â”€â”€ Alerts â”€â”€ */
.alert-error {
    margin-bottom: 16px;
    padding: 12px 14px;
    background: #fef9f0;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    color: #b45309;
    font-size: 13px;
    display: flex; align-items: flex-start; gap: 8px;
}

/* â”€â”€ Form â”€â”€ */
.form-field { margin-bottom: 18px; }
.field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: #9a7a62;
    margin-bottom: 7px;
}
.field-hint {
    font-size: 11px;
    color: #9a7a62;
    margin-top: 5px;
}
.input-wrap { position: relative; }
.input-wrap > i {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #9a7a62;
    font-size: 14px;
    pointer-events: none;
}
.input-wrap input {
    width: 100%;
    padding: 12px 40px 12px 40px;
    border: 1.5px solid #f1e2d3;
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    background: #f8fbfb;
    color: #3a2413;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    box-sizing: border-box;
}
.input-wrap input:focus {
    border-color: #f29a4b;
    box-shadow: 0 0 0 3px rgba(242, 154, 75, 0.15);
}
.input-wrap input.is-invalid { border-color: #f87171; }
.toggle-password {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9a7a62;
    cursor: pointer;
    padding: 4px;
    font-size: 14px;
    line-height: 1;
}
.toggle-password:hover { color: #3a2413; }

/* â”€â”€ Submit button â”€â”€ */
.btn-submit {
    width: 100%;
    margin-top: 4px;
    padding: 13px;
    background: #f29a4b;
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .2s, transform .1s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 14px rgba(242, 154, 75, 0.3);
}
.btn-submit:hover:not(:disabled) { background: #e8883a; }
.btn-submit:active:not(:disabled) { transform: scale(0.99); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

/* â”€â”€ Back link â”€â”€ */
.auth-back {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 18px;
    font-size: 13px;
    color: #f29a4b;
    text-decoration: none;
    font-weight: 600;
}
.auth-back:hover { text-decoration: underline; }
</style>

