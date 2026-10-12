<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useLoginVM } from '@modules/Core/Identity/presentations/viewmodels/login.vm';

// Props dari Inertia
const props = defineProps<{
    status?: string;
    errors?: Record<string, string | string[]>;
}>();

const vm = useLoginVM();

// Inertia mengirim error bag sebagai array per field; jangan dirender mentah
// (akan tampil sebagai [ "pesan" ]) -- gabungkan jadi kalimat tunggal.
const firstError = computed(() => {
    const value = Object.values(props.errors ?? {})[0];
    if (Array.isArray(value)) {
        return value.join(' ');
    }
    return value ?? '';
});

onMounted(() => {
    vm.initCsrf();
});

</script>

<template>
    <div class="login-page">

        <!-- Centered login card -->
        <div class="panel-right">
            <div class="login-box">
                <div class="login-brand">
                    <div class="login-brand-icon">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div class="login-brand-text">Inventory</div>
                </div>
                <div class="login-greeting">Selamat Datang!</div>
                <div class="login-sub">Masuk ke akun Anda untuk melanjutkan</div>

                <!-- Flash status -->
                <div v-if="props.status" class="alert-success">{{ props.status }}</div>

                <!-- Validation/auth errors -->
                <div v-if="firstError" class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ firstError }}
                </div>
                <div v-if="vm.clientError" class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ vm.clientError }}
                </div>

                <form method="POST" action="/login" autocomplete="off" :ref="vm.setFormElement"
                    @submit="vm.handleSubmit">
                    <input type="hidden" name="_token" :value="vm.csrfToken" />
                    <input type="hidden" name="role" :value="vm.selectedRole" />

                    <div class="field">
                        <label>Email atau Username</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input v-model="vm.email" type="text" name="email" required autocomplete="off"
                                placeholder="nama@email.com atau username" />
                        </div>
                    </div>

                    <div class="field">
                        <label>Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <button type="button" class="password-toggle"
                                :aria-label="vm.showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                @click="vm.togglePasswordVisibility">
                                <i :class="vm.showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                            <input v-model="vm.password" :type="vm.showPassword ? 'text' : 'password'" name="password"
                                required autocomplete="new-password" placeholder="••••••••" />
                        </div>
                    </div>

                    <div class="row">
                        <label class="remember">
                            <input v-model="vm.remember" type="checkbox" name="remember" />
                            Ingat saya
                        </label>
                        <a href="/forgot-password" class="forgot">Lupa password?</a>
                    </div>

                    <button class="btn-login" type="submit" :disabled="vm.isLoading">
                        <i v-if="vm.isLoading" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-right-to-bracket"></i>
                        {{ vm.isLoading ? 'Memverifikasi...' : 'Masuk' }}
                    </button>
                </form>

                <!-- Register link dinonaktifkan.
                <a href="/register" class="register-link">
                    Belum punya akun? Daftar di sini
                </a>
                -->

                <!-- Demo accounts button (dimatikan -- jangan tampilkan kredensial di UI) -->
                <!--
                <button type="button" class="btn-demo" @click="vm.openInfoModal">
                    <i class="fa-solid fa-circle-info"></i> Lihat Akun Demo (Role)
                </button>
                -->

                <!-- Role selector modal -->
                <Teleport to="body">
                    <div class="modal" :class="{ show: vm.isRoleModalOpen }" @click.self="vm.closeRoleModal">
                        <div class="modal-content" style="max-width: 400px;">
                            <div class="modal-header">
                                <div class="modal-title">
                                    <i class="fa-solid fa-user-tag"></i> Pilih Role Anda
                                </div>
                                <button type="button" class="close-modal" aria-label="Tutup" @click="vm.closeRoleModal">&times;</button>
                            </div>
                            <div class="login-sub" style="margin-bottom: 20px;">
                                Akun Anda memiliki lebih dari satu role. Silakan pilih role untuk melanjutkan.
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <button v-for="role in vm.roles" :key="role.name" type="button" class="btn-role"
                                    @click="vm.selectRoleFromForm(role.name)">
                                    <div class="role-icon">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </div>
                                    <div class="role-info">
                                        <div class="role-label">{{ role.label }}</div>
                                        <div class="role-name">Role: {{ role.name }}</div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right role-arrow"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </Teleport>

                <!-- Demo accounts modal (dimatikan) -->
                <!--
                <Teleport to="body">
                    <div class="modal" :class="{ show: vm.isInfoModalOpen }" @click.self="vm.closeInfoModal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="modal-title">
                                    <i class="fa-solid fa-users"></i> Daftar Akun Demo
                                </div>
                                <button type="button" class="close-modal" aria-label="Tutup" @click="vm.closeInfoModal">&times;</button>
                            </div>
                            <div style="overflow-x: auto;">
                                <table class="modal-table">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>Password</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="account in vm.demoAccounts" :key="account.email">
                                            <td>{{ account.role }}</td>
                                            <td><code>{{ account.email }}</code></td>
                                            <td><code>{{ account.password }}</code></td>
                                            <td>
                                                <button type="button" class="btn-use"
                                                    @click="vm.useDemoAccount(account.email, account.password)">
                                                    Gunakan
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </Teleport>
                -->
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.login-page {
    /* Scoped Variables to avoid global conflicts */
    --primary: #3B76EF;
    --primary-dark: #2563d6;
    --bg: #ffffff;
    --text: #1e2a3a;
    --muted: #7a8999;
    --line: #e2e8f0;
    --shadow: 0 20px 60px rgba(59, 118, 239, 0.12);

    font-family: "Plus Jakarta Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr;
    margin: 0;
    padding: 0;
}

