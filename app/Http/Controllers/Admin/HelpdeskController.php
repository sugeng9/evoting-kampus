<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TolakSengketaRequest;
use App\Models\KodePendaftaran;
use App\Models\Sengketa;
use App\Models\SystemPhase;
use App\Models\Voter;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HelpdeskController extends Controller
{
    /** Daftar semua laporan sengketa dengan filter. */
    public function index(Request $request)
    {
        $query = Sengketa::with(['petugasA', 'petugasB'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $search = $request->input('nim') ?? $request->input('search');
        if (!empty($search)) {
            $escaped = addcslashes(trim($search), '%_\\');
            $query->where('nim', 'like', '%' . $escaped . '%');
        }

        $sengketas = $query->paginate(20)->withQueryString();

        return view('helpdesk.index', compact('sengketas'));
    }

    /** Detail laporan sengketa. */
    public function show(Sengketa $sengketa)
    {
        $voter = Voter::where('nim', $sengketa->nim)->first();
        $phase = SystemPhase::current();

        // Mask email voter
        $maskedEmail = null;
        if ($voter && $voter->email) {
            $maskedEmail = preg_replace('/(.{1}).+(@.+)/', '$1***$2', $voter->email);
        }

        return view('helpdesk.show', compact('sengketa', 'voter', 'phase', 'maskedEmail'));
    }

    /** Petugas A: tandai sengketa sebagai sedang diproses. */
    public function proses(Request $request, Sengketa $sengketa)
    {
        if ($sengketa->status !== 'baru') {
            return back()->with('error', 'Sengketa sudah dalam status: ' . $sengketa->status);
        }

        $sengketa->update([
            'status'       => 'diproses',
            'ditangani_oleh' => auth()->guard('web')->id(),
        ]);

        AuditLogger::log('SENGKETA_DIPROSES', "Sengketa #{$sengketa->id} NIM {$sengketa->nim} mulai diproses.");

        return back()->with('success', 'Laporan ditandai sedang diproses oleh Anda.');
    }

    /** Petugas B: setujui pencabutan akun (2-petugas approval). */
    public function setujuiCabut(Request $request, Sengketa $sengketa)
    {
        $userId = auth()->guard('web')->id();

        if ($sengketa->ditangani_oleh === $userId) {
            return back()->with('error', 'Anda tidak dapat menyetujui pencabutan yang Anda sendiri ajukan. Diperlukan petugas berbeda.');
        }

        if ($sengketa->disetujui_oleh) {
            return back()->with('error', 'Pencabutan sudah disetujui sebelumnya.');
        }

        $sengketa->update(['disetujui_oleh' => $userId]);

        AuditLogger::log('PENCABUTAN_DISETUJUI', "Petugas #{$userId} menyetujui pencabutan akun NIM {$sengketa->nim}.");

        return back()->with('success', 'Pencabutan akun disetujui. Petugas pertama dapat sekarang menjalankan pencabutan.');
    }

    /** Jalankan pencabutan akun (butuh: KTM verified + persetujuan 2 petugas + fase tidak frozen). */
    public function cabutAkun(Request $request, Sengketa $sengketa)
    {
        $phase = SystemPhase::current();
        if ($phase->isFrozen()) {
            return back()->with('error', 'Pencabutan akun TIDAK diizinkan pada fase ' . strtoupper($phase->fase) . '. Kepemilikan akun sudah dikunci.');
        }

        if (! $request->boolean('ktm_diverifikasi')) {
            return back()->with('error', 'Wajib mencentang konfirmasi "KTM asli sudah diverifikasi" sebelum mencabut akun.');
        }

        if (! $sengketa->disetujui_oleh) {
            return back()->with('error', 'Pencabutan belum mendapat persetujuan dari petugas kedua. Minta petugas lain untuk menyetujui terlebih dahulu.');
        }

        $catatan = $request->input('catatan', 'Akun dicabut berdasarkan verifikasi KTM oleh helpdesk.');

        $voter = Voter::where('nim', $sengketa->nim)->first();
        if ($voter) {
            $voter->update(['pendaftaran_status' => 'dicabut']);
            // TODO: Mail::to($voter->email)->send(new AkunDicabutMail($voter)); saat production
        }

        $sengketa->update([
            'status'           => 'selesai',
            'ktm_diverifikasi' => true,
            'catatan_petugas'  => $catatan,
            'diselesaikan_at'  => now(),
        ]);

        AuditLogger::log('AKUN_DICABUT', "Akun NIM {$sengketa->nim} dicabut — alasan: $catatan");

        return back()->with('success', 'Akun berhasil dicabut. Pemilih dapat mendaftar ulang menggunakan kode dari helpdesk.');
    }

    /** Tolak laporan sengketa dengan catatan. */
    public function tolakSengketa(TolakSengketaRequest $request, Sengketa $sengketa)
    {
        $sengketa->update([
            'status'          => 'selesai',
            'catatan_petugas' => 'DITOLAK: ' . $request->catatan_tolak,
            'diselesaikan_at' => now(),
        ]);

        AuditLogger::log('SENGKETA_DITOLAK', "Sengketa #{$sengketa->id} NIM {$sengketa->nim} ditolak: {$request->catatan_tolak}");

        return back()->with('success', 'Laporan sengketa ditolak.');
    }

    /**
     * Terbitkan kode pendaftaran sekali-pakai untuk pemilik asli NIM.
     * Kode ditampilkan SEKALI di flash session — tidak tersimpan dalam plaintext.
     */
    public function terbitkanKodePendaftaran(Request $request, Sengketa $sengketa)
    {
        $phase = SystemPhase::current();
        if ($phase->isFrozen()) {
            return back()->with('error', 'Penerbitan kode pendaftaran TIDAK diizinkan pada fase ' . strtoupper($phase->fase) . '.');
        }

        // Pastikan akun lama sudah dicabut sebelum menerbitkan kode daftar ulang
        $voter = Voter::where('nim', $sengketa->nim)->first();
        if ($voter && $voter->pendaftaran_status !== 'dicabut') {
            return back()->with('error', 'Kode pendaftaran hanya bisa diterbitkan setelah akun lama dicabut terlebih dahulu.');
        }

        // Nonaktifkan kode lama yang belum terpakai
        KodePendaftaran::where('nim', $sengketa->nim)->whereNull('used_at')->update(['expired_at' => now()]);

        // Buat kode baru: 32 karakter random, simpan sebagai hash
        $plainKode = Str::random(32);
        $userId    = auth()->guard('web')->id();

        KodePendaftaran::create([
            'nim'        => $sengketa->nim,
            'kode_hash'  => hash('sha256', $plainKode),
            'dibuat_oleh' => $userId,
            'expired_at' => now()->addHours(48),
        ]);

        AuditLogger::log('KODE_PENDAFTARAN_DITERBITKAN',
            "Kode pendaftaran ulang diterbitkan untuk NIM {$sengketa->nim} oleh helpdesk #{$userId}.");

        // Kode tampil SEKALI via flash — tidak pernah disimpan dalam plaintext di DB
        return back()
            ->with('kode_plain', $plainKode)
            ->with('success', 'Kode berhasil diterbitkan. Catat dan berikan kepada pemilik asli NIM. Kode ini TIDAK bisa ditampilkan lagi.');
    }

    /**
     * Download / pratinjau bukti KTM privat dari storage aman.
     * Hanya dapat diakses oleh petugas helpdesk/admin yang terotentikasi & lolos 2FA.
     */
    public function downloadKtm(Sengketa $sengketa)
    {
        if (empty($sengketa->bukti_ktm_path) || !Storage::disk('local')->exists($sengketa->bukti_ktm_path)) {
            abort(404, 'Lampiran bukti KTM tidak ditemukan pada media penyimpanan aman.');
        }

        $fullPath = Storage::disk('local')->path($sengketa->bukti_ktm_path);
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);

        AuditLogger::log(
            'KTM_ACCESSED',
            "Petugas #" . auth()->guard('web')->id() . " melihat lampiran bukti KTM sengketa #{$sengketa->id} (NIM: {$sengketa->nim})."
        );

        $response = response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="KTM-' . $sengketa->nim . '.' . $ext . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}

