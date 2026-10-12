## Konsep Service Cronjob Backup Data dan File untuk Aplikasi Klinik

### 🎯 Frekuensi Backup yang Sesuai Industri

Berdasarkan standar industri dan praktik terbaik, untuk aplikasi klinik dengan skala menengah-tinggi, rekomendasi frekuensi backup adalah:

| Jenis Backup | Frekuensi | Waktu Eksekusi | Tujuan |
|--------------|-----------|----------------|--------|
| **Database Backup** | **Daily (setiap hari)** | 01:00 - 03:00 WIB | Data transaksi pasien, rekam medis, pembayaran |
| **File Backup** (upload, dokumen, gambar) | **Daily** | 02:00 - 04:00 WIB | File bukti, foto, dokumen pasien |
| **Full System Backup** | **Weekly (1x minggu)** | Minggu, 02:00 WIB | Backup lengkap aplikasi + konfigurasi |
| **Long-term Archive** | **Monthly (1x bulan)** | Tanggal 1, 03:00 WIB | Arsip untuk kepatuhan dan audit |

**Pertimbangan:**
- Backup **daily** adalah standar minimum untuk sistem yang memiliki data transaksi harian
- **Midnight (tengah malam)** adalah waktu paling umum karena beban sistem rendah
- Industri dengan data sensitif (klinik) dianjurkan RPO (Recovery Point Objective) maksimal 24 jam
- **1-2 bulan sekali tidak cukup** untuk klinik operasional karena risiko kehilangan data terlalu besar

---

### 🏗️ Arsitektur Konsep Backup

```text
┌─────────────────────────────────────────────────────────────────┐
│                         SERVER PRODUKSI                          │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │   MySQL     │  │   Upload    │  │  Config     │              │
│  │  Database   │  │   Files     │  │   Files     │              │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘              │
└─────────┼────────────────┼────────────────┼─────────────────────┘
          │                │                │
          ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CRONJOB (Daily @ 01:00)                       │
├─────────────────────────────────────────────────────────────────┤
│  backup.sh script menjalankan:                                   │
│  1. mysqldump --single-transaction --quick database_name        │
│  2. tar -czf uploads.tar.gz /path/to/uploads/                   │
│  3. rsync -avz /etc/app/config/ /backup/config/                 │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                   LOCAL BACKUP DIRECTORY                         │
│  /backup/daily/db_2026-01-15.sql.gz                             │
│  /backup/daily/files_2026-01-15.tar.gz                          │
│  /backup/weekly/full_2026-W02.tar.gz                            │
│  /backup/monthly/archive_2026-01.tar.gz                         │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                    (rsync/scp ke remote)
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                    REMOTE BACKUP SERVER                          │
│  - Backup lokasi berbeda (disaster recovery)                     │
│  - Minimal 7-30 hari retensi             │
│  - Otomatis hapus backup lama                                    │
└─────────────────────────────────────────────────────────────────┘
```

---

### ⏰ Cron Schedule yang Tepat

Berdasarkan standar industri:

| Frekuensi | Cron Expression | Keterangan |
|-----------|-----------------|-------------|
| **Daily Database** | `0 1 * * *` | Setiap hari jam 01:00 |
| **Daily Files** | `0 2 * * *` | Setiap hari jam 02:00 |
| **Weekly Full** | `0 2 * * 0` | Setiap Minggu jam 02:00 |
| **Monthly Archive** | `0 3 1 * *` | Tanggal 1 setiap bulan jam 03:00 |
| **Cleanup Old** | `0 4 * * *` | Hapus backup > retention period |

**Mengapa jam 01:00?**
- Beban sistem paling rendah
- Tidak mengganggu operasional klinik (pasien sedikit)
- Cukup waktu sebelum backup file berikutnya

---

### 📦 Komponen yang Harus di Backup

| Komponen | Path/Lokasi | Metode Backup | Prioritas |
|----------|-------------|---------------|-----------|
| **Database MySQL** | - | `mysqldump --single-transaction` | 🔴 **Kritis** |
| **Uploaded Files** | `storage/app/public/` | `tar` atau `rsync` | 🔴 **Kritis** |
| **Application Logs** | `storage/logs/` | `tar` (opsional) | 🟡 Medium |
| **Config Files** | `.env`, `config/*` | `tar` | 🟢 Low |
| **SSL Certificates** | `/etc/ssl/` | `tar` | 🔴 **Kritis** |

