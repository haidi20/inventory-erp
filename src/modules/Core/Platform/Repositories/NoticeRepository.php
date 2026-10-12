<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use Illuminate\Support\Collection;

class NoticeRepository
{
    /**
     * @return Collection<NoticeEntity>
     */
    public function all(): Collection
    {
        $data = [
            [
                'id' => 'operasional-pagi',
                'title' => 'Validasi identitas pasien sebelum pelayanan',
                'message' => 'Pastikan NIK, nomor rekam medis, dan penjamin pasien sudah sesuai sebelum pasien masuk ke poli atau dibuatkan SEP.',
                'audience' => 'Pendaftaran dan Kasir',
                'priority' => 'high',
                'status' => 'baru',
                'updated_at' => now()->copy()->setTime(7, 30)->timestamp,
                'action_label' => 'Buka Pendaftaran',
                'action_url' => '/pendaftaran',
            ],
            [
                'id' => 'satusehat-review',
                'title' => 'Sinkronisasi SATUSEHAT perlu review data pasien',
                'message' => 'Lengkapi data identitas pasien yang belum memiliki NIK agar proses sinkronisasi dan pengiriman encounter tidak tertahan.',
                'audience' => 'Pendaftaran dan Rekam Medis',
                'priority' => 'medium',
                'status' => 'aktif',
                'updated_at' => now()->copy()->subDay()->setTime(16, 15)->timestamp,
                'action_label' => 'Buka SATUSEHAT',
                'action_url' => '/satusehat/sync',
            ],
            [
                'id' => 'bpjs-credential',
                'title' => 'Lengkapi kredensial bridging BPJS',
                'message' => 'Consumer ID, user key, dan kode PPK masih kosong. Halaman verifikasi, SEP, dan monitoring klaim akan tetap placeholder sampai konfigurasi dilengkapi.',
                'audience' => 'Admin dan Owner',
                'priority' => 'high',
                'status' => 'butuh aksi',
                'updated_at' => now()->copy()->subDays(2)->setTime(9, 45)->timestamp,
                'action_label' => 'Buka Pengaturan BPJS',
                'action_url' => '/bpjs/pengaturan',
            ],
            [
                'id' => 'keuangan-harian',
                'title' => 'Rekonsiliasi kas harian belum ditutup',
                'message' => 'Cocokkan mutasi kas dan bank setelah operasional selesai agar laporan arus kas dan saldo harian tetap akurat.',
                'audience' => 'Kasir dan Keuangan',
                'priority' => 'medium',
                'status' => 'monitor',
                'updated_at' => now()->copy()->subHours(5)->timestamp,
                'action_label' => 'Lihat Kas dan Bank',
                'action_url' => '/transaksi/kas-bank',
            ],
        ];

        return collect($data)->map(fn($item) => NoticeEntity::fromArray($item));
    }
}
