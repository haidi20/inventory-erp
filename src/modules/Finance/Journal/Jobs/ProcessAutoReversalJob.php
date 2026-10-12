<?php

namespace App\Modules\Finance\Journal\Jobs;

use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Services\JournalService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk memproses pembatalan/pembalikan (reversal) jurnal secara otomatis
 * berdasarkan tanggal pembalikan yang telah ditentukan.
 */
class ProcessAutoReversalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Membuat instance job baru.
     */
    public function __construct()
    {
        //
    }

    /**
     * Menjalankan logika utama pemrosesan job.
     * Mengambil semua jurnal dengan status 'pending_reversal' yang sudah jatuh tempo.
     * 
     * @param JournalService $journalService Dependency injection untuk layanan jurnal.
     */
    public function handle(JournalService $journalService): void
    {
        Log::info("Menjalankan ProcessAutoReversalJob untuk memproses reversal jurnal...");

        // 1. Ambil jurnal yang memiliki status pending reversal dan 
        //    tanggal reversal-nya sudah lewat atau hari ini.
        $journals = Journal::where('reversal_status', 'pending_reversal')
            ->where('reversal_date', '<=', now()->endOfDay()->timestamp)
            ->get();

        // 2. Iterasi setiap jurnal dan lakukan proses reversal melalui JournalService.
        foreach ($journals as $journal) {
            try {
                Log::info("Memproses Reversal untuk Journal UUID: {$journal->uuid} - Tanggal: {$journal->reversal_date}");
                
                // Memanggil service untuk membuat jurnal pembalik (reversing entry).
                // Prefix 'REVERSAL:' ditambahkan pada deskripsi jurnal baru.
                $journalService->reverseJournal($journal->uuid, [
                    'reversal_date' => $journal->reversal_date,
                    'description_prefix' => 'REVERSAL:',
                ]);

            } catch (Exception $e) {
                // Jika satu jurnal gagal, log error-nya dan lanjutkan ke jurnal berikutnya agar tidak menghentikan keseluruhan job.
                Log::error("Gagal melakukan auto-reverse pada Journal ID {$journal->id}: " . $e->getMessage());
            }
        }
    }
}
