<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Voter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportDptCommand extends Command
{
    protected $signature = 'dpt:import 
                            {file : Lokasi path file CSV mahasiswa} 
                            {--generate-pin : Buat PIN 6-digit otomatis jika kolom PIN kosong}';

    protected $description = 'Import batch data pemilih mahasiswa (DPT) dari file CSV';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Membaca data mahasiswa dari: {$filePath}");

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file CSV.");
            return Command::FAILURE;
        }

        // Baca header baris pertama
        $header = fgetcsv($handle, 1000, ',');
        if (!$header || count($header) < 2) {
            // Coba dengan pemisah titik koma ';'
            rewind($handle);
            $header = fgetcsv($handle, 1000, ';');
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        // Normalisasi nama kolom header
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleaned = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $colName)));
            if (in_array($cleaned, ['nim', 'npm', 'student_id'])) {
                $headerMap['nim'] = $index;
            } elseif (in_array($cleaned, ['nama', 'name', 'nama_lengkap', 'student_name'])) {
                $headerMap['name'] = $index;
            } elseif (in_array($cleaned, ['fakultas', 'faculty'])) {
                $headerMap['faculty'] = $index;
            } elseif (in_array($cleaned, ['prodi', 'program_studi', 'jurusan', 'major'])) {
                $headerMap['study_program'] = $index;
            } elseif (in_array($cleaned, ['pin', 'password', 'token'])) {
                $headerMap['pin'] = $index;
            }
        }

        if (!isset($headerMap['nim']) || !isset($headerMap['name'])) {
            $this->error("Format header tidak valid. File harus memiliki kolom 'nim' dan 'nama'.");
            return Command::FAILURE;
        }

        $shouldGeneratePin = $this->option('generate-pin');
        $successCount = 0;
        $skipCount = 0;
        $generatedTokens = [];

        $this->output->progressStart();

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
                if (empty($row) || !isset($row[$headerMap['nim']])) {
                    continue;
                }

                $nim = trim($row[$headerMap['nim']]);
                if (empty($nim)) {
                    continue;
                }

                // Cek duplikasi
                if (Voter::where('nim', $nim)->exists()) {
                    $skipCount++;
                    continue;
                }

                $name = trim($row[$headerMap['name']] ?? 'Mahasiswa');
                $faculty = isset($headerMap['faculty']) ? trim($row[$headerMap['faculty']] ?? 'Umum') : 'Umum';
                $studyProgram = isset($headerMap['study_program']) ? trim($row[$headerMap['study_program']] ?? 'Umum') : 'Umum';

                // Tentukan PIN
                $plainPin = null;
                if (isset($headerMap['pin']) && !empty($row[$headerMap['pin']])) {
                    $plainPin = trim($row[$headerMap['pin']]);
                } elseif ($shouldGeneratePin) {
                    $plainPin = (string) mt_rand(100000, 999999);
                } else {
                    $plainPin = '123456'; // Default PIN
                }

                Voter::create([
                    'nim' => $nim,
                    'name' => $name,
                    'faculty' => $faculty ?: 'Umum',
                    'study_program' => $studyProgram ?: 'Umum',
                    'password' => Hash::make($plainPin),
                    'is_active' => true,
                ]);

                $generatedTokens[] = [$nim, $name, $faculty, $studyProgram, $plainPin];
                $successCount++;
                $this->output->progressAdvance();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi error saat import: " . $e->getMessage());
            return Command::FAILURE;
        } finally {
            fclose($handle);
        }

        $this->output->progressFinish();

        $this->info("Import selesai!");
        $this->info("Berhasil diimpor: {$successCount} mahasiswa.");
        if ($skipCount > 0) {
            $this->warn("Dilewati (NIM sudah ada): {$skipCount} mahasiswa.");
        }

        // Simpan rekap distribusi PIN jika ada
        if (!empty($generatedTokens)) {
            $exportPath = storage_path('app/distribusi_token_pemira_' . date('Ymd_His') . '.csv');
            $exportHandle = fopen($exportPath, 'w');
            fputcsv($exportHandle, ['NIM', 'Nama Mahasiswa', 'Fakultas', 'Program Studi', 'PIN Verifikasi']);
            foreach ($generatedTokens as $t) {
                fputcsv($exportHandle, $t);
            }
            fclose($exportHandle);

            $this->info("Rekap distribusi PIN mahasiswa disimpan di: {$exportPath}");
        }

        AuditLog::log('DPT_BATCH_IMPORTED_CLI', 'admin', null, [
            'imported' => $successCount,
            'skipped' => $skipCount,
        ]);

        return Command::SUCCESS;
    }
}
