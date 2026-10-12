<?php

namespace App\Modules\Finance\Journal\Enums;

/**
 * Enum untuk tipe transaksi jurnal
 */
enum JournalReferenceType: string
{
    /**
     * Menu: Penjualan Obat (POS / Resep Luar)
     * Digunakan saat ada transaksi penjualan obat langsung di Apotek.
     */
    case SALE = 'sale';

    /**
     * Menu: Pembelian (PBF / Supplier)
     * Digunakan saat klinik membeli stok obat/alkes dari supplier.
     */
    case PURCHASE = 'purchase';

    /**
     * Menu: Pemeriksaan (Rekam Medis)
     * Digunakan setelah dokter selesai memeriksa pasien dan meresepkan obat.
     */
    case ENCOUNTER = 'encounter';

    /**
     * Menu: Pendaftaran Pasien
     * Digunakan jika ada biaya pendaftaran awal sebelum pasien diperiksa (opsional).
     */
    case REGISTRATION = 'registration';

    /**
     * Menu: Penyesuaian Stok (Stock Opname)
     * Digunakan saat ada selisih stok (rusak, hilang, atau lebih) di Gudang/Apotek.
     */
    case STOCK_ADJUSTMENT = 'stock_adjustment';

    /**
     * Menu: Penjualan Obat (Kasir POS Ritel)
     * Varian transaksi kasir tunai langsung tanpa resep dokter.
     */
    case POS_SALE = 'pos_sales';

    /**
     * Menu: Pembayaran Hutang
     * Digunakan saat klinik membayar tagihan pembelian ke Supplier (PBF).
     */
    case DEBT_PAYMENT = 'debt_payment';

    /**
     * Menu: Penerimaan Piutang
     * Digunakan saat pasien, asuransi, atau BPJS melunasi tunggakan cicilan/tagihan.
     */
    case RECEIVABLE_COLLECTION = 'receivable_collection';

    /**
     * Menu: Biaya Operasional / Pengeluaran Umum
     * Digunakan untuk mencatat beban biaya seperti Listrik, Air, Gaji Karyawan, dll.
     */
    case EXPENSE = 'expense';

    /**
     * Menu: Jurnal Umum / Manual
     * Digunakan untuk *entry* jurnal akuntansi yang diketik langsung oleh Akuntan.
     * Contoh:
     * 1. Setoran modal tambahan dari pemilik secara tunai.
     * 2. Reklasifikasi saldo antar Kas dan Bank.
     * 3. Pembayaran piutang karyawan (peminjaman kas).
     */
    case GENERAL = 'general';

    /**
     * Menu: Jurnal Penyesuaian (Akhir Bulan)
     * Digunakan oleh Akuntan untuk menyusutkan aset atau menyesuaikan saldo akhir periode.
     * Contoh:
     * 1. Penyusutan aset tetap bulanan (misal: Alat Medis, Bangunan).
     * 2. Pembebanan biaya sewa dibayar di muka (prepaid) yang sudah jatuh tempo.
     * 3. Akrual bunga bank atau penyesuaian selisih kurs akhir bulan.
     */
    case ADJUSTMENT = 'adjustment';

    /**
     * Transaksi Pembalikan (Reversal)
     * Digunakan oleh sistem secara otomatis saat sebuah transaksi (misal Penjualan) dibatalkan/diretur.
     */
    case REVERSAL = 'reversal';

    /**
     * Menu: Pengaturan Saldo Awal
     * Digunakan saat admin mengatur saldo pembukaan akun (Opening Balance).
     */
    case OPENING_BALANCE = 'opening_balance';

    /**
     * Menu: Retur Pembelian (Supplier)
     * Digunakan saat mengembalikan barang ke supplier dan mengurangi hutang/menambah kas.
     */
    case PURCHASE_RETURN = 'purchase_return';

    /**
     * Menu: Layanan Laboratorium
     * Digunakan untuk mencatat pendapatan dari jasa pemeriksaan lab.
     */
    case LABORATORY = 'laboratory';

    /**
     * Menu: Layanan Radiologi
     * Digunakan untuk mencatat pendapatan dari jasa pemeriksaan radiologi (X-Ray, dll).
     */
    case RADIOLOGY = 'radiology';
}
