# Postman Review Checklist

Centang setiap item sebelum menyetujui pull request yang menyentuh API:

- [ ] Tidak ada file endpoint, controller, route, atau request baru yang
  lolos tanpa representasi di collection Postman.
- [ ] `inventory-api.postman_collection.json` diperbarui.
- [ ] Field `info.version` dinaikkan bila contract API berubah.
- [ ] `CHANGELOG.md` memuat perubahan endpoint atau response.
- [ ] Tidak ada kredensial real di collection atau file yang di-commit.
- [ ] Environment lokal hanya memakai `*.local.postman_environment.json`.
