<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\NimStatusRequest;
use App\Http\Requests\SengketaRequest;
use App\Models\PemilihMaster;
use App\Models\Sengketa;
use App\Models\SystemPhase;
use App\Models\Voter;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class NimStatusController extends Controller
{
    /** Tampilkan form cek status NIM. */
    public function showForm()
    {
        return view('auth.nim_status.form');
    }

    /**
     * Cek status NIM.
     * Rate limit: throttle:10,5 di route.
     */
    public function checkStatus(NimStatusRequest $request)
    {
        $phase = SystemPhase::current();

        // Verifikasi NIM + tanggal lahir (pesan generik untuk mencegah enumerasi)
        $master = PemilihMaster::findByNim($request->nim);

        if (! $master) {
            // Anti-Timing Attack: Hashing dummy agar waktu eksekusi identik saat NIM tidak ada
            hash('sha256', trim($request->tanggal_lahir));
            try {
                \Carbon\Carbon::parse(trim($request->tanggal_lahir))->format('Y-m-d');
                hash('sha256', 'dummy_salt_timing_status');
            } catch (\Throwable $e) {}
        }

        if (! $master || ! $master->verifyTanggalLahir(trim($request->tanggal_lahir))) {
            AuditLogger::log('CEK_STATUS_GAGAL', "NIM {$request->nim} — data tidak cocok.", null, 'system');
            return back()->withInput()->with('error', 'Data tidak ditemukan atau tidak cocok.');
        }

        $voter = Voter::where('nim', $request->nim)->first();

        // Tentukan status
        $status = 'belum_terdaftar';
        if ($voter) {
            $status = $voter->pendaftaran_status; // pending / aktif / dicabut
        }

        // Mask email
        $maskedEmail = null;
        if ($voter && $voter->email) {
            $maskedEmail = preg_replace('/(.{1}).+(@.+)/', '$1***$2', $voter->email);
        }

        // Ambil riwayat sengketa terakhir jika pernah ada laporan
        $sengketa = Sengketa::where('nim', $request->nim)->latest()->first();

        AuditLogger::log('CEK_STATUS_NIM', "NIM {$request->nim} dicek — status: $status.", null, 'system');

        return view('auth.nim_status.result', [
            'nim'               => $request->nim,
            'status'            => $status,
            'voter'             => $voter,
            'maskedEmail'       => $maskedEmail,
            'sengketa'          => $sengketa,
            'faseAllowsSengketa' => $phase->allowsSengketa(),
            'fase'              => $phase->fase,
        ]);
    }

    /** Tampilkan form laporan sengketa. */
    public function showSengketaForm(Request $request)
    {
        $phase = SystemPhase::current();
        $nim = $request->query('nim', '');

        return view('auth.nim_status.sengketa', [
            'nim'  => $nim,
            'fase' => $phase->fase,
        ]);
    }

    /**
     * Submit laporan sengketa.
     * Rate limit: throttle:3,10 di route.
     */
    public function submitSengketa(SengketaRequest $request)
    {
        $phase = SystemPhase::current();

        // Verifikasi NIM + tanggal lahir dulu (mencegah laporan asal)
        $master = PemilihMaster::findByNim($request->nim);

        if (! $master) {
            // Anti-Timing Attack: Hashing dummy agar waktu eksekusi identik saat NIM tidak ada
            hash('sha256', trim($request->tanggal_lahir));
            try {
                \Carbon\Carbon::parse(trim($request->tanggal_lahir))->format('Y-m-d');
                hash('sha256', 'dummy_salt_timing_status');
            } catch (\Throwable $e) {}
        }

        if (! $master || ! $master->verifyTanggalLahir(trim($request->tanggal_lahir))) {
            return back()->withInput()->with('error', 'Data tidak ditemukan atau tidak cocok.');
        }

        // Jika fase voting: catat sebagai insiden saja, tidak ada perubahan akun
        if ($phase->fase === 'voting') {
            AuditLogger::log('INSIDEN_SENGKETA_VOTING',
                "Laporan insiden dari {$request->pelapor_kontak} untuk NIM {$request->nim}: {$request->alasan}",
                null, 'system');

            return back()->with('info',
                'Laporan Anda telah diterima dan dicatat sebagai insiden. ' .
                'Pada fase ini tidak ada perubahan kepemilikan akun yang dapat dilakukan. ' .
                'Panitia KPU-M akan menindaklanjuti setelah pemungutan suara selesai.');
        }

        // Fase pendaftaran atau sengketa: buat laporan sengketa resmi
        if (! $phase->allowsSengketa()) {
            return back()->with('error', 'Pelaporan sengketa tidak tersedia di fase saat ini.');
        }

        // Simpan lampiran berkas bukti KTM ke direktori privat (di luar webroot public)
        $ktmPath = null;
        if ($request->hasFile('bukti_ktm')) {
            $file = $request->file('bukti_ktm');
            $extension = strtolower($file->getClientOriginalExtension());
            $safeFilename = \Illuminate\Support\Str::uuid()->toString() . '.' . $extension;
            $ktmPath = $file->storeAs('private_ktm', $safeFilename, 'local');
        }

        Sengketa::create([
            'nim'              => $request->nim,
            'alasan'           => $request->alasan,
            'pelapor_kontak'   => $request->pelapor_kontak,
            'bukti_ktm_path'   => $ktmPath,
            'ktm_diverifikasi' => $ktmPath !== null,
            'status'           => 'baru',
        ]);

        AuditLogger::log('LAPORAN_SENGKETA',
            "Laporan sengketa NIM {$request->nim} dari {$request->pelapor_kontak}" . ($ktmPath ? " (Lampiran KTM diunggah)." : "."),
            null, 'system');

        return back()->with('success',
            'Laporan sengketa berhasil dikirim. Tim helpdesk KPU-M akan memeriksa dokumen dan menghubungi Anda dalam 1x24 jam.');
    }
}
