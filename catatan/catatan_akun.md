# Catatan Akun User — Klinik Starter

> **PENTING:** File ini hanya untuk keperluan development/testing lokal.
> Jangan commit file ini ke repo publik atau gunakan password ini di production.

---

## Akun Login Aplikasi

| Nama | Email | Password | Role | Keterangan |
|---|---|---|---|---|
| Admin Klinik | `admin@klinik.local` | `admin123` | admin | Akses penuh semua menu |
| Denny Octavia | `dokter1@klinik.local` | `password` | dokter | Dokter + linked ke employee |
| Eko Setiana | `dokter2@klinik.local` | `password` | dokter | Dokter + linked ke employee |
| Kasir Klinik | `kasir@klinik.local` | `password` | kasir | Kasir + linked ke employee |

---

## Role & Permission

| Role | Permission |
|---|---|
| `admin` | master.manage, operational.manage, finance.manage, report.view, integration.manage |
| `dokter` | — (belum assign, konfigurasi via UI) |
| `kasir` | — (belum assign, konfigurasi via UI) |
| `owner` | — (belum assign, konfigurasi via UI) |

---

## Akun Infrastruktur

### PostgreSQL
| | |
|---|---|
| Host | `localhost:5432` (atau sesuai DB_PORT_FORWARD) |
| Database | `klinik` |
| Username | `klinik` |
| Password | `klinik` |

### Redis
| | |
|---|---|
| Host | `localhost:6379` |
| Password | _(kosong, tanpa password)_ |

### MinIO Console
| | |
|---|---|
| URL | `http://localhost:9101` |
| Username | `minioadmin` |
| Password | `minioadmin123` |
| Bucket | `klinik-starter` |

---

## Cara Reset Password (Development)

**Via artisan tinker:**
```bash
docker compose exec app php artisan tinker
>>> \App\Models\User::where('email','admin@klinik.local')->first()->update(['password' => bcrypt('password_baru')]);
```

**Via fitur Reset Password di aplikasi:**
1. Buka `http://localhost:8080/forgot-password`
2. Masukkan email
3. Cek token di log: `docker compose exec app tail -f storage/logs/laravel.log`
4. Buka URL reset dari log

---

## Cara Tambah User Baru (Seeder)

Edit file `database/seeders/DemoSeeder.php`, tambahkan ke array `$users`:
```php
'email_baru@klinik.local' => ['name' => 'Nama User', 'role' => 'kasir'],
```
Lalu jalankan:
```bash
make seed
```

---

## Cara Login Pertama Kali

```bash
# 1. Pastikan container jalan
make up

# 2. Jalankan migration + seeder
make migrate
make seed

# 3. Buka browser
# http://localhost:8080/login
# Login dengan: admin@klinik.local / admin123
```
