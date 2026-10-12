Berikut adalah panduan arsitektur, aturan pembatasan, dan *system prompt* siap pakai untuk mengimplementasikan sistem audit berantai (*hash-chain audit ledger*) di Laravel tanpa mengorbankan performa aplikasi.

---

### 1. Aturan & Batasan Sistem (Engineering Rules)

Untuk menjaga latensi database tetap di bawah ambang batas kritis (< 20–30 ms) dan mencegah lonjakan I/O, tetapkan aturan berikut:

* **Aturan 1 (Granularitas Ketat):** Hanya pasang pada model yang memiliki konsekuensi finansial, kepatuhan hukum (*compliance*), atau hak otorisasi tinggi.
* **Aturan 2 (Asynchronous/Job Queue Prioritas):**
* Jangan jalankan kalkulasi SHA-256 dan *insert* baris log berantai di dalam siklus HTTP *request-response* utama pengguna jika volume transaksi tinggi.
* Masukkan ke dalam **Queue Job** (misal: Redis/database queue) dengan konfigurasi *atomic lock* atau serial worker per model agar urutan `prev_hash` tidak saling tabrak (*race condition*).


* **Aturan 3 (Selective Payload Snapshotting):**
* Dilarang menyimpan kolom bertipe blob, file base64, teks berukuran sangat besar, atau metadata sementara di kolom `payload`.
* Hanya simpan `array_diff` / perubahan nilai kolom (`$model->getChanges()`), bukan keseluruhan row.


* **Aturan 4 (Immutable DB Constraint):**
* Kolom pada tabel ledger wajib diatur `read-only` untuk akun operasional standar melalui trigger database atau pemisahan izin user DB.



---

### 2. Matriks: Fitur yang Boleh vs Dilarang

| Kategori | Fitur yang Diperbolehkan (Wajib Pakai) | Fitur yang Dilarang (Jangan Dipakai) |
| --- | --- | --- |
| **Finansial** | Transaksi kas, mutasi saldo, *invoice payout*, *refund*, perubahan status *payment gateway*. | Riwayat cek ongkir, simulasi cicilan, keranjang belanja (*cart*). |
| **Legal & Medis** | Penerbitan nomor surat resmi, verifikasi status berkas/dokumen, hasil rekam medis/MCU final. | Draft catatan harian yang masih diedit berkali-kali per menit. |
| **Keamanan** | Perubahan role/hak akses (admin escalation), reset kata sandi, API key revocation. | Log request harian, log login/logout rutin, IP tracking biasa. |
| **Data Operasional** | Perubahan status aset/inventaris bernilai tinggi (misal: transfer kepemilikan). | Notifikasi lonceng, tracking lokasi real-time (GPS), komentar, pesan chat. |

---

### 3. System Prompt (Siap Pakai untuk AI / Coding Assistant)

Salin prompt di bawah ini dan berikan kepada AI coding assistant (seperti Cursor, Claude Code, Aider, atau GitHub Copilot) saat membangun kodenya di proyek Laravel Anda:

```markdown
Anda adalah Senior Software Architect & Backend Specialist Laravel.
Tugas Anda adalah merancang dan mengimplementasikan sistem "Tamper-Evident Hash-Chained Audit Ledger" di Laravel dengan memprioritaskan performa tinggi, konkurensi aman (concurrency-safe), dan integritas data anti-manipulasi.

Patuhi aturan teknis berikut secara ketat:

1. Kriteria Pemilihan Fitur (Whitelisting):
   - Sistem HANYA boleh dipasang pada Model yang mengimplementasikan interface `TamperEvidentContract` atau menggunakan Trait `HasImmutableLedger`.
   - Dilarang keras menerapkan sistem ini secara global ke seluruh database. Model yang diizinkan terbatas pada: Transaksi finansial, status hukum/dokumen final, dan perubahan role/akses kritis.

2. Skema Database (Migration):
   - Buat tabel `immutable_ledgers` dengan kolom:
     `id` (BIGINT PK), `auditable_type` (string), `auditable_id` (bigint), `action` (string), `payload` (json), `prev_hash` (char 64), `current_hash` (char 64), `created_at` (timestamp with microsecond precision).
   - Berikan indexing komposit yang tepat pada `[auditable_type, auditable_id]` dan `created_at`.

3. Optimasi Performa & Pencegahan Bottleneck:
   - Kalkulasi hash SHA-256 dan penulisan ledger TIDAK BOLEH memblokir transaksi utama pengguna. Gunakan Laravel Queue (`ShouldQueue`) untuk memproses pencatatan ledger di latar belakang.
   - Payload JSON yang di-hash hanya boleh berisi delta perubahan (`getChanges()`), bukan keseluruhan atribut model.
   - Gunakan Cache Lock atau Queue berurutan (`onOneServer` / single-worker queue channel) untuk menghindari race condition saat mengambil `prev_hash`.

4. Logika Rantai Kriptografi:
   - Hash baris baru dihitung dari: `hash('sha256', $prevHash . json_encode($sortedPayload) . $timestampMicro)`.
   - Pastikan keys pada array payload selalu di-sort (`ksort`) sebelum di-hash agar konsisten.
   - Baris pertama (Genesis) menggunakan `str_repeat('0', 64)` sebagai `prev_hash`.

5. Deliverable yang Harus Dihasilkan:
   - Migration file untuk tabel `immutable_ledgers`.
   - Trait `HasImmutableLedger` dan Model Observer terkait.
   - Job Queue handler untuk pemrosesan asinkron berantai.
   - Artisan Command `php artisan audit:verify` untuk memvalidasi integritas seluruh rantai hash dan mendeteksi anomali/manipulasi data.

Pastikan kode bersih, mengikuti standar PSR-12, dan tidak menghasilkan query N+1.

```

