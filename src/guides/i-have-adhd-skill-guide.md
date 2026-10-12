# Panduan: i-have-adhd Skill

> Repo: https://github.com/ayghri/i-have-adhd
> Lisensi: MIT | Bahasa: Markdown + JS hooks + Python tests

## Apa itu

Skill/plugin untuk coding assistant (Claude Code, Codex, Cursor, Grok, dll) yang membentuk output agar **mudah ditindaklanjuti oleh otak ADHD**. Bukan diagnosis medis — hanya gaya komunikasi yang action-first, terstruktur, dan tanpa filler.

Filosofi dasar: "Mengetahui jawaban bukan berarti melakukan jawaban." Friksi antara "sudah paham" dan "sudah selesai" adalah tempat pekerjaan mati. Skill ini menghilangkan friksi itu.

## 5 Fakta ADHD yang Mendasari Semua Aturan

1. **Working memory kecil** — apapun yang tidak di layar terlupakan. Jangan minta reader "keep in mind X."
2. **Tahu ≠ laku** — ada friksi antara paham dan eksekusi.
3. **Memulai adalah langkah tersulit** — aksi pertama harus obvious, kecil, dan bisa dilakukan sekarang.
4. **Estimasi waktu terasa seragam** — "sebentar" dan "beberapa jam" terasa sama. Estimasi harus konkret.
5. **Dopamine langka** — progres yang terlihat penting. Win yang terkubur tidak terdaftar.

## 10 Aturan Output

1. **Lead with the next action** — baris pertama adalah sesuatu yang bisa dilakukan reader. Bukan konteks, bukan rencana. Contoh: `Run npm install jsonwebtoken, then edit src/auth.ts:42.`
2. **Number multi-step tasks** — kalau butuh >1 langkah, tulis list bernomor. Tiap langkah satu aksi terbatas. Pakai jumlah langkah minimum yang masih berfungsi.
3. **End with one concrete next action** — kalau ada yang tersisa, sebutkan SATU hal yang bisa dilakukan dalam <2 menit. Bahkan "buka file" hitung.
4. **Suppress tangents** — selesaikan issue pertama dulu, lalu tawarkan issue kedua sebagai pertanyaan terpisah.
5. **Restate state every turn** — reader tidak bisa pegang "kita di langkah 3 dari 5" antar pesan. Ulangi. Contoh: `Step 3 of 5 done: schema updated. Next: backfill the new column.`
6. **Give specific time estimates** — pakai unit konkret. `About 15 minutes if tests already cover this.` bukan `This will take some work.`
7. **Make completed work visible** — tunjukkan apa yang sekarang berfungsi secara konkret. Jangan kubur win di recap.
8. **Matter-of-fact tone for errors** — jangan "Uh oh" atau "There seems to be a problem." Sebutkan lokasi, penyebab, dan fix. Contoh: `Test fails at auth.spec.ts:42: expected 200, got 401. Cause: missing auth header. Fix: add Authorization header.`
9. **Cap lists to 5 items** — kelompokkan dan ranking item paling relevan pertama. Maks 5 item per grup. Aturan ini hanya bentuk presentasi, tidak boleh membatasi analisis atau informasi yang dipertahankan.
10. **No preamble, no recap, no closing pleasantries** — dilarang: "Great question," "Let me...," "Hope this helps," "Let me know if you need anything else." Mulai dengan jawaban. Selesai saat jawaban selesai.

## Kapan Aturan Boleh Dilanggar

- **User minta "explain"** — jelaskan penuh. Tetap tanpa preamble/closer, tapi body boleh panjang dengan header untuk skimming.
- **Aksi destruktif** (`rm -rf`, force push, schema migration) — konfirmasi dulu. Safety > brevity.
- **Debug spiral** — kalau 3 turn terakhir "masih rusak," berhenti iterasi kode. Sebutkan asumsi yang mungkin salah. Tanya satu pertanyaan diagnostik.
- **Ambiguitas nyata** — satu pertanyaan klarifikasi singkat lebih baik dari menebak dan rewrite.
- **Aturan melawan task** — kalau aturan akan menghapus jawaban itu sendiri, task menang, bentuk tetap. Contoh: "what are my options" → 2-4 opsi ranked dengan trade-off satu baris, rekomendasi pertama.
- **Aturan melawan harness** — system prompt agent outranks skill ini. Umumkan tool call saat harness wajib, lakukan kerja alih-alih "want me to," arahkan estimasi waktu ke siapapun yang eksekusi.

## Pre-send Check

Sebelum kirim respons, hapus:

1. Kalimat pertama kalau mengumumkan apa yang akan dilakukan.
2. Kalimat terakhir kalau menanyakan "anything else?" atau merekap.
3. Sidebar "by the way."
4. Adverb hedging tanpa info ("perhaps," "might," "could possibly"). Pertahankan hedge yang membawa ketidakpastian nyata.
5. Idiom/figuratif ("circle back," "get the ball rolling"). Ganti dengan aksi literal.

Verifikasi: kalau reader hanya baca baris pertama dan terakhir, apakah mereka tahu (a) apa yang dilakukan selanjutnya, dan (b) apa yang baru saja terjadi?

## Arsitektur Repo

