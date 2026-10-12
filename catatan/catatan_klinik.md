# Rencana Rebuild Sistem Klinik (Detail Eksekusi)

Tanggal update: 2026-03-09

## Tujuan Utama
1. Operasional klinik stabil harian.
2. Keuangan valid dan dapat diaudit.
3. Integrasi BPJS + SATUSEHAT aktif end-to-end.
4. Dashboard analitik terisi data nyata untuk keputusan.

## Scope Modul
1. Management user, role, permission.
2. Master data: pasien, dokter/karyawan, supplier, obat/alkes, jasa, paket.
3. Operasional: registrasi, pemeriksaan, POS, pembelian, hutang/piutang.
4. Accounting: COA, jurnal, buku besar, neraca saldo, neraca, laba rugi, arus kas.
5. Integrasi: BPJS, SATUSEHAT.
6. Reporting & analytics.
7. System setting, backup/restore, audit log.

## Asumsi Teknis
1. Semua transaksi memiliki `created_by`, `updated_by`, `reference_no`.
2. Timezone sistem konsisten Asia/Makassar.
3. Soft delete dipakai untuk master data penting.
4. Semua laporan hanya membaca data `posted`, bukan `draft`.

## Fase 1 - MVP Operasional (2-3 minggu)
Target: klinik bisa beroperasi tanpa integrasi eksternal.

### Deliverable
1. User login + role dasar (`admin`, `dokter`, `kasir`).
2. Master data inti aktif.
3. Alur pemeriksaan dan POS aktif.
4. Pembelian dan update stok otomatis.

### Breakdown Task
1. Setup:
- Profil klinik.
- Role & permission dasar.
- User + link karyawan.
2. Master data:
- Pasien/pelanggan.
- Dokter/karyawan + fee.
- Supplier.
- Obat/alkes: kode, kategori, harga beli, harga jual, min stok.
- Jasa tindakan dan paket.
3. Operasional:
- Registrasi -> pemeriksaan -> tindakan -> pemakaian obat -> selesai.
- Penjualan POS.
- Pembelian stok supplier.
4. Keuangan minimum:
- Kas/bank mutasi dari transaksi.
- Piutang/hutang sederhana.

### DoD Fase 1
1. 1 flow pemeriksaan selesai dan menghasilkan transaksi.
2. 1 flow penjualan POS selesai dan stok berkurang.
3. 1 flow pembelian selesai dan stok bertambah.
4. Tidak ada error blocker di alur harian.

## Fase 2 - Stabilitas Keuangan & Kontrol (2 minggu)
Target: laporan keuangan konsisten dan bisa audit.

### Deliverable
1. COA final + mapping akun transaksi.
2. Jurnal otomatis dan manual.
3. Laporan accounting aktif.
4. Governance closing period.

### Breakdown Task
1. Accounting core:
- COA hierarki akun.
- Mapping akun wajib: kas, persediaan, HPP, penjualan, piutang, hutang, beban.
- Jurnal otomatis dari POS, pembelian, pemeriksaan.
- Jurnal manual + reversal.
2. Laporan:
- Buku besar.
- Neraca saldo.
- Neraca.
- Laba rugi.
- Arus kas.
3. Governance:
- Audit log untuk CRUD dan posting jurnal.
- Lock/closing bulanan.
- Larangan edit jurnal posted.

### DoD Fase 2
1. Total debit = total kredit pada neraca saldo.
2. Nilai laporan konsisten dengan jurnal sumber.
3. Periode closed tidak bisa diubah tanpa jurnal penyesuaian.

## Fase 3 - Integrasi BPJS & SATUSEHAT (2-3 minggu)
Target: integrasi aktif di proses operasional.

### Deliverable
1. Konfigurasi API aman + health check.
2. BPJS minimal verifikasi peserta + SEP basic.
3. SATUSEHAT sinkron pasien, kunjungan, rekam medis.

### Breakdown Task
1. Security integrasi:
- Enkripsi credential.
- Masking secret di UI.
- Endpoint health check.
- Retry + timeout + queue.
2. BPJS:
- Verifikasi kepesertaan.
- SEP basic.
- Monitoring klaim minimal.
3. SATUSEHAT:
- Sinkron IHS pasien.
- Kirim Encounter/kunjungan.
- Kirim rekam medis FHIR minimal.

### DoD Fase 3
1. Test koneksi BPJS dan SATUSEHAT lulus dari UI.
2. Minimal 1 pasien sukses sinkron IHS.
3. Minimal 1 kunjungan dan 1 rekam medis sukses terkirim.
4. Error integrasi tampil jelas dan bisa retry.

## Fase 4 - Analitik & Optimasi (1-2 minggu)
Target: dashboard analitik menjadi alat monitoring manajemen.

