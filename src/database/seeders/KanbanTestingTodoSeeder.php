<?php

namespace Database\Seeders;

use App\Modules\Core\Platform\Models\KanbanCard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Kartu permasalahan + solusi (perbaikan code) — papan kanban /kanban.
 *
 * Distilasi temuan testing 2026-10-06 (laporan .md dihapus sesuai instruksi;
 * yang tersisa hanya item yang butuh perbaikan code, masing-masing dengan
 * permasalahan + solusi di judul kartu).
 *
 * Nomor unik TEST-### (wajib unique, jangan duplikat nomor).
 * Terakhir dipakai: TEST-011 — kartu baru mulai dari TEST-012.
 * Hanya item yang butuh perbaikan code (permasalahan + solusi).
 * Idempotent: judul sama tidak dibuat dua kali.
 *
 * Jalankan:
 *   php artisan db:seed --class=KanbanTestingTodoSeeder
 */
class KanbanTestingTodoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ($this->cards() as $card) {
            KanbanCard::query()->firstOrCreate(
                ['title' => $card['title']],
                [
                    'title' => $card['title'],
                    'label' => $card['label'],
                    'assignee' => $card['assignee'] ?: null,
                    'column_name' => $card['column'],
                    'position' => (int) KanbanCard::where('column_name', $card['column'])->max('position') + 1,
                ],
            );
        }
    }

    /**
     * @return array<int, array{title: string, label: string, assignee: string, column: string}>
     */
    private function cards(): array
    {
        return [
            // ── TODO — permasalahan + solusi (butuh perbaikan code) ─────────
            [
                'title' => 'TEST-001 MASALAH: register 500 — duplikat insert user_companies (UNIQUE constraint) | SOLUSI: ganti UserCompany::create() dengan firstOrCreate — SUDAH DIPERBAIKI di working tree oleh Forge (juga assignDefaultBranch) + test regresi OwnerRegistrationAssignmentTest — tinggal review',
                'label' => 'bug', 'assignee' => 'Forge', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-002 MASALAH: SuperadminSeeder V2 error "no column branch_uuid" — insert kolom legacy users yang sudah di-drop a22e93043 | SOLUSI: SUDAH DIPERBAIKI di working tree oleh Forge (hapus 2 baris legacy, pivot tetap) — tinggal review',
                'label' => 'bug', 'assignee' => 'Forge', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-003 MASALAH: kanban_cards MySQL live masih datetime, model tulis unix integer (dateFormat U) → tambah kartu via UI error di live | SOLUSI: migration ALTER TABLE created_at/updated_at → bigInteger nullable',
                'label' => 'bug', 'assignee' => 'Haidi', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-004 MASALAH: 3 set test PHPUnit remember-me duplikatif (AuthControllerTest 6, LoginRememberMeTest 4, RememberMeLoginTest 4) | SOLUSI: pertahankan AuthControllerTest sebagai kanonik, hapus 2 file lain',
                'label' => 'bug', 'assignee' => 'Haidi', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-005 MASALAH: ProductImportService melanggar boundary R1 — import Model langsung (Product, Unit) | SOLUSI: inject ProductRepositoryInterface + UnitRepositoryInterface via query() — SUDAH DIPERBAIKI di working tree, tinggal review',
                'label' => 'bug', 'assignee' => 'Haidi', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-006 MASALAH: checkbox "Ingat saya" target klik 18px < 24px WCAG 2.5.8 AA + fokus tidak konsisten | SOLUSI: .remember padding 4px + :focus-visible — SUDAH DIPERBAIKI di Login.vue, tinggal review',
                'label' => 'optimalisasi', 'assignee' => 'Sora', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-007 MASALAH: komit bd37819f5 memicu 3 dari 5 regresi (hapus User::branch, UserEntity::can ubah filter menu, ProductImportService R1) | SOLUSI: review komit tsb + wajib full suite sebelum commit fitur besar',
                'label' => 'bug', 'assignee' => 'Haidi', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-008 MASALAH: test generated TDD (DatabaseModelRelationshipsTest) tanpa generator di repo & tidak ikut update saat skema berubah | SOLUSI: simpan generator di scripts/ agar bisa di-regenerate, atau ubah ke test manual',
                'label' => 'optimalisasi', 'assignee' => 'Haidi', 'column' => 'todo',
            ],

            // ── TODO — distilasi testings/*.md (dihapus 15:44, isi pindah ke sini) ──
            [
                'title' => 'TEST-009 MASALAH: RuntimeUserSeeder V1 juga insert branch_uuid/company_uuid legacy users (bug serupa TEST-002) | SOLUSI: SUDAH DIPERBAIKI di working tree (hapus 2 baris, pivot tetap) — tinggal review',
                'label' => 'bug', 'assignee' => 'Forge', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-010 MASALAH: test "sesi hilang" berisiko false-positive — SessionManager meng-cache Store antar request dalam satu test method (T-1 qa-findings) | SOLUSI: audit test lain yang butuh sesi fresh, pakai pola helper simulateLostSession (forgetDrivers + forgetGuards) dari RememberMeLoginTest',
                'label' => 'bug', 'assignee' => 'Forge', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-011 MASALAH: file sampah ter-commit ke git — tmp-product-groups.png, tmp-products.png, tmp-repro.mjs, null, _chk.py, _read.py, sh.exe.stackdump, tmp_previous_routes.ts (+ stub public/_kanban_seed_tmp.php dan tombstone testings/*.md dari sesi agent) | SOLUSI: git rm semua file sampah + tambahkan pattern (tmp_*, *.stackdump, null) ke .gitignore',
                'label' => 'optimalisasi', 'assignee' => 'Forge', 'column' => 'todo',
            ],
            [
                'title' => 'TEST-012 MASALAH: seeder kanban dapat menambah kartu duplikat saat CI atau deployment dijalankan ulang | SOLUSI: pakai firstOrCreate berdasarkan judul kartu — SUDAH DIPERBAIKI di working tree oleh Vault, tinggal review',
                'label' => 'optimalisasi', 'assignee' => 'Vault', 'column' => 'todo',
            ],
        ];
    }
}