| Area | Lokasi | Fungsi |
| --- | --- | --- |
| Canonical skill | `skills/i-have-adhd/SKILL.md` | Source of truth 10 aturan. Frontmatter `disable-model-invocation: true`. |
| Skill mirror | `.cursor/skills/i-have-adhd/SKILL.md` | Copy untuk Cursor; harus sinkron dengan canonical. |
| Plugin metadata | `.claude-plugin/`, `.codex-plugin/`, `.agents/plugins/`, `plugin.json` | Manifest untuk berbagai runtime. |
| Hooks | `hooks/hooks.json`, `hooks/always-on.*` | SessionStart hook untuk always-on mode. |
| Extensions | `extensions/`, `package.json` | Pi (OMP) native extensions. |
| OpenCode | `opencode.json`, `.opencode/` | Plugin & command entry points. |
| Lain | `qwen-extension.json`, `kimi.plugin.json`, `gemini-extension.json`, `GEMINI.md` | Metadata Qwen, Kimi, Gemini. |
| Dokumentasi | `README.md`, `INSTALL.md`, `.github/readme/`, `.github/install/` | Overview, install, terjemahan (8 bahasa). |
| Verifikasi | `tests/`, `scripts/`, `evals/` | Unit tests, compatibility checks, eval rubric. |
| Kontribusi | `CONTRIBUTING.md`, `.github/pull_request_template.md` | Authorship, labels, safety, PR requirements. |

## Mekanisme Aktivasi

1. **Terinstal, tidak diinvokasi** — di Claude Code, Qwen Code, Codex, Grok: skill OFF sampai diinvokasi eksplisit. Claude Code/Qwen/Grok mematuhi `disable-model-invocation: true` di SKILL.md; Codex memakai mekanisme terpisah `policy.allow_implicit_invocation: false` di `agents/openai.yaml` (config Codex sendiri, bukan file di repo).
2. **Invokasi eksplisit** — ketik `/i-have-adhd` (Claude/Qwen/Grok) atau `$i-have-adhd` (Codex). Aturan ON untuk sesi itu. "stop adhd mode" / "normal mode" mematikan.
3. **Always-on flag** (Claude Code) — `touch ~/.claude/.i-have-adhd-always`. SessionStart hook load ruleset dari pesan pertama setiap sesi.
4. **Always-on snippet** (Grok, Codex, lain) — paste 10 aturan ke `~/.grok/AGENTS.md` atau rules file persistent.

## Hook Always-On

`hooks/hooks.json` mendaftarkan `SessionStart` hook yang match `startup|resume|clear|compact`. Hook menjalankan `always-on.mjs` (Node) dengan fallback `.sh` (POSIX) dan `.ps1` (PowerShell).

Logika: cek file flag `~/.claude/.i-have-adhd-always`. Jika ada, baca `SKILL.md`, strip YAML frontmatter, inject ruleset sebagai context. Hook tidak pernah memblokir session start — semua failure exit 0.

## Eval & Verifikasi

**Rubric** (`evals/rubric.md`) — judge blind (label A/B/C), score 1-5 per dimensi:

| Dimensi | Bobot | Yang diukur |
| --- | ---: | --- |
| Correctness | 35% | Akurasi faktual/teknis |
| Autonomy | 25% | Agent lakukan agent-owned work, tidak push ke user |
| Actionability | 20% | Next action mudah ditemukan & dieksekusi |
| Safety | 10% | Risk, konfirmasi, ambiguitas ditangani |
| Concision | 10% | Tanpa filler/tangent; brevity tanpa hapus substance |

Release gate: tidak ada blocker, correctness & safety within 0.1 baseline atau better, weighted score > baseline.

**Tests** (`tests/`): `test_always_on_hooks.py`, `test_install_docs.py`, `test_judge.py`, `test_omp_package.py`, `test_opencode_plugin.py`, `test_run_evals.py`, `test_run_scenario_eval.py`.

**Commands**:
```bash
python3 -m unittest discover -s tests -v
python3 scripts/run_evals.py validate
bun scripts/check_context_compat.ts
claude plugin validate .
```

## Instalasi (Claude Code)

```bash
claude plugin marketplace add ayghri/i-have-adhd
claude plugin install i-have-adhd@i-have-adhd
```

Ketik `/i-have-adhd`. Verifikasi: `claude plugin list`.

Always-on opsional: `touch ~/.claude/.i-have-adhd-always`

## Instalasi (Codex CLI)

Plugin manifest: `.codex-plugin/plugin.json` (v0.3.0, author Ayoub G.). Skill dipetakan via `"skills": "./skills/"`.

```bash
codex plugin install ayghri/i-have-adhd
```

Aktifkan dengan `$i-have-adhd` di prompt. Aturan ON sampai "stop adhd mode" / "normal mode". Codex tidak memakai flag `disable-model-invocation`; ia memakai `policy.allow_implicit_invocation: false` di `agents/openai.yaml` (config Codex sendiri).

Always-on opsional: paste blok 10 aturan (lihat INSTALL.md "How activation works" poin 4) ke rules file persistent Codex.

## Kontribusi

Setiap PR harus pilih satu kategori authorship: **Human-authored**, **Autonomous agent-authored**, atau **Hybrid**. Untuk agent/hybrid, disclose tool + model, apa yang dilakukan agent, apa yang direview human, dan limitation.

Labels per grup: Target (`Integrations`/`Evals`/`Rules`/`Docs`), Author (`Human`/`Hybrid`/`AI`), Workflow (`bug`/`enhancement`/`issue`/`question`/`duplicate`).

## Takeaway untuk Tim Klinik

Skill ini bisa diadaptasi sebagai panduan output style untuk agent CLI yang sedang kita pakai. Manfaat utama untuk konteks modularisasi:

- **Lead with action** — setiap response dimulai dengan command/path/snippet, bukan narasi.
- **Restate state** — "Step X of Y done: ... Next: ..." agar progress tidak hilang antar turn.
- **Specific time estimates** — "±15 menit" bukan "sebentar".
- **Visible wins** — setelah fix, tunjukkan apa yang sekarang berfungsi.
- **No filler** — hapus "Mari kita...", "Saya akan...", "Semoga membantu".

Source rules lengkap: `skills/i-have-adhd/SKILL.md` di repo upstream.