### Deliverable
1. Dashboard tren pendapatan dan komposisi omzet.
2. Top dokter, tindakan terlaris, customer retention.
3. Profitability obat/alkes.
4. Peningkatan performa query laporan.

### Breakdown Task
1. Analytics:
- Revenue trend bulanan.
- Top dokter berdasarkan omzet/volume.
- Komposisi jasa vs obat.
- Customer retention (repeat/churn).
- Profitability obat/alkes.
- Tindakan terlaris.
2. Data quality:
- Definisi metrik baku.
- Rekonsiliasi angka dashboard vs laporan.
3. Optimasi:
- Index query berat.
- Cache agregat.
- Scheduler refresh data ringkasan.

### DoD Fase 4
1. Semua halaman analitik menampilkan data valid.
2. Dashboard konsisten dengan laporan detail.
3. Waktu muat laporan utama stabil di penggunaan normal.

## Timeline Mingguan (Rekomendasi)
1. Week 1: setup user-role, profil klinik, master pasien/dokter/supplier.
2. Week 2: master obat/jasa/paket, alur pemeriksaan, POS, pembelian.
3. Week 3: COA, mapping akun, jurnal otomatis.
4. Week 4: buku besar, neraca saldo, neraca, laba rugi, arus kas.
5. Week 5: integrasi BPJS (health check, verifikasi, SEP basic).
6. Week 6: integrasi SATUSEHAT (IHS, encounter, rekam medis).
7. Week 7: dashboard analitik + laporan lanjutan.
8. Week 8: hardening, QA regresi, UAT, release.

## QA Checklist Wajib
1. Validasi hak akses per role untuk setiap menu dan aksi.
2. Validasi data transaksi terhadap stok dan jurnal.
3. Rekonsiliasi laporan accounting dengan tabel transaksi.
4. Uji retry integrasi saat API eksternal timeout/error.
5. Uji backup/restore di environment staging.

## Risiko & Mitigasi
1. Risiko: kredensial bocor.
- Mitigasi: secret manager, masking, rotate key berkala.
2. Risiko: data tidak konsisten antara transaksi dan jurnal.
- Mitigasi: posting service terpusat + transaksi DB atomic.
3. Risiko: API eksternal down.
- Mitigasi: queue retry + fallback draft status.
4. Risiko: laporan lambat.
- Mitigasi: index, cache, pre-aggregation.

## Kriteria Go-Live
1. Semua skenario kritikal lulus UAT.
2. Backup restore terverifikasi.
3. Role permission final dan terkunci.
4. Tim operasional menerima SOP singkat (kasir, admin, dokter).
5. Monitoring error dan audit log aktif.

## Checklist Harian (Week 1-8)

### Week 1 - Fondasi User, Role, Profil Klinik
Day 1:
- [ ] Finalisasi role matrix (`admin`, `dokter`, `kasir`, `owner` jika perlu).
- [ ] Buat daftar permission per menu (`view/create/update/delete/export`).
- [ ] Tentukan kebijakan password (min length, reset, lockout).
Day 2:
- [ ] Implement CRUD role & permission.
- [ ] Implement guard API (403 jika unauthorized).
- [ ] Implement hide/show tombol aksi sesuai role di UI.
Day 3:
- [ ] Implement CRUD user + link karyawan.
- [ ] Validasi unik username/email/employee link.
- [ ] Tambah status aktif/nonaktif user.
Day 4:
- [ ] Implement profil perusahaan (nama, alamat, telepon, email, rekening).
- [ ] Pastikan data profil tampil di template invoice/laporan.
- [ ] Tambah audit log perubahan profil.
Day 5:
- [ ] QA akses role end-to-end.
- [ ] Perbaikan bug prioritas tinggi.
- [ ] Freeze scope Week 1.

### Week 2 - Master Data + Operasional Dasar
Day 1:
- [ ] Master pasien/pelanggan (CRUD + pencarian + filter status).
- [ ] Validasi kontak wajib (minimal nomor HP).
Day 2:
- [ ] Master karyawan/dokter (CRUD + fee default + status).
- [ ] Master supplier (CRUD + term payment).
Day 3:
- [ ] Master obat/alkes (kode unik, harga beli/jual, min stok, kategori).
- [ ] Tambah indikator stok rendah.
Day 4:
- [ ] Master jasa tindakan (tarif, durasi, fee dokter).
- [ ] Master paket tindakan (bundle jasa/obat).
Day 5:
- [ ] Uji alur registrasi -> pemeriksaan -> selesai.
- [ ] Uji alur POS sederhana.
- [ ] Uji pembelian sederhana + update stok.

