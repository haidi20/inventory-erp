# AGENTS.md — Finance

Folder ini mencakup sub-modul `Journal` dan `Ledger`.

## Batas Modul

- Finance adalah pemilik COA, jurnal, ledger, closing, dan laporan keuangan
  inti.
- Modul lain hanya boleh memakai Finance melalui Service publik berbasis
  Entity/DTO.
- Analytics hanya boleh membaca hasil/report; tidak boleh menulis tabel domain
  Finance.

## Kontrak Khusus

- Pembentukan jurnal dan perhitungan saldo harus berada di Service/Repository,
  bukan di controller atau view.
- Transaksi jurnal multi-line harus atomic di Repository.
- Jaga konsistensi debit/kredit, periode closing, dan audit trail.
- Jangan mengubah struktur akun atau mapping jurnal tanpa memeriksa dampak ke
  Clinical, Operational, dan Analytics.

## Testing Fokus

- Uji pembuatan jurnal, posting, closing, dan pembacaan saldo.
- Skenario minimal: validasi akun, transaksi tidak seimbang, periode tertutup,
  unauthorized, dan isolasi company/branch.