Agar prompt tersebut menghasilkan implementasi yang benar-benar tahan uji (*production-ready*) tanpa menimbulkan *edge cases* tersembunyi, ada 5 aspek teknis kritis yang perlu ditambahkan:

---

### 1. Masalah Konkurensi Antrian (*Parallel Queue vs Chain Ordering*)

* **Masalah:** Jika antrian diproses oleh lebih dari 1 worker secara paralel, Worker B bisa mengambil `prev_hash` yang sama dengan Worker A, sehingga terjadi percabangan (*fork*) atau rantai rusak.
* **Optimasi di Prompt:** Instruksikan pembatasan pemrosesan antrian ke **single consumer / dedicated queue** atau pengelompokan rantai per entitas (*Tenant/Model-based hash chain*), bukan satu rantai global untuk seluruh sistem jika transaksi tinggi.

### 2. Format Serialisasi JSON yang Deterministik

* **Masalah:** `json_encode()` di PHP bisa menghasilkan representasi berbeda jika ada karakter escape slash, unicode, atau urutan float (`0.50` vs `0.5`). Ini bisa merusak verifikasi di kemudian hari.
* **Optimasi di Prompt:** Wajibkan flag kanonikal seperti `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` dan sorting bertingkat (rekursif) untuk *nested array*.

### 3. Proteksi Rollback Transaksi DB (*DB Transaction vs Queue Dispatch*)

* **Masalah:** Jika event model memicu dispatch job di dalam `DB::transaction`, lalu query utama gagal (*rollback*), job ledger tetap terlanjur dikirim ke queue dan mencatat perubahan palsu.
* **Optimasi di Prompt:** Gunakan event lifecycle bawaan Laravel `afterCommit()` saat mendispatch job.

### 4. Strategi Partisi & Purging Masa Lalu (*Scalability / Partitioning*)

* **Masalah:** Tabel ledger yang terus bertambah jutaan baris akan memperlambat verifikasi integritas penuh (`audit:verify`).
* **Optimasi di Prompt:** Tambahkan instruksi **Checkpointing / Periodic Snapshot** (misalnya hash ringkasan mingguan/bulanan) agar verifikasi tidak selalu mengulang dari baris pertama sejak tahun awal sistem berdiri.

---

### Versi Prompt yang Disempurnakan (Tingkat Produksi)

Salin prompt hasil penyempurnaan ini:

```markdown
Anda adalah Principal Software Engineer & Security Architect Laravel.
Tugas Anda adalah merancang dan mengimplementasikan sistem "Tamper-Evident Hash-Chained Audit Ledger" di Laravel dengan standar keamanan tinggi, tahan terhadap race-condition, dan tidak membebani transaksi HTTP utama.

Patuhi spesifikasi teknis berikut secara ketat:

1. Cakupan & Batasan Fitur:
   - Pasang HANYA pada model dengan implikasi hukum, keuangan, atau hak akses tinggi melalui Trait `HasImmutableLedger`. Dilarang global listener.
   - Hanya catat delta perubahan (`$model->getChanges()`), hindari logging atribut statis yang tidak berubah atau data biner/blob.

2. Skema Database & Model:
   - Buat migration tabel `immutable_ledgers`:
     - `id` (BIGINT UNSIGNED, AUTO_INCREMENT, PRIMARY KEY)
     - `auditable_type` (VARCHAR(150))
     - `auditable_id` (VARCHAR(64))
     - `action` (VARCHAR(30)) -- CREATED, UPDATED, DELETED
     - `payload` (JSON)
     - `prev_hash` (CHAR(64))
     - `current_hash` (CHAR(64))
     - `created_at` (TIMESTAMP(6)) -- Presisi microsecond
   - Indeks: Komposit `INDEX (auditable_type, auditable_id, id)` dan `INDEX (created_at)`.

3. Eksekusi Asinkron & Penanganan Konkurensi:
   - Dispatch job HANYA setelah transaksi database utama berhasil commit: gunakan `$this->dispatch(...)->afterCommit()`.
   - Gunakan dedicated single-worker queue (misal: queue connection `audit-ledger`) ATAU implementasikan `Atomic Lock` Redis/Cache (`Cache::lock`) saat menghitung dan menulis hash berantai agar terhindar dari race condition antar-worker.

4. Serialisasi Deterministik & Standar Hashing:
   - Lakukan sorting rekursif pada keys array payload (`ksort` rekursif).
   - Serialisasi JSON wajib memakai opsi: `json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)`.
   - Hash baris dihitung dari: `hash('sha256', $prevHash . $canonicalJson . $timestampMicro)`.
   - Baris pertama (Genesis) bernilai `str_repeat('0', 64)`.

5. Verification Command & Checkpointing:
   - Buat command `php artisan audit:verify {--from-id=} {--to-id=}`.
   - Sediakan mekanisme validasi kontinuitas:
     a. Apakah `current_hash` baris N-1 persis sama dengan `prev_hash` baris N?
     b. Apakah rekalkulasi hash baris N sesuai dengan `current_hash` yang tersimpan?
   - Return exit code 0 jika valid, dan exit code 1 dengan detail ID manipulasi jika rantai terputus.

Berikan implementasi kode yang modular, lengkap dengan dokumentasi ringkas, dan mengikuti standar PSR-12.

```
