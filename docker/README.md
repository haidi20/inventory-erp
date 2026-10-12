# Docker Monorepo Setup (Dev/Prod)

Dokumen ini menjelaskan setup Docker untuk monorepo Klinik Starter dengan dua lingkungan: **Dev** dan **Prod**.

- `be` = Gin API
- `fe` = Vue frontend
- `postgres` = Database utama
- `redis` = Cache, queue, dan session
- `jenkins` = CI/CD local
- `netdata` = Monitoring lokal
- `pgadmin` = Database management

## Service Yang Disiapkan

- `postgres` (pgsql)
- `redis` (klinik_redis)
- `be_dev` / `be_prod`
- `fe_dev` / `fe_prod`
- `nginx` (Gateway)
- `jenkins`
- `pgadmin`

## Langkah Menjalankan

1. Salin file environment:
   `copy .env.example .env`

2. Gunakan helper utama:
   Jalankan [run.bat](d:/projects/klinik-starter/docker/run.bat) dan pilih opsi **1** (Jalankan semua container).

3. Akses via Gateway (Nginx):
   - **Frontend Dev**: `http://127.0.0.1:8088/dev/`
   - **Frontend Prod**: `http://127.0.0.1:8088/prod/`
   - **API Dev**: `http://127.0.0.1:8088/dev/api/health`
   - **API Prod**: `http://127.0.0.1:8088/prod/api/health`
   - **Jenkins**: `http://127.0.0.1:8088/cicd/`
   - **Netdata**: `http://127.0.0.1:19999/`
   - **PgAdmin**: `http://localhost:5050/`

## Catatan Penting

- **Mode Dev**: Frontend di-mount dari host (`/app`) untuk kenyamanan developer.
- **Mode Prod**: Frontend dan Backend di-bite ke dalam image untuk performa dan stabilitas.
- **Nama Service**: Kini menggunakan terminologi `_dev` dan `_prod` (menggantikan `main`).
- **Nginx Config**: Menggunakan [dev-prod.conf](file:///D:/projects/klinik-starter/docker/nginx/dev-prod.conf).
- **Laragon Safe**: Gateway Docker lokal dipindah ke port `8088`, MySQL ke `13306/13307`, dan Postgres ke `15432/15433` agar tidak bentrok dengan Laragon.

## Lokasi Data dan Persistensi

Penyimpanan data penting menggunakan **Named volume Docker** (postgres_data, redis_data, jenkins_data, dll). Data tetap aman meskipun container di-rebuild, selama volume tidak dihapus secara manual.

---
*Dokumen ini diperbarui untuk mencakup lingkungan Prod yang baru.*
