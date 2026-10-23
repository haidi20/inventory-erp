# Postman Governance

## Kredensial

- Collection JSON tidak boleh berisi token, password, API key, cookie, atau
  kredensial production/staging.
- Autentikasi hanya boleh memakai variable, misalnya `{{api_token}}`.
- File environment yang berisi kredensial lokal wajib memakai nama
  `*.local.postman_environment.json`; pola tersebut sudah di-gitignore.
- Template yang boleh di-commit hanya
  `environment.template.json`, dengan token kosong dan domain lokal/dummy.

## Versioning

- Field `info.version` pada collection wajib diisi.
- Setiap perubahan endpoint, request, atau kontrak response wajib menaikkan
  versi dan menambahkan entri di `CHANGELOG.md`.

## Aturan Pembaruan

Setiap pull request yang menambah atau mengubah endpoint wajib:

1. Memperbarui `inventory-api.postman_collection.json`.
2. Menyesuaikan folder `Overview` bila konvensi API berubah.
3. Memperbarui `CHANGELOG.md` dan field `info.version`.
4. Menjalankan audit kredensial sebelum commit.

## Review

Reviewer wajib memakai `REVIEW_CHECKLIST.md` dan menolak perubahan endpoint
yang tidak menyertakan collection dan changelog terbaru.
