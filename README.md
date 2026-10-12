# Klinik Starter

Monorepo aplikasi klinik yang terdiri dari:

- [src](d:/projects/klinik-starter/src) untuk Laravel
- [be](d:/projects/klinik-starter/be) untuk Gin API
- [fe](d:/projects/klinik-starter/fe) untuk Vue frontend

## Konsep Repo

Repository ini memakai pola **monorepo**, jadi:

- hanya ada **1 git root** di [d:/projects/klinik-starter](d:/projects/klinik-starter)
- folder [be](d:/projects/klinik-starter/be) dan [fe](d:/projects/klinik-starter/fe) **bukan submodule**
- remote tetap memakai source git utama yang sama

Helper monorepo yang tersedia di root:

- [package.json](d:/projects/klinik-starter/package.json)
- [Makefile](d:/projects/klinik-starter/Makefile)
- [scripts/dev-be.ps1](d:/projects/klinik-starter/scripts/dev-be.ps1)
- [scripts/dev-fe.ps1](d:/projects/klinik-starter/scripts/dev-fe.ps1)
- [scripts/dev-all.ps1](d:/projects/klinik-starter/scripts/dev-all.ps1)

Contoh perintah dari root:

- `npm run fe:type-check`
- `npm run fe:test`
- `npm run be:compile`
- `make be-compile`

Untuk build frontend, jalankan langsung dari folder [fe](d:/projects/klinik-starter/fe):

- `cd fe && npm run build`

Untuk jalankan mode development dari root:

- `npm run dev:be`
- `npm run dev:fe`
- `npm run dev`

Atau dengan Make:

- `make dev-be`
- `make dev-fe`
- `make dev-all`

## Stack Aktif

- Laravel 12
- Go Gin
- Vue 3 + Vite
- MySQL 8
- Redis 7
- Docker Compose
- GitHub Actions untuk CI/CD

## Docker

Setup Docker aktif ada di:

- [docker-compose.yml](d:/projects/klinik-starter/docker-compose.yml)
- [docker-compose.prod.yml](d:/projects/klinik-starter/docker-compose.prod.yml)
- [docker-compose.dev-main.yml](d:/projects/klinik-starter/docker-compose.dev-main.yml)
- [docker-compose.ci.yml](d:/projects/klinik-starter/docker-compose.ci.yml)
- [docker/README.md](d:/projects/klinik-starter/docker/README.md)

Penyimpanan runtime Docker diatur agar data penting tidak bergantung pada filesystem container:

- data MySQL disimpan di folder host `docker/runtime/...`
- data Redis disimpan di folder host `docker/runtime/...`
- storage backend Gin dipetakan ke folder host `docker/runtime/.../be*_storage`

Jadi saat container dihapus atau dibuat ulang, data database dan storage file backend tetap berada di host.

Service yang disiapkan:

- `mysql`
- `redis`
- `be`
- `laravel-app`
- `laravel-web`
- `fe`

## CI/CD

Workflow GitHub Actions ada di:

- [ci.yml](d:/projects/klinik-starter/.github/workflows/ci.yml)
- [docker-publish.yml](d:/projects/klinik-starter/.github/workflows/docker-publish.yml)

CI fokus ke:

- test Laravel
- test backend Go yang stabil hanya saat path `be/**` berubah
- build dan test frontend hanya saat path `fe/**` berubah
- docker build `be` dan `fe` hanya saat area terkait berubah
- smoke test stack `be + fe + mysql + redis` via `docker compose` di GitHub Actions

CD fokus ke:

- build image Docker
- publish image ke GHCR

## Catatan

- Docker belum dijalankan di mesin ini karena sesuai arahan Anda Docker tidak diinstal dulu.
- Setup yang dibuat tetap siap dipakai begitu Docker tersedia di environment developer atau CI runner.
- Monorepo dipilih agar `be` dan `fe` tetap satu source of truth, satu remote, dan lebih mudah dijalankan dari root project.
