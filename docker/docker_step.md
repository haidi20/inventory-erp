# Docker Step, Problem, Solving, dan Rekomendasi

Dokumen ini merangkum kondisi Docker proyek Klinik Starter dengan fokus pada **dua environment saja**:

- `dev`
- `prod`

Catatan penting:

- flow operasional utama sekarang hanya `dev` dan `prod`
- jalur akses resmi sekarang:
  - `http://127.0.0.1:8088/dev/`
  - `http://127.0.0.1:8088/prod/`
  - `http://127.0.0.1:8088/cicd/`
  - `http://127.0.0.1:19999/`
- jika diakses dari komputer lain di jaringan klinik, gunakan:
  - `http://IP_SERVER:8088/dev/`
  - `http://IP_SERVER:8088/prod/`
- gateway Docker lokal sengaja dipindah ke port `8088` agar tidak bentrok dengan Laragon/Apache di port `80`
- host port MySQL/Postgres Docker lokal juga dinaikkan agar tidak bentrok dengan Laragon database service

## 0. Ringkasan Cepat

Yang sudah dikerjakan di setup Docker ini:

- memisahkan stack `dev` dan `prod`
- memisahkan DB:
  - `postgres_dev`
  - `postgres_prod`
- memisahkan Redis:
  - `redis_dev`
  - `redis_prod`
- menyederhanakan CI/CD menjadi satu Jenkins:
  - `jenkins`
- menambahkan monitoring lokal:
  - `netdata`
- memindahkan frontend ke flow build/publish static
- membuat `nginx` melayani hasil build frontend langsung
- membuat `run.bat` fokus ke alur operasional `dev` dan `prod`
- membuat background launcher untuk opsi `1-6`
- menambahkan bootstrap login Jenkins:
  - `admin`
  - `qwerty123`
- menambahkan seed job Jenkins:
  - `fe-dev-delivery`
  - `be-dev-delivery`
  - `fe-prod-delivery`
  - `be-prod-delivery`
- menambahkan TDD untuk:
  - background run `run.bat`
  - publish/build FE dev dan FE prod
  - topology DB dan Jenkins dev/prod
  - exact gateway health endpoint `/dev/api/health` dan `/prod/api/health`
  - flow `BE dev` tanpa restart manual helper

## 0.1. URL Siap Akses Saat Semua Container Running

URL lokal yang bisa langsung dicoba:

```text
FE dev            http://127.0.0.1:8088/dev/
FE dev login      http://127.0.0.1:8088/dev/login
API dev health    http://127.0.0.1:8088/dev/api/health
CI/CD             http://127.0.0.1:8088/cicd/

FE prod           http://127.0.0.1:8088/prod/
FE prod login     http://127.0.0.1:8088/prod/login
API prod health   http://127.0.0.1:8088/prod/api/health

PgAdmin           http://127.0.0.1:5050/
MySQL dev         127.0.0.1:13306
MySQL prod        127.0.0.1:13307
Postgres dev      127.0.0.1:15432
Postgres prod     127.0.0.1:15433
MinIO API         http://127.0.0.1:9000/
MinIO Console     http://127.0.0.1:9001/
Netdata           http://127.0.0.1:19999/
```

Kredensial Jenkins:

```text
username: admin
password: qwerty123
```

URL dari komputer lain di jaringan klinik:

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

## 0.2. Masalah Utama dan Solving Singkat

Masalah:
- Docker tidak dikenali dari Windows
  Solving: `run.bat` sekarang fallback ke `WSL`
- FE dev/prod sempat `404` dan `connection refused`
  Solving: frontend dipindah ke model build/publish static, bukan upstream runtime
- `localhost` tidak selalu stabil dari Windows host
  Solving: URL lokal utama dipindah ke `127.0.0.1`
- Jenkins belum punya login baku
  Solving: dibootstrap `admin / qwerty123`
- FE dev/prod bisa terasa lambat jika image dibuild ulang
  Solving: flow harian sekarang publish ulang source tanpa rebuild image
- risiko data hilang saat recreate
  Solving: data penting dipisah ke host bind mount per environment
- satu command `docker compose up ...` terlalu panjang
  Solving: startup manual sekarang direkomendasikan dipisah per tahap agar progres dan titik gagal mudah terlihat

## 0.3. Startup Manual yang Direkomendasikan, Dipisah Per Tahap

