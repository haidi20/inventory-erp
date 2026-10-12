# AGENTS.md — Analytics

Folder ini mencakup sub-modul `Dashboard` dan `Reports`.

## Batas Modul

- Analytics bersifat read-only terhadap data modul lain.
- Tidak boleh menulis ke tabel domain Clinical, Operational, Finance, atau
  Core.
- Baca data melalui Service/repository/query yang menjadi kontrak modul
  pemilik data; jangan menembus batas modul.

## Kontrak Khusus

- Laporan dan dashboard boleh melakukan agregasi, tetapi tidak boleh mengubah
  sumber data.
- Jaga performa query: gunakan eager loading, filter tanggal, agregasi di query,
  dan hindari N+1.
- Endpoint report yang sudah dibekukan tidak boleh diubah tanpa mengecek
  dokumentasi endpoint.

## Testing Fokus

- Uji response, filter, pagination, authorization, dan scope company/branch.
- Untuk laporan keuangan/stok, pastikan angka sesuai sumber data dan tidak
  mengubah data domain.

