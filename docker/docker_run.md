# Docker Run Guide

Dokumen ini adalah panduan operasional yang siap copas untuk menjalankan Docker proyek Klinik Starter dengan **dua mode utama saja**:

- `dev`
- `prod`

Jalur akses resminya:

- `http://127.0.0.1:8088/dev/`
- `http://127.0.0.1:8088/prod/`
- `http://127.0.0.1:8088/cicd/`
- `http://127.0.0.1:19999/`

Semua perintah diasumsikan dijalankan dari:

```powershell
cd /d D:\projects\klinik-starter\docker
```

Catatan:

- [run.bat](d:/projects/klinik-starter/docker/run.bat) sekarang sudah fokus ke `dev` dan `prod`
- [start_step_by_step.bat](d:/projects/klinik-starter/docker/start_step_by_step.bat) disediakan jika ingin startup manual bertahap dengan progres yang terlihat
- jika `docker` tidak ada di PATH Windows, helper akan mencoba fallback ke `WSL`
- opsi `1-6` sekarang dijalankan di background lalu batch langsung keluar
- pada mesin Windows ini, `127.0.0.1` terbukti lebih stabil daripada `localhost`
- gateway Docker lokal sekarang default ke `8088` supaya tidak bentrok dengan Laragon/Apache di port `80`
- port DB lokal Docker juga dinaikkan supaya tidak bentrok dengan MySQL/Postgres Laragon

## 0. Ringkasan Status

Yang sudah dikerjakan pada setup Docker sekarang:

- stack dipisah menjadi `dev` dan `prod`
- database dipisah menjadi:
  - `postgres_dev`
  - `postgres_prod`
- Redis dipisah menjadi:
  - `redis_dev`
  - `redis_prod`
- CI/CD sekarang memakai satu Jenkins:
  - `jenkins`
- monitoring lokal sekarang tersedia lewat:
  - `netdata`
- frontend `dev` dan `prod` sekarang tidak lagi dilayani oleh `vite preview`
- frontend `dev` dan `prod` sekarang dibuild lalu dipublish ke folder static host:
  - `docker/runtime/local/fe_dev_public/current`
  - `docker/runtime/local/fe_prod_public/current`
- `nginx` sekarang melayani hasil build static frontend secara langsung
- `run.bat` sudah disesuaikan agar fokus operasional harian `dev` dan `prod`
- opsi `2` dan `4` sekarang wajib menjalankan build/publish frontend dulu sebelum flow dianggap selesai
- TDD sudah ditambahkan untuk menjaga:
  - route `dev` dan `prod`
  - urutan build/publish FE
  - background run dari `run.bat`
  - topology DB dan Jenkins per environment

Masalah utama yang sudah diselesaikan:

- Docker Windows tidak selalu tersedia
  - solving: `run.bat` fallback ke `WSL`
- FE dev/prod sempat `404` atau `connection refused`
  - solving: pindah ke arsitektur build/publish static + gateway `nginx`
- `localhost` kadang tidak stabil dari host Windows
  - solving: gunakan `127.0.0.1` sebagai URL lokal utama
- Jenkins sebelumnya belum punya login baku
  - solving: admin dibootstrap otomatis dengan password tetap

## 0.1. URL Siap Copas Saat Semua Container Running

Pakai ini saat semua container sudah hidup:

```text
FE dev            http://127.0.0.1:8088/dev/
FE dev login      http://127.0.0.1:8088/dev/login
API dev health    http://127.0.0.1:8088/dev/api/health
CI/CD             http://127.0.0.1:8088/cicd/

FE prod           http://127.0.0.1:8088/prod/
FE prod login     http://127.0.0.1:8088/prod/login
API prod health   http://127.0.0.1:8088/prod/api/health

PgAdmin           http://127.0.0.1:5050/
MySQL dev         http://127.0.0.1:13306  (host port)
MySQL prod        http://127.0.0.1:13307  (host port)
Postgres dev      http://127.0.0.1:15432  (host port)
Postgres prod     http://127.0.0.1:15433  (host port)
MinIO API         http://127.0.0.1:9000/
MinIO Console     http://127.0.0.1:9001/
Netdata           http://127.0.0.1:19999/
```

Login Jenkins:

```text
username: admin
password: qwerty123
```

Untuk akses dari jaringan klinik:

```text
http://IP_SERVER:8088/dev/
http://IP_SERVER:8088/dev/login
http://IP_SERVER:8088/dev/api/health
http://IP_SERVER:8088/cicd/

http://IP_SERVER:8088/prod/
http://IP_SERVER:8088/prod/login
http://IP_SERVER:8088/prod/api/health
http://IP_SERVER:19999/
```