Kalau ingin melihat progres dengan jelas, jangan pakai satu command panjang seperti:

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d --build postgres_dev redis_dev postgres_prod redis_prod pgadmin jenkins_dev jenkins_prod be_dev be_prod fe_dev fe_prod nginx"
```

Gunakan urutan ini:

1. reset stack lokal lebih dulu
2. nyalakan DB dan Redis
3. cek status
4. nyalakan Jenkins
5. cek status lagi
6. nyalakan MinIO bila perlu
7. nyalakan app
8. publish FE dev dan FE prod
9. cek URL API health dan CI/CD

Command yang siap copas:

```powershell
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose down --remove-orphans"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d postgres_dev redis_dev postgres_prod redis_prod pgadmin"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose ps"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d jenkins_dev jenkins_prod"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose ps"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d minio"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose up -d be_dev be_prod fe_dev fe_prod nginx"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose exec -T fe_dev sh -lc 'node ./scripts/docker_publish_frontend.js dev /publish/dev'"
wsl -e sh -lc "cd /mnt/d/projects/klinik-starter/docker && docker compose exec -T fe_prod sh -lc 'node ./scripts/docker_publish_frontend.js prod /publish/prod'"
wsl -e sh -lc "curl -i http://127.0.0.1/dev/api/health"
wsl -e sh -lc "curl -i http://127.0.0.1/dev/cicd/login"
```

Keuntungan cara ini:

- progres terlihat
- service yang macet langsung ketahuan
- log yang perlu dicek jadi lebih jelas
- tidak perlu menunggu semua service selesai baru tahu gagal di mana

## 1. Gambaran Umum Setup

Setup Docker aktif utama menggunakan:

- [docker-compose.yml](d:/projects/klinik-starter/docker/docker-compose.yml)
- [run.bat](d:/projects/klinik-starter/docker/run.bat)

Stack lokal utama memuat:

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

Gateway akses memakai Nginx:

- FE dev: `http://localhost/dev/`
- FE prod: `http://localhost/prod/`
- API dev: `http://localhost/dev/api/health`
- API prod: `http://localhost/prod/api/health`
- CI/CD dev: `http://localhost/dev/cicd/`
- CI/CD prod: `http://localhost/prod/cicd/`

## 2. Masalah Utama yang Pernah Terjadi

Bagian ini dibuat paling atas supaya operator langsung tahu problem nyata apa saja yang pernah muncul.

### 2.1. Docker tidak dikenali dari Windows

Masalah:

- saat menjalankan [run.bat](d:/projects/klinik-starter/docker/run.bat), muncul pesan:
  - `'docker' is not recognized as an internal or external command`

Dampak:

- helper tidak bisa menjalankan command Docker dari Windows
- operator mengira stack gagal total

Apa yang sudah dilakukan:

- [run.bat](d:/projects/klinik-starter/docker/run.bat) sekarang mencoba `docker` Windows terlebih dahulu
- jika tidak tersedia, helper akan fallback otomatis ke `WSL`
- helper tidak lagi mengklaim sukses jika command gagal

Langkah solving:

1. jalankan [run.bat](d:/projects/klinik-starter/docker/run.bat)
2. jika `docker` tidak ada di PATH Windows, biarkan helper memakai jalur `WSL`
3. jika masih gagal, pastikan Docker memang aktif di sisi `WSL`

### 2.2. FE dev sempat membaca source code lama

Masalah:

- container `fe_dev` sempat hidup tetapi source yang terbaca bukan repo saat ini

Dampak:

- perubahan frontend terbaru tidak muncul
- hasil di browser berbeda dengan file source di repo

Apa yang sudah dilakukan:

- stack direcreate ulang dari folder repo yang benar
- [run.bat](d:/projects/klinik-starter/docker/run.bat) dipakai dari folder [docker](d:/projects/klinik-starter/docker)
- dokumentasi sekarang menegaskan folder kerja yang benar

Langkah solving:

1. pindah ke folder:

```powershell
cd /d D:\projects\klinik-starter\docker
```

2. turunkan stack yang aktif:

```powershell
docker compose down
```

3. jalankan ulang stack dari repo yang benar:

```powershell
docker compose up -d --build
```

### 2.3. Asset FE dev `404` atau root FE `404` saat dibuka lewat `/dev/`

Masalah:

- frontend dev dibuka di bawah prefix `/dev/`
- asset Vite awalnya masih mencoba di-load dari root `/`

