# AGENTS.md — Core

Folder ini mencakup sub-modul `Identity`, `Organization`, `Authorization`,
`Platform`, dan `shared`.

## Batas Modul

- Core adalah fondasi user, perusahaan, cabang, auth, RBAC, settings, dan
  platform.
- Core tidak boleh mengimpor modul `Clinical`, `Operational`, `Finance`, atau
  `Analytics`.
- Kode lintas modul yang benar-benar umum ditempatkan di shared layer, bukan di
  modul bisnis lain.

## Kontrak Khusus

- Jaga konsistensi `company_uuid` dan `branch_uuid` pada data milik
  perusahaan/cabang.
- Superadmin boleh bypass scope hanya untuk fitur administratif lintas scope;
  pengguna biasa harus tetap terisolasi.
- Perubahan auth, role, permission, atau menu wajib menguji unauthorized,
  forbidden, dan scope user.
- Endpoint auth, user, role, permission, dan sidebar tidak boleh diubah tanpa
  mengecek keputusan freeze endpoint.

## Testing Fokus

- Prioritaskan test fokus pada controller/service yang diubah.
- Skenario minimal: login sukses/gagal, session/token, unauthorized,
  forbidden, CRUD role/permission, dan isolasi company/branch.

