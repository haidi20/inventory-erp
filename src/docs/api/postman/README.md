# Inventory ERP Postman Collection

`inventory-api.postman_collection.json` adalah collection induk untuk dokumentasi
API Inventory ERP. Folder `Overview` berisi konvensi response, pagination,
filter, dan error catalog.

Contoh response per HTTP status tersedia di `error-catalog.md`.

## Konvensi Data

- Identifier internal memakai UUID string.
- Timestamp memakai Unix integer detik.
- Nilai uang memakai dua desimal tanpa aritmetika float.
- API tenant memakai `/api/v1` dengan JWT bearer; POS SBPOS memakai `/api`
  dengan guard `pos.auth`.

## Pagination Dan Filter

Parameter umum adalah `search`, `search_column`, `page`, `per_page`,
`sort_by`, dan `sort_direction`. Metadata standar berisi `total`,
`current_page`, `per_page`, dan `last_page`; endpoint lama dapat menyediakan
`page` sebagai alias. Kolom search/sort harus sesuai whitelist backend.

## Rate Limit

API umum dibatasi 60 request/menit per user UUID atau IP. Login dan reset
password dibatasi 5 request/menit, sedangkan registrasi 3 request/menit.
Respons 429 memakai pesan Indonesia dan header `Retry-After`.

Import collection ini setelah membuat environment Postman. Jangan menyimpan
token production di dalam collection; gunakan variable `api_token` pada
environment lokal atau staging.

## Governance

Collection memiliki field `version`, changelog, environment template tanpa
secret, aturan kredensial, dan checklist review. Panduan lengkap ada di
`GOVERNANCE.md`. File environment berisi kredensial lokal wajib memakai nama
`*.local.postman_environment.json` agar tidak ikut ter-commit.