Gejala:

- browser menampilkan error seperti:
  - `@vite/client 404`
  - `src/main.ts 404`
  - `virtual:vue-devtools-path 404`

Dampak:

- FE dev terlihat rusak walaupun container sebenarnya hidup
- yang gagal adalah path asset frontend, bukan service utamanya

Apa yang sudah dilakukan:

- frontend ditambah `VITE_APP_BASE`
- FE dev diarahkan ke `/dev/`
- FE prod diarahkan ke `/prod/`
- `vite.config.js` sekarang membaca `VITE_APP_BASE` dan `BUILD_OUT_DIR` dari env runtime
- Nginx di [dev-prod.conf](d:/projects/klinik-starter/docker/nginx/dev-prod.conf) diubah menjadi static SPA gateway
- FE tidak lagi diakses sebagai upstream `vite preview`

Langkah solving:

1. publish ulang FE dev:

```powershell
docker compose up -d postgres_dev redis_dev be_dev fe_dev nginx
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
```

2. recreate FE dev, FE prod, dan Nginx jika konfigurasi compose atau gateway berubah:

```powershell
docker compose up -d --force-recreate --no-deps fe_dev fe_prod nginx
```

3. hard refresh browser
4. cek ulang:
  - `http://127.0.0.1/dev/`
  - `http://127.0.0.1/dev/login`
  - asset hash yang dirujuk `index.html`

### 2.4. FE dev terasa sangat lambat saat dijalankan

Masalah:

- opsi FE dev terasa lama sekali walaupun user hanya ingin melihat update code frontend

Penyebab utama:

- helper sebelumnya masih menjalankan `docker compose build fe_dev`
- build context dibaca dari root repo
- repo berada di drive Windows yang diakses dari WSL, sehingga proses `load build context` bisa sangat lambat

Dampak:

- user mengira FE dev selalu harus rebuild image
- waktu tunggu terasa tidak masuk akal untuk perubahan code biasa

Apa yang sudah dilakukan:

- opsi `2` di [run.bat](d:/projects/klinik-starter/docker/run.bat) diubah menjadi jalankan atau refresh FE dev tanpa rebuild image
- opsi `3`, `4`, dan `5` juga diarahkan ke refresh container tanpa rebuild image
- root [`.dockerignore`](d:/projects/klinik-starter/.dockerignore) ditambahkan supaya context build lebih ringan saat memang perlu rebuild

Langkah solving:

1. untuk perubahan code biasa, gunakan opsi `2-5` sesuai service yang ingin direfresh
2. rebuild image hanya jika file dependency atau Dockerfile berubah
3. contoh kondisi yang memang perlu rebuild:
  - `package.json` berubah
  - `package-lock.json` berubah
  - `go.mod` berubah
  - `go.sum` berubah
  - [Dockerfile](d:/projects/klinik-starter/docker/fe/Dockerfile) berubah
  - [Dockerfile](d:/projects/klinik-starter/docker/be/Dockerfile) berubah
4. jika memang perlu rebuild, gunakan menu `Recreate service tertentu`

### 2.4.1. `localhost/dev/login` atau `127.0.0.1/dev/login` kadang `connection refused`

Masalah:

- saat operator memilih refresh FE dev, browser kadang mendapat:
  - `ERR_CONNECTION_REFUSED`
- gejalanya terlihat acak:
  - kadang login atau dashboard muncul
  - kadang `localhost/dev/login` seperti mati

Penyebab utama:

- arsitektur lama membuat `nginx` meneruskan `/dev/` ke proses FE runtime yang juga dipakai untuk build/refresh
- saat FE build ulang atau restart, proses upstream sempat tidak listen
- browser yang kena di momen itu menerima `connection refused` atau `502`

Dampak:

- user mengira FE dev atau route login rusak
- padahal yang putus sementara adalah jalur antara `nginx` dan frontend upstream

Apa yang sudah dilakukan:

- FE dev dan FE prod diubah ke model **build/publish static**
- container frontend sekarang berfungsi sebagai publisher, bukan web upstream runtime
- `nginx` melayani folder `current` secara langsung
- publisher berbasis Node menyalin asset lebih dulu lalu `index.html` paling akhir
- flow refresh FE di [run.bat](d:/projects/klinik-starter/docker/run.bat) sekarang memanggil publisher, bukan `vite preview`
- TDD batch, compose, nginx, dan vite config ditambah untuk mengunci arsitektur ini

