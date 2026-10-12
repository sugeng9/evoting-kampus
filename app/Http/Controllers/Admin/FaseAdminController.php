<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportMasterCsvRequest;
use App\Http\Requests\UbahFaseRequest;
use App\Models\PemilihMaster;
use App\Models\SystemPhase;
use App\Models\Voter;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class FaseAdminController extends Controller
{
    /** Panel manajemen fase dan statistik pendaftaran. */
    public function showFasePanel()
    {
        $phase = SystemPhase::current();

        $totalMaster     = PemilihMaster::count();
        $totalPending    = Voter::where('pendaftaran_status', 'pending')->count();
        $totalAktif      = Voter::where('pendaftaran_status', 'aktif')->count();
        $totalDicabut    = Voter::where('pendaftaran_status', 'dicabut')->count();
        $totalTerdaftar  = Voter::whereIn('pendaftaran_status', ['pending', 'aktif', 'dicabut'])->count();
        $belumDaftar     = $totalMaster - $totalTerdaftar;

        return view('admin.fase.panel', [
            'phase' => $phase,
            'currentPhase' => $phase,
            'stats' => [
                'total_master' => $totalMaster,
                'total_aktif' => $totalAktif,
                'total_pending' => $totalPending,
                'total_dicabut' => $totalDicabut,
                'belum_daftar' => $belumDaftar,
            ],
            'totalMaster' => $totalMaster,
            'totalPending' => $totalPending,
            'totalAktif' => $totalAktif,
            'totalDicabut' => $totalDicabut,
            'belumDaftar' => $belumDaftar,
        ]);
    }

    /** Ubah fase sistem. */
    public function ubahFase(UbahFaseRequest $request)
    {
        $phase   = SystemPhase::current();
        $faseLama = $phase->fase;
        $faseBaru = $request->fase;

        // Peringatan: aktif dan voting tidak bisa dibalik
        // (Frontend harus memberi konfirmasi — enforcement di sini hanya informatif)

        $phase->update([
            'fase'                => $faseBaru,
            'pendaftaran_terbuka' => $request->boolean('pendaftaran_terbuka', true),
            'catatan'             => $request->catatan,
            'diubah_oleh'        => auth()->guard('web')->id(),
        ]);

        AuditLogger::log('UBAH_FASE',
            "Fase diubah dari {$faseLama} menjadi {$faseBaru}. Catatan: {$request->catatan}");

        return back()->with('success', "Fase sistem berhasil diubah ke: " . strtoupper($faseBaru));
    }

    /**
     * Aktifkan semua akun pending menjadi aktif.
     * Hanya boleh berjalan di fase 'aktif'.
     */
    public function aktifkanSemuaPending(Request $request)
    {
        $phase = SystemPhase::current();

        if ($phase->fase !== 'aktif') {
            return back()->with('error',
                'Aksi ini hanya dapat dijalankan saat fase sistem adalah AKTIF. Fase saat ini: ' . strtoupper($phase->fase));
        }

        $count = Voter::where('pendaftaran_status', 'pending')
            ->where('is_active', true)
            ->update([
                'pendaftaran_status' => 'aktif',
                'activated_at'       => now(),
            ]);

        AuditLogger::log('AKTIFKAN_SEMUA_PENDING', "$count akun pending berhasil diaktifkan.");

        return back()->with('success', "$count akun pemilih berhasil diaktifkan. Mereka sekarang bisa login dan memilih.");
    }

    /**
     * Import data master pemilih dari CSV.
     * Format CSV: nim,nama,prodi,tanggal_lahir (YYYY-MM-DD)
     */
    public function importMasterCsv(ImportMasterCsvRequest $request)
    {
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (empty($rows)) {
            return back()->with('error', 'File CSV kosong.');
        }

        // Ambil header
        $header = array_map(function($col) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$col)));
        }, array_shift($rows));

        // Petakan kolom
        $colNim = null; $colNama = null; $colProdi = null; $colTgl = null; $colAngkatan = null; $colStatus = null;
        foreach ($header as $idx => $name) {
            if (in_array($name, ['nim', 'npm', 'student_id'])) $colNim = $idx;
            elseif (in_array($name, ['nama', 'name', 'nama_lengkap', 'nama_mahasiswa'])) $colNama = $idx;
            elseif (in_array($name, ['prodi', 'program_studi', 'jurusan', 'study_program'])) $colProdi = $idx;
            elseif (in_array($name, ['tanggal_lahir', 'tgl_lahir', 'dob', 'birthdate'])) $colTgl = $idx;
            elseif (in_array($name, ['angkatan', 'tahun_masuk', 'batch'])) $colAngkatan = $idx;
            elseif (in_array($name, ['status', 'status_akademik', 'academic_status'])) $colStatus = $idx;
        }

        if ($colNim === null || $colNama === null || $colTgl === null) {
            return back()->with('error', "Header CSV tidak lengkap. Wajib memiliki kolom 'nim', 'nama', dan 'tanggal_lahir'.");
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            if (count($row) < 3 || empty($row[$colNim])) {
                $skipped++;
                continue;
            }

            $nim = trim($row[$colNim]);
            $nama = trim($row[$colNama]);
            $prodi = $colProdi !== null && isset($row[$colProdi]) ? trim($row[$colProdi]) : 'Umum';
            $angkatan = $colAngkatan !== null && isset($row[$colAngkatan]) ? trim($row[$colAngkatan]) : (strlen($nim) >= 4 ? '20' . substr($nim, 2, 2) : null);

            // Saring jika ada kolom status akademik
            if ($colStatus !== null && isset($row[$colStatus])) {
                $status = strtolower(trim($row[$colStatus]));
                if (str_contains($status, 'cuti') || str_contains($status, 'do') || str_contains($status, 'wisuda') || str_contains($status, 'masalah')) {
                    $skipped++; // Dicoret karena TMS
                    continue;
                }
            }

            // Validasi tanggal lahir fleksibel (YYYY-MM-DD, DD/MM/YYYY, dll)
            $formattedTgl = null;
            try {
                $formattedTgl = \Carbon\Carbon::parse(trim($row[$colTgl]))->format('Y-m-d');
            } catch (\Throwable) {
                $skipped++;
                continue;
            }

            $exists = \App\Models\PemilihMaster::where('nim', $nim)->exists();

            PemilihMaster::updateOrCreate(
                ['nim' => $nim],
                [
                    'nama'               => $nama,
                    'prodi'              => $prodi,
                    'angkatan'           => $angkatan,
                    'tanggal_lahir_hash' => hash('sha256', $formattedTgl),
                ]
            );

            $exists ? $updated++ : $inserted++;
        }

        AuditLogger::log('IMPORT_CSV_MASTER',
            "Import master: $inserted baru, $updated diperbarui, $skipped dilewati.");

        return back()->with('success',
            "Import berhasil — Ditambahkan: $inserted | Diperbarui: $updated | Dilewati: $skipped.");
    }
}
