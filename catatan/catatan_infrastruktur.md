# Catatan Infrastruktur — Klinik Starter

Tanggal update: 2026-03-15

---

## Gambaran Arsitektur

```
Internet / Browser
        │
        ▼ :8080
  ┌─────────────┐
  │   Gateway   │  ← Go 1.23
  │  (rate limit│
  │  JWT, proxy)│
  └──────┬──────┘
         │
    ┌────┴──────────────────┐
    │                       │
    ▼                       ▼
┌────────┐          ┌──────────────┐
│ Nginx  │          │ Integration  │  ← Go 1.23
│ :80    │          │ Service :9001│
└───┬────┘          └──────┬───────┘
    │                      │
    ▼                      ├── BPJS API (eksternal)
┌────────┐                 ├── SATUSEHAT API (eksternal)
│Laravel │                 └── MinIO (internal)
│PHP-FPM │
└───┬────┘
    │
    ├── PostgreSQL :5432
    ├── Redis :6379
    └── MinIO :9000 (S3 API)
```

---

## Container & Port

| Container | Image | Port Host | Fungsi |
|---|---|---|---|
| `klinik_gateway` | Go binary (Alpine) | **8080** → 9000 | API Gateway — entry point utama |
| `klinik_integration` | Go binary (Alpine) | internal :9001 | BPJS, SATUSEHAT, MinIO storage |
| `klinik_app` | PHP 8.3 FPM Alpine | internal :9000 | Laravel backend |
| `klinik_nginx` | Nginx 1.27 Alpine | internal :80 | Static files + PHP-FPM proxy |
| `klinik_queue` | PHP 8.3 FPM Alpine | — | Laravel queue worker |
| `klinik_scheduler` | PHP 8.3 FPM Alpine | — | Laravel schedule worker |
| `postgres_remote` (external) | PostgreSQL 16 | **5434** → 5432 | Database utama (db_shared) |
| `klinik_redis` | Redis 7 Alpine | **6379** | Cache, session, queue |
| `minio_storage` (external) | MinIO latest | **9000** (API), **9001** (Console) | Object storage PDF & gambar |

---

## Stack Teknologi

### Backend — Laravel
- **Laravel 11** + PHP 8.3 FPM
- ORM: Eloquent
- Queue: Redis (driver `redis`)
- Session: Redis
- Cache: Redis
- Auth: Laravel Sanctum (API token) / session (web)

### Gateway & Integration — Go
- **Go 1.23**
- Router: [gin-gonic/gin](https://github.com/gin-gonic/gin)
- JWT: [golang-jwt/jwt v5](https://github.com/golang-jwt/jwt)
- Redis client: [redis/go-redis v9](https://github.com/redis/go-redis)
- MinIO client: [minio/minio-go v7](https://github.com/minio/minio-go)

### Database
- **PostgreSQL 16** (Alpine) — external (`/root/Sites-Hasbi/postgres-remote`)
- Timezone: `Asia/Makassar`
- Network: `db_shared`

### Cache / Queue
- **Redis 7** (Alpine)
- Append-only persistence aktif
- Volume persisten: `redis_data`

### Object Storage
- **MinIO** (S3-compatible) — external (`/root/Sites-Hasbi/minio`)
- Network: `db_shared`
- Bucket tunggal: `klinik-starter` dengan folder `klinik-invoices/`, `klinik-images/`, `klinik-reports/`, `klinik-temp/`

### Web Server
- **Nginx 1.27** (Alpine)
- Konfigurasi: [docker/nginx/default.conf](../docker/nginx/default.conf)
- FastCGI ke PHP-FPM container `app:9000`

---

## Alur Request

```
1. Request masuk ke Gateway (:8080)
2. Gateway cek rate limit (Redis INCR per IP)
3. Gateway extract JWT → inject X-User-Id, X-User-Role ke header
4. Routing:
   - /api/integration/*  → Integration Service (:9001)
   - /*                  → Nginx (:80) → Laravel PHP-FPM (:9000)
5. Laravel proses bisnis logic
6. Untuk file → Laravel call Integration Service /api/storage/upload
7. Integration Service simpan ke MinIO
```

---

## Async Queue

Laravel push job ke Redis → Go Integration Service consume lewat `klinik:integration:jobs`:

| Job Type | Trigger | Handler |
|---|---|---|
| `bpjs.verify` | Pemeriksaan pasien BPJS | Integration → BPJS API |
| `bpjs.sep` | Pendaftaran SEP | Integration → BPJS API |
| `satusehat.sync_patient` | Pasien baru / update | Integration → SATUSEHAT IHS |
| `satusehat.encounter` | Kunjungan selesai | Integration → SATUSEHAT Encounter |
| `satusehat.record` | Rekam medis posted | Integration → SATUSEHAT FHIR |

Retry otomatis hingga 3x dengan delay bertahap (5s, 10s, 15s).

---

## Environment Variables Utama

```env
# App
APP_ENV=local
APP_PORT=8080
JWT_SECRET=change-me-in-production

# Database
DB_DATABASE=klinik-starter
DB_USERNAME=klinik-starter
DB_PASSWORD=klinik-starter
DB_PORT_FORWARD=5434

# Redis
REDIS_PASSWORD=
REDIS_PORT_FORWARD=6379

# MinIO
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin123
MINIO_API_PORT=9000
MINIO_CONSOLE_PORT=9001
MINIO_BUCKET=klinik-starter

# BPJS (isi saat Fase 3)
BPJS_CONS_ID=
BPJS_SECRET_KEY=
BPJS_USER_KEY=
BPJS_BASE_URL=https://apijkn-dev.bpjs-kesehatan.go.id

# SATUSEHAT (isi saat Fase 3)
SATUSEHAT_CLIENT_ID=
SATUSEHAT_CLIENT_SECRET=
SATUSEHAT_AUTH_URL=https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1/accesstoken
SATUSEHAT_BASE_URL=https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1
```

---

## Perintah Operasional

```bash
make up              # Build + jalankan semua container
make down            # Stop semua container
make logs            # Log realtime semua container
make ps              # Status container

make migrate         # Jalankan migration Laravel
make seed            # Jalankan seeder Laravel
make artisan cmd=... # Jalankan artisan command apapun

make go-tidy         # Update go.sum semua module Go
make go-build        # Build binary Go secara lokal

make healthcheck     # Ping semua service
make backup          # Backup PostgreSQL ke folder backups/

make minio-console   # Info URL & kredensial MinIO Console
```

---

## Volume & Persistensi Data

| Volume | Isi |
|---|---|
| `pg_data` | Data PostgreSQL (external postgres-remote) |
| `redis_data` | Data Redis (append-only) |
| `minio_data` | File PDF & gambar di MinIO (external) |

---

## Keamanan

| Aspek | Implementasi |
|---|---|
| Rate limiting | Gateway: 100 req/60s per IP via Redis |
| Auth API | JWT Bearer token, validasi di Gateway |
| Auth Web | Laravel session (cookie) |
| Kredensial eksternal | Env variable, tidak di-commit ke repo |
| File access | MinIO presigned URL (TTL 1–1440 menit) |
| Audit trail | Semua aksi penting dicatat di `audit_logs` |
| DB backup | `make backup` → pg_dump + gzip → folder `backups/` |

---

## Healthcheck

Semua container punya healthcheck bawaan. Untuk cek manual:

```bash
make healthcheck
# atau langsung:
curl http://localhost:8080/gateway/health   # Gateway
curl http://localhost:9101/minio/health/live # MinIO
```
