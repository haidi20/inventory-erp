# AGENTS.md — Clinical

Folder ini mencakup sub-modul `Registration`, `MedicalRecord`, `Pharmacy`,
`Ancillary`, `Classification`, dan `Integration`.

## Batas Modul

- Clinical boleh bergantung pada Core.
- Data milik modul lain hanya boleh diakses melalui Service publik modul
  pemilik domain dengan Entity/DTO.
- Pharmacy tidak boleh memodifikasi stok langsung ke tabel `Operational`;
  gunakan Service Inventory milik Operational.
- Analytics tidak boleh ditulis dari Clinical; Clinical hanya menghasilkan data
  domain atau event yang relevan.

## Kontrak Khusus

- Jaga privacy dan integritas data pasien serta rekam medis.
- Perubahan clinical flow harus mempertimbangkan riwayat medis dan jejak audit.
- Integrasi eksternal seperti SATUSEHAT/BPJS harus tetap terisolasi di
  `Clinical/Integration` dan tidak bocor ke controller/view.
- Jangan mengubah endpoint pelayanan medis tanpa mengecek mapping endpoint.

## Testing Fokus

- Uji alur registrasi, encounter, rekam medis, atau dispensing pada level
  feature test.
- Skenario wajib mencakup validasi, data tidak ditemukan, unauthorized,
  isolasi company/branch, dan efek transaksi bila memengaruhi stok atau jurnal.