### Week 3 - COA dan Jurnal Otomatis
Day 1:
- [ ] Bentuk struktur COA final (aset, kewajiban, modal, pendapatan, beban).
- [ ] Validasi parent-child COA dan kode unik.
Day 2:
- [ ] Buat halaman mapping akun transaksi wajib.
- [ ] Kunci transaksi agar tidak post jika mapping belum lengkap.
Day 3:
- [ ] Implement posting jurnal otomatis dari POS.
- [ ] Implement posting jurnal otomatis dari pembelian.
Day 4:
- [ ] Implement posting jurnal otomatis dari pemeriksaan/tindakan.
- [ ] Simpan `reference_no` ke sumber transaksi.
Day 5:
- [ ] Implement jurnal manual + validasi balance.
- [ ] Tambah reversal jurnal.
- [ ] QA rekonsiliasi jurnal vs transaksi.

### Week 4 - Laporan Accounting
Day 1:
- [ ] Buku besar per akun + filter periode.
- [ ] Saldo awal dari periode sebelumnya.
Day 2:
- [ ] Neraca saldo + validasi debit = kredit.
- [ ] Mode tampilan tunggal/perbandingan periode.
Day 3:
- [ ] Laporan neraca (Aset = Kewajiban + Modal).
- [ ] Laporan laba rugi periode.
Day 4:
- [ ] Laporan arus kas (operasional, investasi, pendanaan jika dipakai).
- [ ] Export cetak dan excel.
Day 5:
- [ ] Implement closing period bulanan.
- [ ] Lock edit jurnal posted.
- [ ] QA full accounting regression.

### Week 5 - Integrasi BPJS
Day 1:
- [ ] Simpan credential BPJS terenkripsi.
- [ ] UI masking secret + tombol reveal terbatas role.
Day 2:
- [ ] Implement BPJS client (signature, timestamp, header).
- [ ] Endpoint health check BPJS.
Day 3:
- [ ] Integrasi verifikasi kepesertaan.
- [ ] Handle error code BPJS -> pesan user-friendly.
Day 4:
- [ ] Integrasi SEP basic.
- [ ] Simpan request/response log non-sensitif.
Day 5:
- [ ] Uji end-to-end verifikasi + SEP.
- [ ] Tambah retry/timeout/circuit breaker dasar.

### Week 6 - Integrasi SATUSEHAT
Day 1:
- [ ] Simpan credential SATUSEHAT aman (OAuth client).
- [ ] Implement token fetch + refresh otomatis.
Day 2:
- [ ] Implement sinkronisasi pasien ke IHS.
- [ ] Simpan status sinkron per pasien.
Day 3:
- [ ] Implement kirim data kunjungan (Encounter).
- [ ] Implement queue + retry bila gagal kirim.
Day 4:
- [ ] Implement kirim rekam medis (FHIR minimal).
- [ ] Mapping data vital signs, diagnosis, tindakan.
Day 5:
- [ ] Uji end-to-end IHS + encounter + rekam medis.
- [ ] Finalisasi dashboard status integrasi.

### Week 7 - Laporan Lanjutan & Analitik
Day 1:
- [ ] Aktifkan laporan pemeriksaan tindakan.
- [ ] Aktifkan laporan pendapatan dokter.
Day 2:
- [ ] Aktifkan laporan obat/alkes vs jasa.
- [ ] Aktifkan laporan pengeluaran operasional.
Day 3:
- [ ] Dashboard tren pendapatan + komposisi omzet.
- [ ] Top dokter + tindakan terlaris.
Day 4:
- [ ] Customer retention + profitability obat/alkes.
- [ ] Rekonsiliasi angka dashboard vs laporan detail.
Day 5:
- [ ] Optimasi query berat (index + query plan).
- [ ] Tambah cache agregat harian.

### Week 8 - Hardening, UAT, Release
Day 1:
- [ ] Security review (credential, auth, audit log, backup).
- [ ] Perbaiki temuan kritikal.
Day 2:
- [ ] UAT bersama user operasional (admin, kasir, dokter).
- [ ] Catat gap proses dan revisi SOP.
Day 3:
- [ ] Final bugfix P1/P2.
- [ ] Regression test menyeluruh.
Day 4:
- [ ] Simulasi go-live + rollback plan.
- [ ] Verifikasi backup & restore terakhir.
Day 5:
- [ ] Go-live.
- [ ] Monitoring 24 jam pertama + hotfix jika perlu.

## Template Daily Standup (Opsional)
1. Yesterday:
- [ ] ...
2. Today:
- [ ] ...
3. Blocker:
- [ ] ...
4. Decision needed:
- [ ] ...

## Tech Stack Rekomendasi (Ringan & Stabil)
1. Backend: Laravel 11 + PHP 8.3 (FPM).
2. Database: PostgreSQL 16.
3. Cache/Queue/Session: Redis 7.
4. Frontend: Blade + Alpine.js + Tailwind CSS.
5. Web server: Nginx.
6. Container: Docker Compose (app, nginx, postgres, redis, queue, scheduler).

## Yang Harus Dibikin (Checklist Implementasi)