---

### 🔄 Strategi Rotasi dan Retensi

Berdasarkan praktik industri:

| Jenis Backup | Retention Local | Retention Remote | Keterangan |
|--------------|-----------------|------------------|-------------|
| **Daily** | 7 hari | 30 hari | Untuk restore cepat |
| **Weekly** | 4 minggu | 12 minggu | Untuk recovery mingguan |
| **Monthly** | 12 bulan | 24 bulan | Untuk audit dan arsip |

**Contoh implementasi cleanup di script:**
```bash
# Hapus backup daily lebih dari 7 hari
find /backup/daily -name "*.sql.gz" -mtime +7 -delete
find /backup/daily -name "*.tar.gz" -mtime +7 -delete

# Hapus backup weekly lebih dari 30 hari
find /backup/weekly -name "*.tar.gz" -mtime +30 -delete
```

---

### 🏥 Kebutuhan Spesifik Klinik

| Skenario | Rekomendasi Backup | Alasan |
|----------|-------------------|--------|
| **Klinik 1 cabang, < 100 pasien/hari** | Daily backup + weekly full | Data masih manageable |
| **Klinik multi-cabang** | Daily + real-time replication antar cabang | Data tersebar, risiko lebih tinggi |
| **Wajib rekam medis elektronik** | Daily + monthly archive | Kepatuhan regulasi penyimpanan rekam medis minimal 10 tahun |
| **BPJS terintegrasi** | Daily + encrypted backup | Data BPJS sensitif, wajib keamanan tinggi |

---

### ✅ Best Practices untuk Backup Database (MySQL)

1. **Gunakan `--single-transaction`** untuk backup tanpa lock table
2. **Kompres hasil backup** dengan gzip (hemat storage 70-90%)
3. **Encrypt backup** jika berisi data sensitif pasien
4. **Simpan di lokasi berbeda** (minimal 1 remote server)
5. **Verifikasi backup secara berkala** dengan test restore

**Contoh command backup database:**
```bash
mysqldump --single-transaction --quick --skip-lock-tables \
  --user=backup_user --password=$DB_PASSWORD \
  --databases klinik_db | gzip > /backup/daily/db_$(date +%Y%m%d).sql.gz
```

---

### 💎 Kesimpulan

| Pertanyaan | Jawaban |
|------------|---------|
| **Frekuensi backup?** | **Daily (setiap hari)** untuk database dan file, bukan 1-2 bulan sekali |
| **Waktu terbaik?** | **Tengah malam (01:00 - 03:00)** saat beban sistem rendah |
| **Berapa lama retensi?** | Daily: 7 hari, Weekly: 30 hari, Monthly: 12-24 bulan |
| **Apakah cukup 2x bulan?** | **Tidak cukup** untuk klinik operasional dengan data harian. Risiko kehilangan data terlalu besar. |

**Rekomendasi untuk aplikasi klinik Anda:**
- ✅ **Daily database backup** (setiap hari jam 01:00)
- ✅ **Daily file backup** (setiap hari jam 02:00)
- ✅ **Remote backup** ke server berbeda (disaster recovery)
- ✅ **Monthly archive** untuk kepatuhan regulasi rekam medis
- ✅ **Monitoring alert** jika backup gagal

Implementasi ini sesuai dengan standar industri untuk sistem dengan data sensitif dan transaksi harian seperti klinik.

**Ya, backup secara KESELURUHAN (full backup)**, bukan berdasarkan created date. Ini adalah standar industri untuk disaster recovery.

---

## 📦 Full Backup vs Incremental Backup

| Jenis Backup | Deskripsi | Cocok untuk |
|--------------|-----------|-------------|
| **Full Backup** | Backup SEMUA data dan file, tanpa filter tanggal | ✅ **Disaster recovery**, restore total sistem |
| **Incremental Backup** | Hanya backup data yang berubah sejak backup terakhir | ⚠️ Melengkapi full backup, hemat storage |
| **Differential Backup** | Hanya backup data yang berubah sejak full backup terakhir | ⚠️ Alternatif incremental |

---

## 🎯 Untuk Klinik: Gunakan Full Backup + Incremental

Praktik terbaik di industri (termasuk klinik) adalah **kombinasi**:

