# Error Catalog

Semua contoh memakai envelope response standar.

## 400 Bad Request

Contoh:

    status: error
    message: Permintaan tidak valid.
    data: null
    errors: null

## 500 Server Error

Contoh:

    status: error
    message: Terjadi kesalahan server.
    data: null
    errors: null

## 422 Validation Error

Contoh:

    status: error
    message: Data yang dikirim tidak valid.
    data: null
    errors:
      name:
        - Nama wajib diisi.

## 429 Too Many Requests

Contoh:

    status: error
    message: Terlalu banyak percobaan. Silakan coba lagi nanti.
    data: null
    errors: null

## 404 Not Found

Contoh:

    status: error
    message: Data tidak ditemukan.
    data: null
    errors: null

## 409 Conflict

Contoh:

    status: error
    message: Data sudah digunakan.
    data: null
    errors: null

## 401 Unauthorized

Contoh:

    status: error
    message: Token tidak valid.
    data: null
    errors: null

## 403 Forbidden

Contoh:

    status: error
    message: Anda tidak memiliki hak akses.
    data: null
    errors: null