Langkah solving:

1. gunakan opsi `2` untuk FE dev biasa
2. tunggu beberapa detik sampai FE dev siap
3. buka ulang:
  - `http://127.0.0.1/dev/`
  - `http://127.0.0.1/dev/login`
4. jika tetap gagal, cek status:

```powershell
docker compose ps
```

5. recreate `nginx` dan FE terkait hanya jika file compose atau konfigurasi gateway berubah, bukan untuk refresh code frontend biasa

### 2.5. `localhost` dari Windows host tidak selalu tembus ke WSL

Masalah:

- dari dalam WSL, service bisa sehat
- tetapi dari browser Windows host, `http://localhost/dev/` atau `http://localhost/prod/` kadang tidak terbuka

Dampak:

- user mengira FE atau API mati
- padahal masalahnya ada di jalur akses Windows ke WSL

Apa yang sudah dilakukan:

- dokumentasi sekarang menegaskan bahwa `localhost` bukan satu-satunya jalur akses
- hasil uji menunjukkan `127.0.0.1` stabil, sedangkan `localhost` bisa timeout di Windows host
- disiapkan fallback berbasis:
  - `127.0.0.1`
  - `IP_WSL`
  - `IP_SERVER`

Langkah solving:

1. cek dulu dari sisi stack apakah service sehat
2. jika `localhost` dari Windows gagal, coba:
  - `http://127.0.0.1/dev/`
  - `http://127.0.0.1/prod/`
3. jika `127.0.0.1` juga gagal, gunakan:
  - `http://IP_WSL/dev/`
  - `http://IP_WSL/prod/`
4. untuk operasional klinik, lebih baik langsung gunakan:
  - `http://IP_SERVER/dev/`
  - `http://IP_SERVER/prod/`

### 2.5.1. `/dev/api/health` sempat tetap 502 setelah opsi 3

Masalah:

- saat operator menjalankan opsi `3`, endpoint:
  - `http://127.0.0.1/dev/api/health`
  masih bisa mengembalikan `502 Bad Gateway`

Dampak:

- operator mengira backend dev mati
- gateway `nginx` terlihat rusak padahal akar masalahnya ada di flow refresh backend dev

Penyebab utama:

- helper sebelumnya melakukan `restart be_dev`
- restart manual itu membuka jendela downtime singkat
- route health API juga masih bergantung pada rule generic `/dev/api/`, belum punya jalur exact yang dikunci khusus

Apa yang sudah dilakukan:

- opsi `3` di [run.bat](d:/projects/klinik-starter/docker/run.bat) sekarang selalu menunggu dependency dev hidup dulu
- helper kini eksplisit menunggu gateway health setelah refresh backend dev
- helper sekarang menunggu gateway health:
  - `http://127.0.0.1/dev/api/health`
- `nginx` sekarang punya exact location:
  - `/dev/api/health`
  - `/prod/api/health`
- TDD ditambahkan untuk mengunci:
  - urutan wait health backend dev
  - helper wajib wait ke gateway health
  - exact location health route harus muncul sebelum generic API route

Langkah solving:

1. jalankan opsi `3` lagi
2. tunggu helper menyelesaikan wait health
3. cek:
  - `http://127.0.0.1/dev/api/health`
4. kalau masih gagal, cek:

```powershell
docker compose ps
docker compose logs --tail=200 be_dev
docker compose logs --tail=200 nginx
```

### 2.5.2. MinIO belum bisa hidup jika port `9000` atau `9001` bentrok

Masalah:

- container `minio` bisa gagal start walaupun compose sudah benar
- pada mesin ini sempat muncul error bind host port:
  - `failed to bind host port for 0.0.0.0:9000`

Dampak:

- `http://127.0.0.1:9001/` tidak bisa dibuka
- `MinIO Console` tidak tersedia walaupun service lain sudah sehat

Apa yang sudah dilakukan:

- flow startup utama sekarang memperlakukan `MinIO` sebagai service opsional
- jika `MinIO` gagal start, helper menampilkan warning khusus, bukan langsung mengklaim semua service sehat
- dokumen ini sekarang menandai konflik port 9000/9001 sebagai masalah environment mesin, bukan bug frontend/backend utama

Langkah solving:

1. cek apakah port 9000 atau 9001 dipakai proses lain
2. jika bentrok, bebaskan port tersebut atau ganti `MINIO_API_PORT` dan `MINIO_CONSOLE_PORT` di environment
3. jalankan ulang `MinIO`
4. verifikasi:
  - `http://127.0.0.1:9000/`
  - `http://127.0.0.1:9001/`

### 2.6. Login Jenkins sebelumnya mentok `403`

Masalah:

- sebelumnya hanya ada satu `jenkins`
- respons `403` dianggap tanda service hidup, tetapi belum ada bootstrap user yang jelas

Dampak:

- operator tidak punya kredensial baku
- CI/CD dev dan prod belum terpisah

Apa yang sudah dilakukan:

- `jenkins_dev` dan `jenkins_prod` sekarang dipisah
- admin user dibootstrap otomatis dengan:
  - username: `admin`
  - password: `qwerty123`
- route resmi sekarang:
  - `http://127.0.0.1/dev/cicd/`
  - `http://127.0.0.1/prod/cicd/`
- job pipeline awal juga disiapkan otomatis:
  - `fe-dev-delivery`
  - `be-dev-delivery`
  - `fe-prod-delivery`
  - `be-prod-delivery`

Langkah solving:

1. buka `CI/CD dev` atau `CI/CD prod`
2. login memakai:
  - `admin`
  - `qwerty123`
3. jalankan job sesuai environment yang dibutuhkan

### 2.7. Kekhawatiran data hilang saat rebuild

Masalah:

- sering ada kekhawatiran bahwa `docker compose build` atau `docker compose up --build` akan menghapus database dan storage backend

Dampak:

- operator takut rebuild
- proses solving jadi lambat

Apa yang sudah dilakukan:

- database dan runtime utama memakai **named volume**
- storage backend memakai **bind mount host**
- dokumentasi kini menjelaskan dengan tegas mana yang persisten

Langkah solving:

1. gunakan:
  - `docker compose stop`
  - `docker compose down`
2. jangan gunakan `docker compose down -v` sebagai kebiasaan harian
3. jangan hapus folder storage host atau volume Docker secara manual kalau tidak benar-benar diperlukan

## 3. Apa Saja yang Sudah Dikerjakan

Yang sudah dikerjakan di setup Docker:

- [run.bat](d:/projects/klinik-starter/docker/run.bat) direvisi untuk mode `dev` dan `prod`
- [start_step_by_step.bat](d:/projects/klinik-starter/docker/start_step_by_step.bat) ditambahkan untuk startup manual bertahap
- database dipisah menjadi:
  - `postgres_dev`
  - `postgres_prod`
- Redis dipisah menjadi:
  - `redis_dev`
  - `redis_prod`
- CI/CD dipisah menjadi:
  - `jenkins_dev`
  - `jenkins_prod`
- flow lama yang tidak dipakai lagi sudah dikeluarkan dari jalur operasional utama
- helper sekarang bisa fallback ke `WSL`
- opsi `1` di [run.bat](d:/projects/klinik-starter/docker/run.bat) sekarang:
  - stop container yang sedang berjalan
  - lalu menyalakan infra utama lebih dulu
  - mencoba menyalakan `MinIO` sebagai service opsional
  - lalu menyalakan service aplikasi
  - lalu publish ulang FE dev dan FE prod
- opsi `1-6` sekarang dijalankan di background lalu batch utama langsung keluar
- opsi `2-5` sekarang fokus ke refresh code/container, bukan rebuild image
- refresh biasa tidak lagi ikut memutus `nginx`
- output helper sekarang mengarahkan akses lokal ke `127.0.0.1`
- FE dev dan FE prod sekarang lebih stabil karena tidak lagi dilayani sebagai upstream `vite preview`
- Jenkins dev dan prod sekarang dibuild dari image custom yang sudah memuat:
  - `docker`
  - `git`
  - `curl`
  - plugin pipeline yang dibutuhkan
- frontend sekarang dipublish ke:
  - `docker/runtime/local/fe_dev_public/current`
  - `docker/runtime/local/fe_prod_public/current`
- `nginx` sekarang melayani folder static publish itu secara langsung
- Jenkins home sekarang disimpan di host:
  - `docker/runtime/local/jenkins_dev`
  - `docker/runtime/local/jenkins_prod`
- database PostgreSQL sekarang disimpan di host:
  - `docker/runtime/local/postgres_dev`
  - `docker/runtime/local/postgres_prod`
