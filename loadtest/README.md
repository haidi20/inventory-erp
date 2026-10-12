# Load Testing

## Prerequisites
- Install k6: https://k6.io/docs/getting-started/installation/
- Laravel server running
- Reverb server running (php artisan reverb:start) -- hanya untuk WS test

## Run WebSocket Load Test
```bash
# Set environment variables
export K6_BASE_URL=http://127.0.0.1:8000
export K6_REVERB_KEY=your-reverb-key

# Run load test (100 VUs, 30 seconds)
k6 run loadtest/websocket-load-test.js
```

## Thresholds
- ws_connecting p(95) < 2000ms
- ws_session_duration p(95) < 30000ms

---

## Run HTTP Performance Gate (Section 24/31 _plan.md)

Performance gate pre-launch: bukti terukur bahwa 200+ user konkuren
aman di Laravel, bukan asumsi. Skenario nyata ERP: login JWT ->
listing produk (search + pagination 2 halaman) -> listing jurnal ->
cash flow (agregat terberat) -> re-login berkala.

```bash
# 1. Server jalan + DB ter-seed (RuntimeUserSeeder: admin@klinik.local / klinik123)
php artisan serve   # atau server production

# 2. Smoke kecil dulu (200 VU profile) untuk validasi kredensial/endpoint
export K6_BASE_URL=http://127.0.0.1:8000
k6 run -e K6_PROFILE=v200 loadtest/http-load-test.js

# 3. Gate penuh: ramp 50 -> 200 (hold 2 menit) -> 500 (hold 2 menit)
k6 run loadtest/http-load-test.js

# Kredensial lain (jangan seed default di server yang terekspos):
export K6_EMAIL=admin@klinik.local
export K6_PASSWORD=klinik123

# Cash-flow butuh periode akuntansi + cabang (validasi 404 di controller):
export K6_PERIOD_UUID=<uuid dari closing_periods>
export K6_BRANCH_UUID=<uuid dari branches>
```

### Profil K6_PROFILE

| Profil | Bentuk | Kapan dipakai |
|---|---|---|
| `full` (default) | 50 -> 200 (hold 2m) -> 500 (hold 2m) -> 0, ~7 menit | Pre-launch gate |
| `v200` | 50 -> 200 (hold 2m) -> 0, ~4 menit | Iterasi cepat / smoke |
| `v500` | 100 -> 500 (hold 2m) -> 0, ~4 menit | Verifikasi fase 2 |

### Thresholds (budget Section 24.1)

- `http_req_duration`: **p(95) < 200ms**, p(99) < 500ms
- `http_req_failed`: rate < 1%
- `listing_error_rate`: rate < 1%

Interpretasi hasil:
- **Lolos di `full`** -> launch aman, catat angka P95/P99 ke `../src/guides/deployment-checklist.md`.
- **Lolos `v200` tapi P95 > 300ms saat `v500`** -> bukan gagal; itu trigger
  eskalasi Fase 1.5 (Laravel Octane) sesuai Section 24.3 -- bukan alasan
  ganti bahasa/runtime.
- **Gagal di `v200`** -> profil terlebih dahulu: cek N+1 (Pulse), laporan
  sync tanpa queue, index -- perbaiki lalu ulangi, sebelum sempat launch.

### Catatan penting

- Jalankan gate terhadap **server dengan konfigurasi production-like**
  (OPcache on, Redis cache/queue) -- angka dari `php artisan serve` dev
  mode TIDAK mewakili produksi.
- Angka baseline yang lolos dicatat di `../src/guides/deployment-checklist.md` bagian
  Performance Gate.
