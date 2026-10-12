<?php

namespace App\Modules\Finance\Journal\Enums;

/**
 * Enum untuk sumber nilai (nominal) jurnal.
 * Digunakan untuk pemetaan akun di tabel account_mappings.
 */
enum JournalValueSource: string
{
    /** 
     * Total transaksi akhir (Grand Total).
     * Kasus: Transaksi Penjualan Obat seharga Rp 100.000 + PPN 11% = Rp 111.000.
     * Nominal `TOTAL_GROSS` = Rp 111.000 (Biasanya masuk Kas/Piutang).
     */
    case TOTAL_GROSS = 'total_gross';

    /** 
     * Nilai bersih sebelum pajak (DPP / Dasar Pengenaan Pajak).
     * Kasus: Transaksi Penjualan Obat Rp 111.000 (inklusif PPN).
     * Nominal `TOTAL_NET` = Rp 100.000 (Biasanya masuk Pendapatan).
     */
    case TOTAL_NET = 'total_net';

    /** 
     * Nilai pajak saja (PPN).
     * Kasus: Transaksi Penjualan Obat Rp 111.000 (inklusif PPN).
     * Nominal `TOTAL_TAX` = Rp 11.000 (Biasanya masuk Hutang PPN).
     */
    case TOTAL_TAX = 'total_tax';

    /** 
     * Harga Pokok Penjualan (HPP) / Total Modal.
     * Kasus: Jual 2 strip Paracetamol (HPP per strip Rp 5.000).
     * Nominal `TOTAL_COST` = Rp 10.000 (Biasanya masuk HPP dan mengurangi Persediaan).
     */
    case TOTAL_COST = 'total_cost';

    /** 
     * Jumlah yang dibayar saat ini (Uang Pemasukan Asli).
     * Kasus: Tagihan Rp 100.000, tapi pasien baru bayar DP Rp 40.000.
     * Nominal `TOTAL_PAID` = Rp 40.000 (Masuk ke Kas Debit).
     */
    case TOTAL_PAID = 'total_paid';

    /** 
     * Sisa tagihan yang belum dibayar.
     * Kasus: Tagihan Rp 100.000, baru dibayar Rp 40.000.
     * Nominal `TOTAL_RECEIVABLE` = Rp 60.000 (Masuk ke Piutang Debit).
     */
    case TOTAL_RECEIVABLE = 'total_receivable';

    /** 
     * Nilai khusus Jasa/Tindakan Medis.
     * Kasus: Cek tensi dan suntik vitamin seharga Rp 150.000 di modul Pemeriksaan.
     * Nominal `TOTAL_SERVICE` = Rp 150.000 (Masuk ke Pendapatan Jasa Kredit).
     */
    case TOTAL_SERVICE = 'total_service';

    /** 
     * Nilai khusus harga jual Obat/Alkes di dalam modul Pemeriksaan.
     * Kasus: Dokter meresepkan Amoxicillin seharga Rp 25.000.
     * Nominal `TOTAL_NET_PRODUCT` = Rp 25.000 (Masuk ke Pendapatan Obat Kredit).
     */
    case TOTAL_NET_PRODUCT = 'total_net_product';

    /** 
     * Nilai khusus HPP Obat/Alkes di dalam modul Pemeriksaan.
     * Kasus: Resep Amoxicillin terjual, HPP modalnya Rp 11.500.
     * Nominal `TOTAL_COST_PRODUCT` = Rp 11.500 (Masuk HPP Debit & Persediaan Kredit).
     */
    case TOTAL_COST_PRODUCT = 'total_cost_product';

    /** 
     * Nominal tunggal pelunasan spesifik.
     * Kasus: Membayar Hutang/Invoice Supplier senilai Rp 500.000.
     * Nominal `AMOUNT_PAID` = Rp 500.000 (Kas berkurang, Hutang berkurang).
     */
    case AMOUNT_PAID = 'amount_paid';

    /** 
     * Nominal bebas (General).
     * Kasus: Membayar biaya token listrik Rp 300.000.
     * Nominal `AMOUNT` = Rp 300.000 (Kas berkurang, Biaya Listrik bertambah).
     */
    case AMOUNT = 'amount';
}
