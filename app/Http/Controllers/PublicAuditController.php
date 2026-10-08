<?php

namespace App\Http\Controllers;

use App\Models\BallotBox;
use Illuminate\Http\Request;

class PublicAuditController extends Controller
{
    /**
     * Halaman Verifikasi Suara Independen (Public Ballot Verification)
     */
    public function showVerificationPage()
    {
        return view('public.verify_ballot');
    }

    /**
     * Cek keberadaan suara di Kotak Suara Digital tanpa membocorkan isi pilihan
     */
    public function verifyBallot(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'string', 'max:50'],
        ]);

        $code = trim($request->input('verification_code'));

        $ballot = BallotBox::with('category')
            ->where('verification_code', $code)
            ->first();

        if (!$ballot) {
            return back()->with('audit_result', [
                'status' => 'not_found',
                'message' => 'Kode verifikasi tidak ditemukan di dalam kotak suara digital. Pastikan Anda memasukkan kode dengan benar.',
            ])->withInput();
        }

        return back()->with('audit_result', [
            'status' => 'verified',
            'category_name' => $ballot->category->name,
            'ballot_hash' => $ballot->ballot_hash,
            'recorded_at' => $ballot->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB',
            'message' => 'VALID & TERVERIFIKASI: Suara Anda tercatat secara sah dan aman di dalam kotak suara digital terenkripsi.',
        ])->withInput();
    }
}