- root cause FE prod `vue-tsc: not found` sudah ditutup dengan install dev dependencies pada publisher container
- frontend disesuaikan agar FE dev dan FE prod bisa hidup di bawah prefix:
  - `/dev/`
  - `/prod/`
- CI/CD disesuaikan agar hidup di bawah prefix:
  - `/dev/cicd/`
  - `/prod/cicd/`
- backend dev dan prod lokal disesuaikan untuk membaca source code langsung dari host mount
- konfigurasi Nginx diperbarui di [dev-prod.conf](d:/projects/klinik-starter/docker/nginx/dev-prod.conf)
- dokumentasi operasional di [docker_run.md](d:/projects/klinik-starter/docker/docker_run.md) diperbarui supaya siap copas

## 4. Menu run.bat yang Berlaku Sekarang

Menu utama [run.bat](d:/projects/klinik-starter/docker/run.bat):

1. `Jalankan semua container`
2. `Build dan publish FE dev`
3. `Jalankan atau refresh BE dev`
4. `Build dan publish FE prod`
5. `Jalankan atau refresh BE prod`
6. `Start service tertentu`
7. `Stop service tertentu`
8. `Recreate service tertentu`
9. `Cek status container`
10. `Lihat logs container`
11. `Stop stack lokal utama`
12. `Stop semua stack proyek`
13. `Tampilkan URL akses penting`
14. `Buka panduan docker_run.md`
15. `Keluar`

Catatan:

- flow utama sekarang hanya `dev` dan `prod`
- untuk opsi `1-6`, setelah dipilih helper langsung keluar dan tidak menunggu di menu
- service internal yang dipakai juga sudah:
  - `be_dev`
  - `be_prod`
  - `fe_dev`
  - `fe_prod`

## 5. Jalur Akses yang Benar

### 5.0. Jalur akses resmi setelah stack dev dan prod hidup

Jalur yang perlu dipakai sekarang:

- FE dev: `http://127.0.0.1/dev/`
- FE prod: `http://127.0.0.1/prod/`
- API dev: `http://127.0.0.1/dev/api/health`
- API prod: `http://127.0.0.1/prod/api/health`
- CI/CD dev: `http://127.0.0.1/dev/cicd/`
- CI/CD prod: `http://127.0.0.1/prod/cicd/`
- PgAdmin: `http://127.0.0.1:5050/`
- MinIO API: `http://127.0.0.1:9000/`
- MinIO Console: `http://127.0.0.1:9001/`

Kredensial Jenkins baku:

- username: `admin`
- password: `qwerty123`

### 5.1. Dari server lokal itu sendiri

- FE dev: `http://127.0.0.1/dev/`
- FE prod: `http://127.0.0.1/prod/`
- API dev: `http://127.0.0.1/dev/api/health`
- API prod: `http://127.0.0.1/prod/api/health`
- CI/CD dev: `http://127.0.0.1/dev/cicd/`
- CI/CD prod: `http://127.0.0.1/prod/cicd/`
- PgAdmin: `http://127.0.0.1:5050/`
- MinIO API: `http://127.0.0.1:9000/`
- MinIO Console: `http://127.0.0.1:9001/`

### 5.2. Dari komputer lain di jaringan klinik

- FE dev: `http://IP_SERVER/dev/`
- FE prod: `http://IP_SERVER/prod/`
- API dev: `http://IP_SERVER/dev/api/health`
- API prod: `http://IP_SERVER/prod/api/health`
- CI/CD dev: `http://IP_SERVER/dev/cicd/`
- CI/CD prod: `http://IP_SERVER/prod/cicd/`

### 5.3. Jika Docker berjalan di WSL dan localhost tidak tembus

Gunakan:

- FE dev: `http://IP_WSL/dev/`
- FE prod: `http://IP_WSL/prod/`
- CI/CD dev: `http://IP_WSL/dev/cicd/`
- CI/CD prod: `http://IP_WSL/prod/cicd/`

## 6. Persistensi Data dan Keamanan Data

### 6.1. Named volume Docker

Pada mode utama, data berikut memakai bind mount host:

- `docker/runtime/local/postgres_dev`
- `docker/runtime/local/postgres_prod`
- `docker/runtime/local/redis_dev`
- `docker/runtime/local/redis_prod`
- `docker/runtime/local/jenkins_dev`
- `docker/runtime/local/jenkins_prod`
- `docker/runtime/local/be_dev_storage`
- `docker/runtime/local/be_prod_storage`
- `docker/runtime/local/fe_dev_public`
- `docker/runtime/local/fe_prod_public`

