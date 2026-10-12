---
name: gitpush
description: Update from remote, verify relevant changes, commit in Indonesian, and push the active branch when explicitly requested.
---

# Git Push Workflow

Gunakan workflow ini ketika pengguna secara eksplisit meminta perubahan dipush atau meminta `$gitpush`. Jangan melakukan push hanya karena tugas coding selesai.

## Urutan kerja

1. **Periksa kondisi Git dan branch aktif.** Jalankan `git status --short` dan `git branch --show-current`. Jangan berpindah branch. Tinjau perubahan staged, unstaged, dan untracked agar perubahan pengguna yang tidak terkait tidak ikut ter-commit.
2. **Tarik pembaruan remote terlebih dahulu.** Jalankan `git pull --ff-only` pada branch aktif. Jika pull gagal, branch tidak punya upstream, atau fast-forward tidak dapat dilakukan, berhenti dan jelaskan masalahnya. Jangan reset, rebase, stash, atau menyelesaikan konflik tanpa arahan pengguna.
3. **Jalankan pemeriksaan tipe.** Pilih perintah yang sesuai dengan proyek, misalnya `npm run typecheck` atau `tsc`. Laporkan kegagalan dan berhenti sebelum commit/push jika ada error.
4. **Jalankan tes yang relevan saja.** Pilih unit/feature test yang mencakup perubahan. Jangan menjalankan seluruh suite tanpa alasan. Jika tes gagal, laporkan dan berhenti sebelum commit/push.
5. **Stage perubahan yang dimaksud saja.** Jangan stage semua perubahan secara membabi buta. File dengan ekstensi `.txt` wajib dikecualikan. Pastikan perubahan staged sudah ditinjau dan hanya berisi file yang memang diminta.
6. **Buat commit message seluruhnya dalam Bahasa Indonesia** dan ikuti standar pesan Git: subject satu baris yang ringkas dan jelas, baris kosong, lalu body opsional yang menjelaskan perubahan. Subject tetap memakai format proyek `[Tipe] Judul Pesan` (contoh: `[Fix] Perbaiki validasi login`, `[Refactor] Rapikan pemetaan modul laporan`). Jangan mengganti format ini dengan format lain.
   - Sebelum menulis message, tinjau `git diff --cached --stat` dan `git diff --cached` agar subject dan body mencerminkan perubahan yang benar-benar di-stage.
   - Body harus memberi penjelasan lengkap dalam Bahasa Indonesia tentang perubahan utama dan alasan/efeknya bila terlihat dari diff. Cantumkan kelompok perubahan penting dalam butir singkat bila membantu; jangan mengarang konteks yang tidak didukung diff.
   - Pisahkan subject dan body dengan satu baris kosong. Jaga subject tetap mudah dipindai; taruh rincian di body, bukan dengan memanjangkan subject.
7. **Push ke branch aktif.** Jangan checkout branch lain. Push `HEAD` ke upstream branch aktif (umumnya `git push`; jika upstream belum disetel, pastikan nama remote dan branch tujuan dari konfigurasi Git sebelum menggunakan `git push -u <remote> <branch-aktif>`). Jika push ditolak, jangan force-push; jelaskan penolakan dan berhenti.
8. **Laporkan hasil.** Sebutkan ringkasan perubahan, hasil typecheck dan tes, commit hash/pesan, serta branch tujuan push. Berikan penjelasan detail mengenai pekerjaan yang disertakan.

## Batas keselamatan

- Pengguna harus secara eksplisit meminta tindakan push sebelum menjalankan `pull`, `add`, `commit`, atau `push` sebagai bagian workflow ini. Pemanggilan `$gitpush` atau permintaan eksplisit "push" memenuhi syarat tersebut.
- Pull dilakukan lebih dulu sesuai workflow. `--ff-only` mencegah pull membuat merge commit otomatis; jika tidak bisa fast-forward, berhenti dan minta arahan.
- Jangan memasukkan perubahan yang tidak terkait atau perubahan staged milik pengguna ke commit. Bila batas perubahan yang dimaksud tidak jelas, tanyakan sebelum staging.
- Jangan pernah memasukkan file `.txt`, memakai force-push, atau menjalankan operasi destruktif untuk memaksa workflow selesai.
- Jika tidak ada perubahan untuk di-commit setelah pull, jangan membuat commit kosong. Laporkan bahwa branch sudah diperbarui dan tidak ada perubahan untuk dipush.