## 1. Menjalankan Lewat run.bat

### Opsi 1. Jalankan semua container

Perilaku:

1. turunkan dulu stack lokal yang masih aktif
2. nyalakan infra utama lebih dulu
3. tunggu DB, Redis, dan Jenkins sehat
4. nyalakan service aplikasi
5. publish FE dev dan FE prod
6. setelah dipilih, batch langsung selesai dan tidak menunggu di menu

Yang aktif:

- `redis_dev`
- `redis_prod`
- `postgres_dev`
- `postgres_prod`
- `pgadmin`
- `minio`
- `be_dev`
- `be_prod`
- `fe_dev`
- `fe_prod`
- `nginx`
- `jenkins_dev`
- `jenkins_prod`

### Opsi 1 versi manual, dipisah agar progres kelihatan

Jalankan satu per satu dari PowerShell Windows:

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose down --remove-orphans"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d postgres_dev redis_dev postgres_prod redis_prod pgadmin"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose ps"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d jenkins_dev jenkins_prod"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose ps"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d minio"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d be_dev be_prod fe_dev fe_prod nginx"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose ps"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose exec -T fe_dev sh -lc 'node ./scripts/docker_publish_frontend.js dev /publish/dev'"
```

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose exec -T fe_prod sh -lc 'node ./scripts/docker_publish_frontend.js prod /publish/prod'"
```

```powershell
wsl -e sh -lc "curl -i http://127.0.0.1/dev/api/health"
```

```powershell
wsl -e sh -lc "curl -i http://127.0.0.1/dev/cicd/login"
```

Kalau ada tahap yang macet, langsung cek log service yang relevan sebelum lanjut ke tahap berikutnya.

### Opsi 2. Jalankan atau refresh FE dev

Catatan perilaku:

- command dijadwalkan di background
- setelah dipilih, batch langsung keluar

Setara dengan:

```powershell
docker compose up -d postgres_dev redis_dev be_dev fe_dev nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
```

Dipakai jika:

- ada perubahan FE dev
- FE dev perlu dijalankan atau direfresh tanpa rebuild image Docker
- source code frontend berubah dan ingin dibangun ulang ke mode yang lebih stabil untuk browser lokal

Catatan penting:

- perubahan code FE biasa tidak perlu rebuild image Docker
- refresh FE dev sekarang tidak merecreate `fe_dev` dan tidak memutus `nginx`
- jika `fe_dev` belum running, helper akan otomatis menjalankannya dulu
- FE dev lokal sekarang dibangun ke folder publish static, lalu diserve langsung oleh `nginx`
- folder static aktif FE dev ada di:
  - `docker/runtime/local/fe_dev_public/current`
- HTML shell dan asset hash sekarang tetap tersedia saat publish ulang berjalan
- rebuild FE dev hanya dibutuhkan jika:
  - `package.json` berubah
  - `package-lock.json` berubah
  - [Dockerfile](d:/projects/klinik-starter/docker/fe/Dockerfile) berubah
- untuk perubahan dependency, cukup jalankan opsi `2` lagi

### Opsi 3. Jalankan atau refresh BE dev

Catatan perilaku:

- command dijadwalkan di background
- setelah dipilih, batch langsung keluar

Setara dengan:

```powershell
docker compose up -d postgres_dev redis_dev nginx be_dev
docker compose restart be_dev
```

Dipakai jika:

- ada perubahan backend dev
- endpoint `/dev/api/...` perlu direfresh

Catatan penting:

- helper menunggu `http://127.0.0.1/dev/api/health` benar-benar balik status `ok` sebelum flow dianggap siap
- perubahan code BE biasa tidak perlu rebuild image
- rebuild BE dev hanya dibutuhkan jika:
  - `go.mod` berubah
  - `go.sum` berubah
  - [Dockerfile](d:/projects/klinik-starter/docker/be/Dockerfile) berubah
- untuk rebuild manual, gunakan menu `Recreate service tertentu`

### Opsi 4. Jalankan atau refresh FE prod

Catatan perilaku:

- command dijadwalkan di background
- setelah dipilih, batch langsung keluar

Setara dengan:

```powershell
docker compose up -d postgres_prod redis_prod be_prod fe_prod nginx
docker compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
```

Dipakai jika:

- ada perubahan FE prod
- halaman `/prod/` perlu direfresh

Catatan penting:

- FE prod lokal sekarang refresh dari source code tanpa rebuild image
- FE prod lokal sekarang dipublish ke static folder lalu diserve langsung oleh `nginx`
- folder static aktif FE prod ada di:
  - `docker/runtime/local/fe_prod_public/current`
