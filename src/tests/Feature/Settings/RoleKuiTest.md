# KUI Test Scenarios - Role Management

Dokumentasi pengujian standar **kui** (Keyboard-first, Informative UI) untuk fitur Manajemen Role.

## 1. Keyboard Shortcuts

| Shortcut | Skenario | Hasil yang Diharapkan |
|----------|----------|-----------------------|
| `Ctrl + S` | Tekan saat form terbuka dan data valid | Form tersubmit (Simpan/Update) dan modal tertutup |
| `Esc` | Tekan saat form terbuka | Modal tertutup (Batal) tanpa menyimpan perubahan |
| `F1` | Tekan saat modal aktif | Bagian Bantuan (Help Section) muncul/hilang secara toggle |
| `Tab` | Tekan berulang kali | Fokus berpindah secara berurutan: Input Label -> Input Name -> Search Permission -> Grid Permission -> Tombol Batal -> Tombol Simpan |
| `Space / Enter` | Tekan saat fokus pada item di Grid Permission | Checkbox izin terpilih/tidak terpilih secara toggle |

## 2. Informative UI (User Guidance)

| Elemen | Kriteria Verifikasi | Hasil yang Diharapkan |
|--------|---------------------|-----------------------|
| Ikon Info (ⓘ) | Klik ikon di header | Muncul panel bantuan dengan langkah-langkah dan daftar shortcut |
| Field Help (?) | Hover pada ikon `?` di label "Nama Role" | Muncul tooltip penjelasan fungsi field |
| Field Help (?) | Hover pada ikon `?` di label "Role ID" | Muncul tooltip penjelasan batasan format (slug) |
| Shortcut Hint | Lihat tombol Simpan | Teks tombol menyertakan petunjuk shortcut: `(Ctrl+S)` |
| Shortcut Hint | Lihat tombol Batal | Teks tombol menyertakan petunjuk shortcut: `(Esc)` |

## 3. Keyboard Accessibility (Permission Grid)

- [ ] Setiap item izin dalam grid dapat dijangkau menggunakan tombol `Tab`.
- [ ] Item yang sedang fokus memiliki indikator visual (ring/border highlight).
- [ ] Menekan `Enter` atau `Space` pada item yang fokus berhasil mengubah status centang (checked/unchecked).
- [ ] Pencarian izin (`Ctrl + F` atau klik search) membatasi daftar izin tanpa menghilangkan kemampuan navigasi keyboard.

## 4. Status Validasi

- **Typecheck**: LULUS (`npm run typecheck`)
- **Build**: LULUS (`npm run build`)
- **Unit Test (UI)**: Direncanakan menggunakan Playwright untuk verifikasi shortcut fisik.
