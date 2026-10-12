// Performance Gate HTTP -- k6 load test sesuai Section 24 _plan.md (P95 < 200ms).
// Skenario nyata ERP klinik: login JWT -> listing produk (search+pagination)
// -> listing jurnal -> cash flow. Print/QZ belum diintegrasikan (Section 31.5),
// jadi skenario print sengaja belum ada.
//
// Run (lihat README.md di folder ini):
//   k6 run loadtest/http-load-test.js
// Variasi:
//   K6_BASE_URL, K6_EMAIL, K6_PASSWORD, K6_PROFILE (full|v200|v500)

import http from "k6/http";
import { check, sleep } from "k6";
import { Trend, Rate } from "k6/metrics";

const BASE_URL = __ENV.K6_BASE_URL || "http://127.0.0.1:8000";
// Default = user RuntimeUserSeeder (config/libraries.php, password klinik123).
// SuperadminSeeder tidak ikut rantai V1DatabaseSeeder, jadi superadmin@klinik.local
// TIDAK ada di DB dev default.
const EMAIL = __ENV.K6_EMAIL || "admin@klinik.local";
const PASSWORD = __ENV.K6_PASSWORD || "klinik123";
const STAGES = __ENV.K6_PROFILE || "full";

// Metrik bisnis tambahan selain bawaan k6.
const loginDuration = new Trend("login_duration", true);
const listingDuration = new Trend("listing_duration", true);
const listingErrorRate = new Rate("listing_error_rate");

const PROFILES = {
  full: {
    executor: "ramping-vus",
    startVUs: 0,
    stages: [
      { duration: "30s", target: 50 },   // warm-up
      { duration: "60s", target: 200 },  // ramp ke fase 1 (Section 24.1)
      { duration: "120s", target: 200 }, // hold 200 VU
      { duration: "60s", target: 500 },  // ramp ke fase 2 (Section 24.1)
      { duration: "120s", target: 500 }, // hold 500 VU
      { duration: "30s", target: 0 },
    ],
    gracefulRampDown: "30s",
  },
  v200: {
    executor: "ramping-vus",
    startVUs: 0,
    stages: [
      { duration: "30s", target: 50 },
      { duration: "60s", target: 200 },
      { duration: "120s", target: 200 },
      { duration: "30s", target: 0 },
    ],
    gracefulRampDown: "30s",
  },
  v500: {
    executor: "ramping-vus",
    startVUs: 0,
    stages: [
      { duration: "30s", target: 100 },
      { duration: "60s", target: 500 },
      { duration: "120s", target: 500 },
      { duration: "30s", target: 0 },
    ],
    gracefulRampDown: "30s",
  },
};

export const options = {
  scenarios: {
    gate: PROFILES[STAGES] || PROFILES.full,
  },
  // Budget Section 24.1: P95 < 200ms, P99 < 500ms.
  // 200 VU = target wajib lolos; 500 VU = perkiraan beban fase 2
  // (kalau P95 melewati 300ms di sini, itu trigger eskalasi Octane
  // sesuai Section 24.3, bukan alasan pindah bahasa).
  thresholds: {
    http_req_duration: ["p(95)<200", "p(99)<500"],
    "http_req_duration{scenario:gate}": ["p(95)<200"],
    http_req_failed: ["rate<0.01"],
    listing_error_rate: ["rate<0.01"],
  },
};

export function setup() {
  const res = http.post(
    `${BASE_URL}/api/v1/auth/login`,
    JSON.stringify({ email: EMAIL, password: PASSWORD }),
    { headers: { "Content-Type": "application/json" } }
  );

  check(res, {
    "setup: login 200": (r) => r.status === 200,
  });

  const body = res.json();
  const token = body && body.access_token;

  if (!token) {
    throw new Error(
      `Login gagal (status ${res.status}). Seed dulu (php artisan db:seed --env=...), ` +
        `atau set K6_EMAIL/K6_PASSWORD. Body: ${res.body.slice(0, 200)}`
    );
  }

  return { token };
}

export default function (data) {
  const auth = {
    headers: {
      Authorization: `Bearer ${data.token}`,
      "Content-Type": "application/json",
    },
  };

  // 1) Listing produk dengan search + pagination (jalur paling sering dipakai user).
  let res = http.get(
    `${BASE_URL}/api/v1/products?search=&page=1&per_page=20&sort_by=created_at&sort_direction=desc`,
    auth
  );
  listingDuration.add(res.timings.duration);
  listingErrorRate.add(res.status !== 200);
  check(res, { "products 200": (r) => r.status === 200 });

  sleep(1); // jeda baca, user ERP klik-baca-typing (bukan streaming)

  // 2) Halaman berikutnya (simulasi paginasi).
  res = http.get(
    `${BASE_URL}/api/v1/products?search=&page=2&per_page=20&sort_by=created_at&sort_direction=desc`,
    auth
  );
  listingDuration.add(res.timings.duration);
  listingErrorRate.add(res.status !== 200);
  check(res, { "products page 2 -> 200": (r) => r.status === 200 });

  sleep(1);

  // 3) Listing jurnal (domain akuntansi, join terberat).
  res = http.get(`${BASE_URL}/api/v1/journals?page=1&per_page=20`, auth);
  listingDuration.add(res.timings.duration);
  listingErrorRate.add(res.status !== 200);
  check(res, { "journals 200": (r) => r.status === 200 });

  sleep(2);

  // 4) Cash flow (laporan agregat -- jalur paling berat di gate ini).
  // Endpoint ini mewajibkan period_uuid + branch_uuid (validasi 404 di
  // CashFlowController). Wajib di-set saat menjalankan gate di DB nyata.
  const PERIOD = __ENV.K6_PERIOD_UUID || "01a12d46-f1b7-701f-af38-f25b06c58390";
  const BRANCH = __ENV.K6_BRANCH_UUID || "01a11f65-2259-73cf-8c87-b4bd88d367f8";
  res = http.get(
    `${BASE_URL}/api/v1/accounting/cash-flow?period_uuid=${PERIOD}&branch_uuid=${BRANCH}&page=1&per_page=20`,
    auth
  );
  listingDuration.add(res.timings.duration);
  listingErrorRate.add(res.status !== 200);
  check(res, { "cash-flow 200": (r) => r.status === 200 });

  sleep(2);

  // 5) Re-login sesekali (refresh token TTL, jalur auth tetap terukur).
  if (__ITER % 20 === 19) {
    const loginStart = Date.now();
    const loginRes = http.post(
      `${BASE_URL}/api/v1/auth/login`,
      JSON.stringify({ email: EMAIL, password: PASSWORD }),
      { headers: { "Content-Type": "application/json" } }
    );
    loginDuration.add(Date.now() - loginStart);
    check(loginRes, { "re-login 200": (r) => r.status === 200 });
  }
}