```text
Minggu 1: FULL BACKUP (semua data + file)
Minggu 2: INCREMENTAL (hanya perubahan)
Minggu 3: INCREMENTAL (hanya perubahan)
Minggu 4: INCREMENTAL (hanya perubahan)
Minggu 5: FULL BACKUP (cycle baru)
```

**Tapi untuk cronjob sederhana, FULL BACKUP setiap hari juga tidak masalah** jika:
- Ukuran database tidak terlalu besar (< 5 GB)
- Storage mencukupi (daily backup 7 hari = 35 GB)
- Server memiliki resource yang cukup

---

## 🔄 Backup yang Dimaksud dalam Panduan

Dari panduan yang Anda berikan, backup yang dimaksud adalah **FULL BACKUP** dari:

| Komponen | Backup Seluruhnya? | Keterangan |
|----------|-------------------|------------|
| **Database** | ✅ **Ya, seluruh tabel** | Semua data pasien, transaksi, dll |
| **Uploaded Files** | ✅ **Ya, seluruh folder upload** | Semua file yang pernah diupload |
| **Config Files** | ✅ **Ya** | .env, config, dll |
| **Logs** | ⚠️ Opsional | Bisa di-backup sesuai kebutuhan |

**Bukan** berdasarkan `created_at` atau filter tanggal tertentu.

---

## 📝 Contoh Command Full Backup Database

```bash
# FULL BACKUP: semua database (bukan berdasarkan tanggal)
mysqldump --single-transaction --all-databases | gzip > /backup/full_db_$(date +%Y%m%d).sql.gz

# ATAU untuk database tertentu
mysqldump --single-transaction klinik_db | gzip > /backup/klinik_db_$(date +%Y%m%d).sql.gz
```

**Perhatikan:** Tidak ada `--where "created_at > ..."` karena ini FULL backup.

---

## 📁 Contoh Command Full Backup File

```bash
# FULL BACKUP: seluruh folder upload (bukan file terbaru saja)
tar -czf /backup/uploads_$(date +%Y%m%d).tar.gz /var/www/klinik/storage/app/public/

# FULL BACKUP: seluruh folder aplikasi (opsional)
tar -czf /backup/app_$(date +%Y%m%d).tar.gz /var/www/klinik/
```

---

## ⚖️ Kapan Perlu Filter Berdasarkan Created Date?

| Skenario | Perlu Filter Tanggal? | Keterangan |
|----------|----------------------|-------------|
| **Disaster Recovery** | ❌ **TIDAK** | Butuh semua data untuk restore total |
| **Migrasi data lama** | ✅ **YA** | Misal pindahkan data pasien tahun 2020 ke archive |
| **Backup harian (incremental)** | ✅ **YA** | Hanya data yang berubah hari ini |
| **Export laporan bulanan** | ✅ **YA** | Hanya data dalam rentang tanggal |

---

## 🏥 Rekomendasi untuk Aplikasi Klinik

```text
┌─────────────────────────────────────────────────────────────┐
│                    STRATEGI BACKUP KLINIK                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Daily (01:00)  →  FULL BACKUP database + files            │
│                     (seluruh data, tanpa filter tanggal)    │
│                                                              │
│  Weekly (Minggu, 02:00) → FULL BACKUP aplikasi + config    │
│                                                              │
│  Monthly (Tanggal 1) → Archive ke cold storage             │
│                         (untuk kepatuhan 10 tahun)          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

**Mengapa FULL BACKUP setiap hari?**
- Data klinik sangat kritis (nyawa pasien, rekam medis)
- Restore harus cepat tanpa perlu merangkai incremental backup
- Ukuran database klinik biasanya < 2 GB (masih manageable)

---

## 💎 Kesimpulan

| Pertanyaan | Jawaban |
|------------|---------|
| **Backup berdasarkan created date?** | ❌ **TIDAK** untuk full backup |
| **Backup seluruh data?** | ✅ **YA** untuk disaster recovery |
| **Kapan pakai filter tanggal?** | Hanya untuk incremental backup atau export laporan |
| **Standar industri klinik?** | **Full backup daily + incremental per jam** untuk data kritis |

**Jadi untuk cronjob backup yang dimaksud dalam panduan: FULL BACKUP (semua data, semua file, tanpa filter created date).**