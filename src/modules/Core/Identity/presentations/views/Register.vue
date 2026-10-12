<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useRegisterVM } from '@modules/Core/Identity/presentations/viewmodels/register.vm';

const props = defineProps<{
    timezones?: string[];
}>();

const vm = useRegisterVM();
</script>

<template>

    <Head title="Registrasi" />

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-logo">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <div class="auth-title">Inventory</div>
                    <div class="auth-subtitle">Registrasi Perusahaan</div>
                </div>
            </div>

            <p class="auth-desc">
                Buat akun pemilik dan perusahaan baru. Cabang utama akan dibuat otomatis.
            </p>

            <div v-if="Object.keys(vm.form.errors).length" class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ Object.values(vm.form.errors)[0] }}
            </div>

            <form @submit.prevent="vm.submit">
                <div class="form-field">
                    <label for="company_name">Nama Perusahaan</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-building"></i>
                        <input id="company_name" v-model="vm.form.company_name" type="text" name="company_name" required
                            maxlength="255" autocomplete="organization" placeholder="Klinik Utama Berkat Medika"
                            :class="{ 'is-invalid': vm.form.errors.company_name }" data-testid="input-company-name" />
                    </div>
                </div>

                <div class="form-field">
                    <label for="timezone">Zona Waktu</label>
                    <div class="input-wrap select-wrap">
                        <i class="fa-solid fa-earth-asia"></i>
                        <select id="timezone" v-model="vm.form.timezone" name="timezone" required
                            :class="{ 'is-invalid': vm.form.errors.timezone }" data-testid="select-timezone">
                            <option v-for="timezone in props.timezones ?? []" :key="timezone" :value="timezone">
                                {{ timezone }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-field">
                    <label for="name">Nama Lengkap</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user"></i>
                        <input id="name" v-model="vm.form.name" type="text" name="name" required maxlength="255"
                            autocomplete="name" placeholder="Nama Anda" :class="{ 'is-invalid': vm.form.errors.name }"
                            data-testid="input-name" />
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-field">
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input id="email" v-model="vm.form.email" type="email" name="email" required maxlength="255"
                                autocomplete="email" placeholder="nama@email.com"
                                :class="{ 'is-invalid': vm.form.errors.email }" data-testid="input-email" />
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-at"></i>
                            <input id="username" v-model="vm.form.username" type="text" name="username" required
                                maxlength="255" autocomplete="username" placeholder="username"
                                :class="{ 'is-invalid': vm.form.errors.username }" data-testid="input-username" />
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-field">
                        <label for="password">Password</label>
                        <div class="input-wrap password-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input id="password" v-model="vm.form.password" :type="vm.showPassword ? 'text' : 'password'"
                                name="password" required minlength="8" autocomplete="new-password"
                                placeholder="Minimal 8 karakter" :class="{ 'is-invalid': vm.form.errors.password }"
                                data-testid="input-password" />
                            <button type="button" class="password-toggle"
                                :aria-label="vm.showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                @click="vm.togglePasswordVisibility">
                                <i :class="vm.showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="input-wrap password-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input id="password_confirmation" v-model="vm.form.password_confirmation"
                                :type="vm.showConfirmation ? 'text' : 'password'" name="password_confirmation" required
                                minlength="8" autocomplete="new-password" placeholder="Ulangi password"
                                :class="{ 'is-invalid': vm.form.errors.password_confirmation }"
                                data-testid="input-password-confirmation" />
                            <button type="button" class="password-toggle"
                                :aria-label="vm.showConfirmation ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'"
                                @click="vm.toggleConfirmationVisibility">
                                <i :class="vm.showConfirmation ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" :disabled="vm.form.processing" data-testid="btn-register">
                    <i v-if="vm.form.processing" class="fa-solid fa-spinner fa-spin"></i>
                    <i v-else class="fa-solid fa-user-plus"></i>
                    {{ vm.form.processing ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                </button>
            </form>

            <a href="/login" class="auth-back">
                <i class="fa-solid fa-arrow-left"></i> Sudah punya akun?
            </a>
        </div>
    </div>
</template>

<style scoped>
.auth-page {
    font-family: "Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial;
    min-height: 100vh;
    background: linear-gradient(135deg, #eff6ff, #fff);
    display: grid;
    place-items: center;
    padding: 32px 20px;
}

.auth-card {
    width: 100%;
    max-width: 680px;
    background: #fff;
    border-radius: 20px;
    padding: 34px;
    box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
}

.auth-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.auth-logo {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(59, 118, 239, 0.12);
    display: grid;
    place-items: center;
    color: var(--primary, #3b76ef);
    font-size: 20px;
}

.auth-title {
    font-weight: 800;
    font-size: 15px;
    color: #1e293b;
}

.auth-subtitle {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.auth-desc {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.65;
    margin-bottom: 22px;
}

.alert-error {
    margin-bottom: 18px;
    padding: 12px 14px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #b91c1c;
    font-size: 13px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.form-field {
    margin-bottom: 18px;
}

.form-field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 7px;
}

.grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.input-wrap {
    position: relative;
}

.input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
}

.input-wrap input,
.input-wrap select {
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    background: #f8fafc;
    color: #1e293b;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}

.input-wrap select {
    appearance: none;
    padding-right: 38px;
}

.select-wrap::after {
    content: "\f078";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 11px;
    pointer-events: none;
}

.input-wrap input:focus,
.input-wrap select:focus {
    border-color: var(--primary, #3b76ef);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(59, 118, 239, 0.12);
}

.input-wrap .is-invalid {
    border-color: #f87171;
}

.password-wrap input {
    padding-right: 44px;
}

.password-toggle {
    position: absolute;
    right: 6px;
    top: 50%;
    width: 32px;
    height: 32px;
    transform: translateY(-50%);
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
}

.password-toggle:hover {
    color: #475569;
    background: #f1f5f9;
}

.btn-submit {
    width: 100%;
    margin-top: 6px;
    padding: 13px;
    background: var(--primary, #3b76ef);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(59, 118, 239, 0.25);
}

.btn-submit:hover:not(:disabled) {
    background: var(--primary-dark, #2563eb);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.auth-back {
    margin-top: 20px;
    font-size: 13px;
    color: var(--primary, #3b76ef);
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.auth-back:hover {
    text-decoration: underline;
}

@media (max-width: 640px) {
    .auth-card {
        padding: 26px;
    }

    .grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>