/* ── Centered login card ── */
.panel-right {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    background:
        radial-gradient(560px 320px at 8% 0%, rgba(59, 118, 239, 0.09), transparent 64%),
        radial-gradient(520px 360px at 100% 100%, rgba(59, 118, 239, 0.11), transparent 62%),
        var(--bg);
}

.login-box {
    position: relative;
    width: 100%;
    max-width: 430px;
    padding: 32px;
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 22px;
    box-shadow: 0 20px 60px rgba(37, 99, 235, 0.12);
    overflow: hidden;
}

.login-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 26px;
}

.login-brand-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--primary);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 17px;
    box-shadow: 0 8px 20px rgba(59, 118, 239, 0.25);
}

.login-brand-text {
    font-size: 20px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.4px;
}

.login-greeting {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    text-align: center;
    margin-bottom: 6px;
}

.login-sub {
    font-size: 14px;
    color: var(--muted);
    text-align: center;
    margin-bottom: 28px;
}

/* ── Fields ── */
.field {
    margin-bottom: 18px;
}

.field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 7px;
}

.input-wrap {
    position: relative;
}

.input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 14px;
}

.input-wrap input {
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    background: #fff;
    color: var(--text);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}

.input-wrap input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 118, 239, 0.12);
}

.password-wrap input {
    padding-right: 44px;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 6px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: translateY(-50%);
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--muted);
    cursor: pointer;
}

.password-toggle:hover {
    color: var(--text);
    background: #f1f5f9;
}

/* ── Row (remember + forgot) ── */
.row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.remember {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--muted);
    cursor: pointer;
    /* Target klik >= 24px tinggi (WCAG 2.5.8 AA) */
    padding: 4px 4px 4px 0;
}

.remember input[type=checkbox] {
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
    cursor: pointer;
}

.remember input[type=checkbox]:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

.forgot {
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.forgot:hover {
    text-decoration: underline;
}

.register-link {
    display: block;
    margin-top: 28px;
    width: 100%;
    text-align: center;
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 700;
}

.register-link:hover {
    text-decoration: underline;
}

/* ── Buttons ── */
.btn-login {
    width: 100%;
    padding: 13px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .2s, transform .1s, box-shadow .2s;
    box-shadow: 0 4px 16px rgba(59, 118, 239, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-login:hover:not(:disabled) {
    background: var(--primary-dark);
    box-shadow: 0 6px 20px rgba(59, 118, 239, 0.4);
}

.btn-login:active:not(:disabled) {
    transform: scale(0.99);
}

.btn-login:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-demo {
    margin-top: 16px;
    width: 100%;
    padding: 12px;
    background: #e0e7ff;
    color: #3730a3;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
}

.btn-demo:hover {
    background: #c7d2fe;
}

/* ── Alerts ── */
.alert-error {
    margin-bottom: 16px;
    padding: 12px 14px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #b91c1c;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-success {
    margin-bottom: 16px;
    padding: 12px 14px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    color: #15803d;
    font-size: 13px;
}

/* ── Modal ── */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    width: 90%;
    max-width: 650px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    position: relative;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1.5px solid var(--line);
    padding-bottom: 12px;
}

.modal-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.close-modal {
    cursor: pointer;
    font-size: 20px;
    color: var(--muted);
    line-height: 1;
    /* Reset tampilan <button> agar visual identik dengan elemen span sebelumnya */
    background: none;
    border: 0;
    padding: 0;
    font-family: inherit;
}

/* ── Modal table ── */
.modal-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.modal-table th,
.modal-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--line);
}

.modal-table th {
    background: #f8fafc;
    font-weight: 700;
    color: var(--muted);
}

.modal-table td code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    color: var(--primary-dark);
}

.btn-use {
    padding: 5px 10px;
    font-size: 11px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 700;
    font-family: inherit;
}

.btn-use:hover {
    background: var(--primary-dark);
}

/* ── Role selector ── */
.btn-role {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 14px 18px;
    background: #f8fafc;
    color: #1e293b;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    font-family: inherit;
    transition: border-color .15s, background .15s;
    text-align: left;
}

.btn-role:hover {
    border-color: var(--primary);
    background: #f1f7ff;
}

.btn-role:hover .role-arrow {
    color: var(--primary);
}

.role-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(59, 118, 239, 0.1);
    color: var(--primary);
    display: grid;
    place-items: center;
    font-size: 14px;
    flex-shrink: 0;
}

.role-info {
    flex: 1;
}

.role-label {
    font-weight: 700;
    font-size: 14px;
}

.role-name {
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-arrow {
    margin-left: auto;
    font-size: 12px;
    color: #cbd5e1;
    transition: color .15s;
}

/* ── Responsive ── */
@media (max-width: 520px) {
    .panel-right {
        padding: 20px 14px;
    }

    .login-box {
        padding: 24px 18px;
        border-radius: 18px;
    }
}
</style>