- rebuild FE prod hanya dibutuhkan jika:
  - `package.json` berubah
  - `package-lock.json` berubah
  - [Dockerfile](d:/projects/klinik-starter/docker/fe/Dockerfile) berubah

### Opsi 5. Jalankan atau refresh BE prod

Catatan perilaku:

- command dijadwalkan di background
- setelah dipilih, batch langsung keluar

Setara dengan:

```powershell
docker compose up -d postgres_prod redis_prod nginx be_prod
docker compose restart be_prod
```

Dipakai jika:

- ada perubahan backend prod
- endpoint `/prod/api/...` perlu direfresh

Catatan penting:

- BE prod lokal sekarang refresh dari source code tanpa rebuild image
- rebuild BE prod hanya dibutuhkan jika:
  - `go.mod` berubah
  - `go.sum` berubah
  - [Dockerfile](d:/projects/klinik-starter/docker/be/Dockerfile) berubah

## 2. Jalankan Semua Container Manual

Kalau tidak memakai `run.bat`, lebih aman jalankan bertahap:

```powershell
docker compose down --remove-orphans
docker compose up -d postgres_dev redis_dev postgres_prod redis_prod pgadmin
docker compose up -d jenkins_dev jenkins_prod
docker compose up -d minio
docker compose up -d be_dev be_prod fe_dev fe_prod nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
docker compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
```

URL utama:

