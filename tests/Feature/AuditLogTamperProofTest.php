<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogTamperProofTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Uji 1: Verifikasi Rantai Hash Berantai (Blockchain-style SHA-256 Chaining)
     * Rekaman log N harus menyimpan hash_ini dari rekaman log N-1.
     */
    public function test_audit_logs_are_sequentially_chained(): void
    {
        $log1 = AuditLog::record('ADMIN_LOGIN_SUCCESS', 'admin:1', ['ip' => '127.0.0.1'], 'admin:1 (Super Admin)');
        $log2 = AuditLog::record('VOTER_STATUS_CHANGED', 'voter:120140001', ['is_active' => false], 'admin:1 (Super Admin)');
        $log3 = AuditLog::record('DATA_EXPORTED', 'export:dpt_csv', ['filename' => 'dpt.csv'], 'admin:1 (Super Admin)');

        // Log 2 harus merujuk ke hash_ini dari Log 1
        $this->assertEquals($log1->hash_ini, $log2->hash_sebelumnya, 'Hash sebelumnya Log 2 harus identik dengan hash ini Log 1.');

        // Log 3 harus merujuk ke hash_ini dari Log 2
        $this->assertEquals($log2->hash_ini, $log3->hash_sebelumnya, 'Hash sebelumnya Log 3 harus identik dengan hash ini Log 2.');

        // Verifikasi keaslian hash terhitung Log 3
        $expectedHash3 = AuditLog::calculateHash(
            $log3->aktor,
            $log3->aksi,
            $log3->target,
            $log3->ip_address,
            $log3->created_at,
            $log3->payload,
            $log3->hash_sebelumnya
        );

        $this->assertEquals($expectedHash3, $log3->hash_ini, 'Hash ini Log 3 harus sesuai dengan perhitungan SHA-256 data.');
    }

    /**
     * Uji 2: Proteksi Immutability (Append-Only)
     * Model AuditLog dilarang keras diubah (update) atau dihapus (delete) lewat aplikasi.
     */
    public function test_audit_log_model_prohibits_update_and_delete(): void
    {
        $log = AuditLog::record('ELECTION_SESSION_OPENED', 'election_setting:1', ['status' => 'OPEN']);

        // Percobaan UPDATE lewat model harus memicu RuntimeException
        try {
            $log->update(['aksi' => 'HACKED_ACTION']);
            $this->fail('Seharusnya melempar RuntimeException saat mencoba mengupdate AuditLog.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Percobaan DELETE lewat model harus memicu RuntimeException
        try {
            $log->delete();
            $this->fail('Seharusnya melempar RuntimeException saat mencoba menghapus AuditLog.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Verifikasi di database: data tetap utuh
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'aksi' => 'ELECTION_SESSION_OPENED',
        ]);
    }

    /**
     * Uji 3: Command audit:verifikasi Berhasil pada Rantai yang Valid
     */
    public function test_verify_audit_chain_command_passes_on_valid_chain(): void
    {
        AuditLog::record('TEST_EVENT_A', 'target:1', ['data' => 1]);
        AuditLog::record('TEST_EVENT_B', 'target:2', ['data' => 2]);

        $exitCode = Artisan::call('audit:verifikasi');
        $this->assertEquals(0, $exitCode, 'Command audit:verifikasi harus mengembalikan exit code 0 saat rantai valid.');

        $output = Artisan::output();
        $this->assertStringContainsString('100% RANTAI LOG AUDIT UTUH', $output);
    }

    /**
     * Uji 4: Deteksi Manipulasi Data (Tampering Detection)
     * Jika penyerang mengubah data mentah langsung di MySQL (bypass PHP),
     * command audit:verifikasi WAJIB mendeteksinya dan gagal.
     */
    public function test_verify_audit_chain_command_detects_data_tampering(): void
    {
        $log1 = AuditLog::record('NORMAL_EVENT_1', 'target:1', ['amount' => 10]);
        $log2 = AuditLog::record('NORMAL_EVENT_2', 'target:2', ['amount' => 20]);

        // Simulasikan serangan injeksi MySQL langsung yang mengubah isi aksi log
        DB::table('audit_logs')->where('id', $log1->id)->update([
            'aksi' => 'MALICIOUS_TAMPERED_EVENT',
        ]);

        $exitCode = Artisan::call('audit:verifikasi');
        $this->assertEquals(1, $exitCode, 'Command audit:verifikasi harus gagal (exit code 1) saat ada data yang termanipulasi.');

        $output = Artisan::output();
        $this->assertStringContainsString('DATA TERMANIPULASI', $output);
        $this->assertStringContainsString("LOG #{$log1->id}", $output);
    }

    /**
     * Uji 5: Deteksi Penghapusan Baris (Broken Chain Link Detection)
     * Jika penyerang menghapus salah satu baris log di tengah-tengah rantai,
     * hash_sebelumnya dari baris berikutnya akan putus.
     */
    public function test_verify_audit_chain_command_detects_row_deletion(): void
    {
        $logA = AuditLog::record('CHAIN_EVENT_A', 'target:A');
        $logB = AuditLog::record('CHAIN_EVENT_B', 'target:B');
        $logC = AuditLog::record('CHAIN_EVENT_C', 'target:C');

        // Simulasikan penyerang menghapus baris B langsung di database MySQL
        DB::table('audit_logs')->where('id', $logB->id)->delete();

        $exitCode = Artisan::call('audit:verifikasi');
        $this->assertEquals(1, $exitCode, 'Command audit:verifikasi harus gagal saat ada baris yang hilang di tengah rantai.');

        $output = Artisan::output();
        $this->assertStringContainsString('RANTAI PUTUS', $output);
        $this->assertStringContainsString("LOG #{$logC->id}", $output);
    }
}
