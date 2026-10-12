<?php

namespace App\Console\Commands;

use App\Models\Sengketa;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeResolvedSengketaEvidence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sengketa:purge-ktm 
                            {--days=30 : Batas usia hari sengketa berstatus selesai/ditolak yang lampirannya dihapus}
                            {--force : Lewati konfirmasi interaktif (cocok untuk cron job/scheduler)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan berkas bukti KTM sengketa yang telah selesai/ditolak sesuai kepatuhan retensi data UU PDP';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $force = (bool) $this->option('force');

        $cutoffDate = now()->subDays($days);

        $sengketas = Sengketa::whereIn('status', ['selesai', 'ditolak'])
            ->whereNotNull('bukti_ktm_path')
            ->where(function ($query) use ($cutoffDate) {
                $query->where('diselesaikan_at', '<=', $cutoffDate)
                    ->orWhere(function ($q) use ($cutoffDate) {
                        $q->whereNull('diselesaikan_at')
                          ->where('updated_at', '<=', $cutoffDate);
                    });
            })
            ->get();

        $totalCount = $sengketas->count();

        $this->info("==================================================================");
        $this->info("🛡️  PEMBERSIHAN BERKAS IDENTITAS KTM SENGKETA (UU PDP RETENTION)");
        $this->info("==================================================================");
        $this->line("Batas Waktu Retensi : Berstatus selesai/ditolak sebelum " . $cutoffDate->toDateTimeString() . " ({$days} hari)");
        $this->line("Ditemukan Berkas    : {$totalCount} lampiran KTM siap dibersihkan");

        if ($totalCount === 0) {
            $this->info("✅ Tidak ada berkas KTM kedaluwarsa yang perlu dibersihkan saat ini.");
            return Command::SUCCESS;
        }

        if (!$force && !$this->confirm("Apakah Anda yakin ingin menghapus {$totalCount} berkas KTM secara permanen dari server?", true)) {
            $this->warn("Operasi pembersihan dibatalkan oleh pengguna.");
            return Command::INVALID;
        }

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($sengketas as $sengketa) {
            $path = $sengketa->bukti_ktm_path;

            try {
                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                }

                $sengketa->update(['bukti_ktm_path' => null]);
                $deletedCount++;

                AuditLogger::log(
                    'KTM_PURGED',
                    "Berkas KTM sengketa #{$sengketa->id} (NIM: {$sengketa->nim}) dibersihkan secara otomatis sesuai batas retensi ({$days} hari)."
                );
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("Gagal menghapus berkas sengketa #{$sengketa->id}: {$e->getMessage()}");
            }
        }

        $this->info("------------------------------------------------------------------");
        $this->info("✅ Berhasil membersihkan {$deletedCount} berkas KTM.");
        if ($failedCount > 0) {
            $this->warn("⚠️ {$failedCount} berkas gagal dibersihkan.");
        }

        return Command::SUCCESS;
    }
}
