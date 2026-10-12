# Deployment Checklist (Production)

## Prerequisites (Code Level -- SELESAI)

| # | Task | Status |
|---|------|--------|
| 1 | Redis driver config (.env.example) | 17.39 |
| 2 | Compound index (11 tabel) | 17.39 |
| 3 | Cache lookup (LookupService) | 17.39 |
| 4 | Reverb install + Echo channel auth | 17.40-17.41 |
| 5 | Broadcast events (5) + SatusehatSyncJob | 17.45 |
| 6 | Monitoring (Pulse + Telescope) | 17.43 |
| 7 | E2E + k6 load test | 17.47 |

## Server Setup (Bukan Code)

### 1. PHP 8.3 + OPcache + JIT

- Install PHP 8.3 + opcache + redis extension
- opcache.enable=1, opcache.jit=tracing, opcache.jit_buffer_size=128M
- composer.json pin ^8.3 saat deploy

### 2. Redis Server

- Install redis-server, maxmemory 256mb, allkeys-lru
- .env production: CACHE_STORE=redis, SESSION_DRIVER=redis, QUEUE_CONNECTION=redis, REDIS_CLIENT=phpredis

### 3. Supervisor (queue:work)

- Config siap pakai: `deploy/supervisor/klinik-worker.conf`
- command=php artisan queue:work redis --sleep=3 --tries=3 --backoff=10 --max-time=3600
- autostart=true, autorestart=true

### 4. Reverb (production)

- Config siap pakai: `deploy/supervisor/klinik-reverb.conf`
- php artisan reverb:start --host=0.0.0.0 --port=8080 via Supervisor

### 5. Nginx WebSocket Proxy

- Config siap pakai: `deploy/nginx/klinik-websocket.conf`
- location /app/: proxy_pass 127.0.0.1:8080, upgrade headers

### 6. Pulse Monitoring (Server)

- `pulse:check --once` sudah terdaftar di scheduler (`routes/console.php`,
  everyMinute, withoutOverlapping -- 17.74). Wajib pasang cron Laravel:
  `* * * * * php artisan schedule:run >> /dev/null 2>&1`
- Alternatif daemon: `deploy/supervisor/klinik-pulse.conf` (pilih salah
  satu, jangan keduanya).
- Ingest default pakai DB utama; jika beban tinggi, pindah ke Redis via
  `.env`: `PULSE_INGEST_DRIVER=redis` (predis sudah ter-install).

## Verification After Deploy

- php artisan migrate --force
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache
- supervisorctl start klinik-worker
- supervisorctl start klinik-reverb
- crontab: `* * * * * php /path/artisan schedule:run` (scheduler Pulse)
- curl http://localhost:8000/up

## Performance Gate (Pre-Launch -- WAJIB)

Bukti terukur bahwa 200+ user konkuren aman -- jalankan k6 HTTP gate
`loadtest/http-load-test.js` (lihat `loadtest/README.md`) terhadap
server production-like (OPcache on, Redis cache/queue, Supervisor
worker jalan).

### Budget (Section 24.1 _plan.md)

| Metrik | Wajib (200 VU) | Catatan (500 VU) |
|---|---|---|
| P95 http_req_duration | < 200ms | > 300ms = trigger Octane (Fase 1.5) |
| P99 http_req_duration | < 500ms | alerting threshold |
| http_req_failed | < 1% | - |
| listing_error_rate | < 1% | metrik custom script |
| ws_connecting (WS test) | p(95) < 2000ms | loadtest/websocket-load-test.js |

### Langkah Gate

- [ ] 1. Deploy production-like (PHP 8.3 + OPcache/JIT, Redis, worker, Reverb)
- [ ] 2. Seed data volume realistis (produk >= 1000 baris, jurnal >= 1000)
- [ ] 3. `k6 run -e K6_STAGES=v200 loadtest/http-load-test.js` (smoke) -- lolos
- [ ] 4. `k6 run loadtest/http-load-test.js` (full 200 -> 500 VU) -- lolos
- [ ] 5. `k6 run loadtest/websocket-load-test.js` (100 koneksi WS) -- lolos
- [ ] 6. Cek Pulse: tidak ada slow query baru di atas ambang selama test
- [ ] 7. Catat hasil baseline di tabel di bawah

### Hasil Baseline (diisi saat gate dijalankan)

| Tanggal | Server | Profil | P95 | P99 | Fail rate | Catatan |
|---|---|---|---|---|---|---|
| - | - | - | - | - | - | belum pernah dijalankan |

### Jika Tidak Lolos

- Gagal di 200 VU -> profil dulu: N+1 (Pulse), laporan sync tanpa
  queue, index yang kurang. Perbaiki, ulangi gate.
- Lolos 200 VU tapi P95 > 300ms di 500 VU -> eskalasi terencana:
  Laravel Octane (FrankenPHP/Swoole) via Supervisor -- nol perubahan
  kode aplikasi. BUKAN ganti bahasa/runtime (keputusan Section 31.4).