- FE dev: [http://127.0.0.1/dev/](http://127.0.0.1/dev/)
- FE prod: [http://127.0.0.1/prod/](http://127.0.0.1/prod/)
- API dev: [http://127.0.0.1/dev/api/health](http://127.0.0.1/dev/api/health)
- API prod: [http://127.0.0.1/prod/api/health](http://127.0.0.1/prod/api/health)
- CI/CD dev: [http://127.0.0.1/dev/cicd/](http://127.0.0.1/dev/cicd/)
- CI/CD prod: [http://127.0.0.1/prod/cicd/](http://127.0.0.1/prod/cicd/)

Untuk jaringan klinik:

- FE dev: `http://IP_SERVER/dev/`
- FE prod: `http://IP_SERVER/prod/`
- CI/CD dev: `http://IP_SERVER/dev/cicd/`
- CI/CD prod: `http://IP_SERVER/prod/cicd/`

Login Jenkins:

- username: `admin`
- password: `qwerty123`

## 3. Jalankan FE dev Saja

Kalau targetnya hanya FE dev:

```powershell
docker compose up -d postgres_dev redis_dev be_dev fe_dev nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
```

Kalau API dev juga dibutuhkan:

```powershell
docker compose up -d redis_dev postgres_dev be_dev fe_dev nginx
```

URL:

- FE dev: [http://127.0.0.1/dev/](http://127.0.0.1/dev/)
- API dev: [http://127.0.0.1/dev/api/health](http://127.0.0.1/dev/api/health)

## 4. Jalankan BE dev Saja

```powershell
docker compose up -d postgres_dev redis_dev nginx be_dev
docker compose restart be_dev
```

Kalau ingin sekalian lengkap untuk flow dev:

```powershell
docker compose up -d redis_dev postgres_dev be_dev
```

## 5. Jalankan FE prod dan BE prod

Frontend prod:

```powershell
docker compose up -d postgres_prod redis_prod be_prod fe_prod nginx
docker compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
```

Backend prod:

```powershell
docker compose up -d postgres_prod redis_prod nginx be_prod
docker compose restart be_prod
```

Kalau ingin dua-duanya sekaligus:

```powershell
docker compose up -d --force-recreate postgres_prod redis_prod fe_prod be_prod nginx
```

URL:

- FE prod: [http://127.0.0.1/prod/](http://127.0.0.1/prod/)
- API prod: [http://127.0.0.1/prod/api/health](http://127.0.0.1/prod/api/health)

## 6. Cek Status Container

```powershell
docker compose ps
```

Minimal yang diharapkan untuk akses FE dev:

- `nginx` = `Up`
- `be_dev` = `Up (healthy)`
- `fe_dev` = `Up (healthy)`
- `postgres_dev` = `Up (healthy)`
- `redis_dev` = `Up (healthy)`

Minimal yang diharapkan untuk akses FE prod:

- `nginx` = `Up`
- `be_prod` = `Up (healthy)`
- `fe_prod` = `Up (healthy)`

## 7. Lihat Logs

Semua stack lokal:

```powershell
docker compose logs --tail=200
```

FE dev:

```powershell
docker compose logs -f fe_dev
```

BE dev:

```powershell
docker compose logs -f be_dev
```

FE prod:

```powershell
docker compose logs -f fe_prod
```

BE prod:

```powershell
docker compose logs -f be_prod
```

Gateway:

```powershell
docker compose logs -f nginx
```

## 8. Start atau Stop Service Tertentu

Start service:

```powershell
docker compose up -d redis_dev
docker compose up -d redis_prod
docker compose up -d postgres_dev
docker compose up -d postgres_prod
docker compose up -d be_dev
docker compose up -d be_prod
docker compose up -d fe_dev
docker compose up -d fe_prod
docker compose up -d nginx
docker compose up -d jenkins_dev
docker compose up -d jenkins_prod
```

Stop service:

```powershell
docker compose stop fe_dev
docker compose stop be_dev
docker compose stop fe_prod
docker compose stop be_prod
docker compose stop nginx
```

## 9. Stop yang Aman

Hentikan sementara tanpa hapus container:

```powershell
docker compose stop
```

Turunkan stack lokal:

```powershell
docker compose down
```

Catatan:

- jangan jadikan `docker compose down -v` sebagai kebiasaan harian
- `-v` bisa menghapus named volume yang seharusnya tetap dipertahankan

## 10. Verifikasi FE dev

Untuk memastikan FE dev benar-benar hidup:

1. jalankan:

```powershell
docker compose up -d postgres_dev redis_dev be_dev fe_dev nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
```

2. cek:

```powershell
docker compose ps
docker compose logs --tail=100 fe_dev
docker compose logs --tail=100 nginx
dir D:\projects\klinik-starter\docker\runtime\local\fe_dev_public\current
```

3. buka:

- [http://127.0.0.1/dev/](http://127.0.0.1/dev/)

4. cek API:

- [http://127.0.0.1/dev/api/health](http://127.0.0.1/dev/api/health)

## 11. Verifikasi FE prod

Untuk memastikan FE prod benar-benar hidup:

1. jalankan:

```powershell
docker compose up -d postgres_prod redis_prod be_prod fe_prod nginx
docker compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
```

2. cek:

```powershell
docker compose ps
docker compose logs --tail=100 fe_prod
docker compose logs --tail=100 nginx
dir D:\projects\klinik-starter\docker\runtime\local\fe_prod_public\current
```

3. buka:

- [http://127.0.0.1/prod/](http://127.0.0.1/prod/)

4. cek API:

- [http://127.0.0.1/prod/api/health](http://127.0.0.1/prod/api/health)

## 12. Jika asset frontend 404

Kalau browser menampilkan error seperti:

- `/dev/ 404`
- `/dev/login 404`
- `asset hash frontend 404`

Langkahnya:

Untuk FE dev:

```powershell
docker compose up -d postgres_dev redis_dev be_dev fe_dev nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
```

Untuk FE prod:

```powershell
docker compose up -d postgres_prod redis_prod be_prod fe_prod nginx
docker compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
```

Setelah itu:

- hard refresh browser
- atau buka ulang lewat incognito
- cek bahwa `current/index.html` memang ada di folder publish frontend

Jika masalah terjadi setelah perubahan dependency atau Dockerfile, baru lakukan rebuild image lewat menu `Recreate service tertentu`.

## 13. Jika localhost dari Windows tidak tembus

Gunakan:

- `http://127.0.0.1/dev/`
- `http://127.0.0.1/prod/`

Kalau `localhost` gagal tetapi `127.0.0.1` berhasil:

- tetap pakai `127.0.0.1`

Kalau dua-duanya gagal, baru gunakan:

- `http://IP_WSL/dev/`
- `http://IP_WSL/prod/`

Atau, untuk operasional klinik:

- `http://IP_SERVER/dev/`
- `http://IP_SERVER/prod/`

## 14. Ringkasan Paling Praktis

Kalau target Anda hanya:

- FE dev hidup
- API dev hidup
- akses jalan

pakai ini:

```powershell
cd /d D:\projects\klinik-starter\docker
docker compose up -d redis_dev postgres_dev be_dev fe_dev nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
docker compose ps
```

Lalu buka:

- [http://127.0.0.1/dev/](http://127.0.0.1/dev/)

Kalau target Anda untuk prod:

```powershell
cd /d D:\projects\klinik-starter\docker
docker compose up -d redis_prod postgres_prod be_prod fe_prod nginx
docker compose ps
```

Lalu buka:

- [http://127.0.0.1/prod/](http://127.0.0.1/prod/)
