# AGENTS.md — Operational

Folder ini mencakup sub-modul `Master`, `Inventory`, `Procurement`, `Sales`,
dan `Manufacturing`.

## Batas Modul

- Operational adalah pemilik master item, unit, warehouse, supplier, harga,
  inventori, pembelian, penjualan, dan manufacturing.
- Modul lain hanya boleh memakai data Operational melalui Service publik yang
  menerima/mengembalikan Entity/DTO.
- Jangan import Repository dari modul lain; batas antar modul wajib dijaga.
- Endpoint master dan transaksi ERP sudah dibekukan; cek `_plan.md` sebelum
  mengganti URL.

## Kontrak Khusus

- CRUD master wajib mempertahankan scope company/branch dan isolasi superadmin.
- Form produk harus menyimpan seluruh input pada semua tab melalui
  `ProductService` dan `ProductRepository`, bukan query model langsung dari
  controller/view.
- Mutasi stok dan transaksi multi-tabel harus atomic di Repository.
- Relasi produk seperti akun, satuan, harga, saldo awal, dan diskon wajib
  disinkronkan melalui satu titik repository/service yang jelas.

## Testing Fokus

- Uji CRUD master, transaksi pembelian/penjualan, dan mutasi stok dengan data
  nyata pada feature test.
- Wajib menguji validasi, duplicate code, not found, unauthorized, dan scope
  company/branch.
- Untuk perubahan form produk, sertakan test persistensi semua input/tab dan
  test UI Playwright bila alurnya berubah.