### A. Fondasi Project
- [ ] Struktur project Laravel modular (`app/Modules` atau per domain).
- [ ] Konfigurasi `.env.example` lengkap (PG, Redis, Queue, Mail).
- [ ] Dockerfile ringan (multi-stage) + `docker-compose.yml`.
- [ ] Healthcheck container (app/nginx/postgres/redis).
- [ ] Script bootstrap (`make up`, `make migrate`, `make seed`).

### B. Infrastruktur Aplikasi
- [ ] Auth login/logout + reset password.
- [ ] Role & Permission (RBAC granular).
- [ ] Audit log untuk aksi penting.
- [ ] Global settings (profil klinik, tema, preferensi).
- [ ] Backup/restore DB via admin + guardrail konfirmasi.

### C. Domain Master Data
- [ ] Master pasien/pelanggan.
- [ ] Master karyawan/dokter + fee.
- [ ] Master supplier.
- [ ] Master obat/alkes + stok minimum + kategori.
- [ ] Master jasa tindakan + paket.

### D. Domain Operasional
- [ ] Registrasi dan antrian pasien.
- [ ] Pemeriksaan + rekam medis.
- [ ] Pemakaian obat/alkes dari pemeriksaan.
- [ ] POS penjualan obat/jasa.
- [ ] Pembelian stok + hutang supplier.
- [ ] Piutang pasien/BPJS.

### E. Domain Keuangan
- [ ] COA hierarkis + mapping akun transaksi.
- [ ] Jurnal otomatis + jurnal manual.
- [ ] Buku besar, neraca saldo, neraca, laba rugi, arus kas.
- [ ] Closing period + reversal.

### F. Integrasi Eksternal
- [ ] Konfigurasi BPJS (credential aman, test koneksi).
- [ ] Verifikasi peserta + SEP basic.
- [ ] Konfigurasi SATUSEHAT OAuth.
- [ ] Sinkron pasien IHS + kirim encounter + kirim rekam medis.

### G. Reporting & Analytics
- [ ] Laporan POS, tindakan, pendapatan dokter, biaya operasional.
- [ ] Dashboard tren pendapatan, top dokter, komposisi omzet.
- [ ] Profitability obat/alkes dan retention pelanggan.

### H. Quality & Release
- [ ] Unit test service penting (posting jurnal, stok, auth).
- [ ] Integration test API utama.
- [ ] E2E test alur kritikal (registrasi -> pemeriksaan -> transaksi -> laporan).
- [ ] CI/CD build + test + deploy.
- [ ] SOP operasional user + runbook incident.

## Prompt ChatGPT Terbaik (Siap Copy-Paste)

Gunakan prompt ini saat mau generate arsitektur/fitur:

```text
Anda adalah Software Architect + Senior Laravel Engineer.
Saya membangun sistem manajemen klinik dengan stack:
- Laravel 11 (PHP 8.3), PostgreSQL, Redis, Nginx, Docker
- Frontend ringan: Blade + Alpine.js + Tailwind

Tujuan:
1) Sistem ringan, cepat, mudah maintain
2) RBAC ketat, audit log lengkap
3) Transaksi klinik konsisten dengan akuntansi
4) Siap integrasi BPJS & SATUSEHAT

Tolong berikan output dalam format:
1. Arsitektur folder yang disarankan (domain-based)
2. Daftar modul inti + dependency antar modul
3. Desain database (tabel utama, relasi, index penting)
4. Daftar endpoint API + contoh request/response
5. Alur bisnis end-to-end untuk:
   - registrasi -> pemeriksaan -> pemakaian obat -> POS
   - pembelian -> hutang supplier -> pembayaran
   - posting jurnal otomatis
6. Strategi keamanan (auth, authorization, secret handling, audit trail)
7. Strategi testing (unit/integration/e2e) + contoh test case prioritas tinggi
8. Rencana implementasi 8 minggu (mingguan, deliverable jelas)

Batasan:
- Hindari kompleksitas tidak perlu
- Prioritaskan MVP lebih dulu
- Berikan contoh kode seperlunya, fokus struktur dan keputusan teknis
- Gunakan bahasa Indonesia
```

## Prompt Lanjutan Per Fitur (Template)

```text
Konteks project:
- Laravel 11 + PostgreSQL + Redis + Docker
- Sudah ada modul: [tulis modul yang sudah jadi]

Tugas:
Implement fitur [nama fitur] dengan detail:
1. Migration tabel
2. Model + relasi
3. Service class (business logic)
4. Controller + FormRequest validation
5. Route
6. Blade view + Alpine interaksi ringan
7. Unit test + integration test

Aturan:
- Ikuti clean code, SOLID secukupnya
- Sertakan edge cases dan error handling
- Sertakan contoh data seed minimal
- Output bertahap dan bisa langsung saya copy
```