Maknanya:

- data tetap ada walaupun container direcreate
- `build` biasa tidak menghapus data
- `down -v` tidak lagi menjadi ancaman utama untuk DB dan Jenkins, karena data utamanya ada di host

### 6.2. Bind mount host

Named volume yang masih dipakai sekarang lebih ke cache/build helper:

- `pgadmin_data`
- `minio_data`
- `fe_dev_node_modules`
- `fe_prod_node_modules`
- `be_dev_go_mod_cache`
- `be_dev_go_build_cache`
- `be_prod_go_mod_cache`
- `be_prod_go_build_cache`

Maknanya:

- cache dan helper runtime tetap persisten selama volume tidak dihapus manual
- tetapi data bisnis inti sekarang sudah dipindah ke host bind mount

### 6.3. Kesimpulan

Secara umum, ini **aman**:

- `docker compose build`
- `docker compose up -d --build`
- recreate container biasa

Selama:

- volume tidak dihapus manual
- folder storage host tidak dihapus manual
- Anda tidak memakai `docker compose down -v` sembarangan

## 7. Langkah Solving Cepat

### 7.1. Jika FE dev tidak bisa dibuka

Urutan solving:

1. cek status stack:

```powershell
docker compose ps
```

2. cek log FE dev:

```powershell
docker compose logs -f fe_dev
```

3. pastikan file publish benar-benar ada:

```powershell
dir D:\projects\klinik-starter\docker\runtime\local\fe_dev_public\current
```

4. cek log gateway:

```powershell
docker compose logs -f nginx
```

5. cek API dev:

```powershell
curl http://localhost/dev/api/health
```

6. jika Windows tidak tembus ke `localhost`, gunakan:
  - `IP_WSL`
  - atau `IP_SERVER`

### 7.2. Jika FE prod tidak bisa dibuka

Urutan solving:

1. cek status:

```powershell
docker compose ps
```

2. cek log FE prod:

```powershell
docker compose logs -f fe_prod
```

3. cek API prod:

```powershell
curl http://localhost/prod/api/health
```

### 7.3. Jika asset FE `404`

Langkah solving:

1. recreate service frontend dan Nginx:

```powershell
docker compose up -d --force-recreate --no-deps fe_dev fe_prod nginx
```

2. publish ulang frontend yang dipakai:

```powershell
docker compose exec -T fe_dev sh -lc "node ./scripts/docker_publish_frontend.js dev /publish/dev"
docker compose exec -T fe_prod sh -lc "node ./scripts/docker_publish_frontend.js prod /publish/prod"
```

3. hard refresh browser
4. jika masalah muncul setelah perubahan dependency atau Dockerfile, baru lakukan rebuild image lewat menu `Recreate service tertentu`

## 8. Aturan Aman Saat Start dan Stop

Yang direkomendasikan:

- untuk berhenti sementara:
  - `docker compose stop`
- untuk menurunkan stack:
  - `docker compose down`

Yang tidak direkomendasikan sebagai kebiasaan harian:

- `docker compose down -v`

Karena:

- bisa menghapus named volume
- bisa menghapus data yang sebenarnya masih ingin dipertahankan

## 9. Saran Terbaik

Untuk server lokal klinik:

1. gunakan [docker-compose.yml](d:/projects/klinik-starter/docker/docker-compose.yml) sebagai mode utama
2. gunakan [run.bat](d:/projects/klinik-starter/docker/run.bat) untuk operator
3. gunakan akses berbasis `IP_SERVER`, bukan mengandalkan `localhost`
4. backup volume penting dan folder storage host secara berkala
5. jika FE bermasalah, recreate frontend terkait dan `nginx`
6. jangan jadikan `down -v` sebagai cara stop harian

## 10. Ringkasan Akhir

Kesimpulan praktis:

- flow Docker sekarang hanya `dev` dan `prod`
- FE dev diakses lewat `/dev/`
- FE prod diakses lewat `/prod/`
- dokumentasi ini fokus pada masalah nyata, apa yang sudah dilakukan, dan langkah solving yang jelas
- untuk client klinik, akses terbaik tetap:
  - `http://IP_SERVER/dev/`
  - `http://IP_SERVER/prod/`
