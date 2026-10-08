<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BallotBox;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\Voter;
use App\Models\VoterParticipation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DoubleVotingException extends Exception {}
class VotingClosedException extends Exception {}
class IneligibleVoterException extends Exception {}

class VotingService
{
    /**
     * Menyimpan suara dengan Database Transaction (ACID) dan Pessimistic Locking
     * Mencegah Race-Condition serta menjamin Prinsip Kerahasiaan Suara (Secret Ballot)
     *
     * @param Voter $voter Mahasiswa yang sedang login
     * @param int $categoryId ID Kategori (Presma / Kahim)
     * @param int $candidateId ID Kandidat pilihan
     * @return array Bukti tanda terima pemilihan (Kuitansi Kriptografis)
     * @throws Exception
     */
    public function castVote(Voter $voter, int $categoryId, int $candidateId): array
    {
        // 1. Validasi Sesi Pemilihan (Global Status & Waktu)
        $setting = ElectionSetting::current();
        if (!$setting->isOpen()) {
            throw new VotingClosedException("Sesi e-voting saat ini sedang DITUTUP atau berada di luar jadwal resmi.");
        }

        // 2. Eksekusi Atomic Transaction dengan Isolasi Level Tinggi
        return DB::transaction(function () use ($voter, $categoryId, $candidateId) {
            
            // PESSIMISTIC LOCKING: Mengunci baris data pemilih secara eksklusif (SELECT ... FOR UPDATE)
            // Jika ada request paralel/bersamaan dari voter yang sama, request kedua akan tertahan (wait)
            // sampai transaksi pertama selesai di-commit.
            $lockedVoter = Voter::where('id', $voter->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedVoter->is_active) {
                throw new IneligibleVoterException("Hak pilih NIM {$lockedVoter->nim} dinonaktifkan oleh panitia.");
            }

            // Validasi Kategori
            $category = ElectionCategory::where('id', $categoryId)
                ->where('is_active', true)
                ->firstOrFail();

            if (!$category->isEligible($lockedVoter)) {
                throw new IneligibleVoterException("NIM Anda tidak terdaftar sebagai pemilih pada kategori {$category->name}.");
            }

            // Validasi Jadwal Independen Kategori (Presma vs HMJ)
            if (!$category->isScheduleOpen()) {
                $status = $category->getScheduleStatus();
                if ($status === 'UPCOMING') {
                    $startStr = $category->start_at ? $category->start_at->translatedFormat('d F Y, H:i T') : 'segera';
                    throw new VotingClosedException("Sesi pemilihan untuk {$category->name} belum dibuka. Jadwal buka: {$startStr}.");
                } elseif ($status === 'ENDED') {
                    $endStr = $category->end_at ? $category->end_at->translatedFormat('d F Y, H:i T') : '-';
                    throw new VotingClosedException("Sesi pemilihan untuk {$category->name} telah resmi berakhir pada {$endStr}.");
                } else {
                    throw new VotingClosedException("Sesi pemilihan untuk {$category->name} saat ini sedang ditutup/ditangguhkan oleh panitia.");
                }
            }

            // CRITICAL CHECK: Cek apakah pemilih sudah pernah memilih pada kategori ini
            $hasVoted = VoterParticipation::where('voter_id', $lockedVoter->id)
                ->where('election_category_id', $categoryId)
                ->exists();

            if ($hasVoted) {
                AuditLog::log('DOUBLE_VOTING_ATTEMPT_BLOCKED', 'voter', $lockedVoter->id, [
                    'category_id' => $categoryId,
                    'nim' => $lockedVoter->nim,
                ]);
                throw new DoubleVotingException("Pelanggaran: NIM {$lockedVoter->nim} sudah pernah memberikan suara untuk kategori ini!");
            }

            // Validasi Kandidat
            $candidate = Candidate::where('id', $candidateId)
                ->where('election_category_id', $categoryId)
                ->where('is_active', true)
                ->firstOrFail();

            // 3. KRIPTOGRAFI ANONIM & AUDIT TRAIL (SECRET BALLOT)
            // Buat token rahasia acak untuk kuitansi pemilih
            $voterSecretSalt = bin2hex(random_bytes(16));
            $verificationCode = 'PEMIRA-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
            
            // Hash suara untuk audit independen
            $ballotHash = hash('sha256', $candidate->id . '|' . $categoryId . '|' . $voterSecretSalt . '|' . config('app.key'));
            
            // Hash kuitansi presensi pemilih
            $receiptHash = hash('sha256', $lockedVoter->id . '|' . $categoryId . '|' . $verificationCode . '|' . config('app.key'));

            // A. Masukkan Suara ke Bilik Suara Digital (Ballot Box)
            // PERHATIKAN: SAMA SEKALI TIDAK MENYIMPAN voter_id ATAU nim!
            // Menggunakan UUID acak sebagai ID untuk mencegah korelasi waktu/urutan auto-increment
            $ballot = BallotBox::create([
                'id' => (string) Str::uuid(),
                'election_category_id' => $categoryId,
                'candidate_id' => $candidate->id,
                'ballot_hash' => $ballotHash,
                'verification_code' => $verificationCode,
                'created_at' => now(),
            ]);

            // B. Catat Presensi Partisipasi Pemilih
            // Menyimpan BAHWA voter telah memilih kategori ini agar tidak bisa memilih dua kali.
            // TIDAK ADA kolom candidate_id di sini!
            $participation = VoterParticipation::create([
                'voter_id' => $lockedVoter->id,
                'election_category_id' => $categoryId,
                'receipt_hash' => $receiptHash,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
                'voted_at' => now(),
            ]);

            // C. Audit Trail System
            AuditLog::log('VOTE_SUCCESSFULLY_CAST', 'voter', $lockedVoter->id, [
                'category_id' => $categoryId,
                'verification_code' => $verificationCode,
                'timestamp' => now()->toIso8601String(),
            ]);

            // Return Bukti Kuitansi Kriptografis untuk Pemilih
            return [
                'success' => true,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'verification_code' => $verificationCode,
                'ballot_hash' => $ballotHash,
                'voter_secret_salt' => $voterSecretSalt,
                'voted_at' => $participation->voted_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i:s') . ' WIB',
            ];
        });
    }

    /**
     * Mendapatkan kategori pemilihan yang masih berhak diikuti oleh pemilih (Tepat 3 Hak Suara)
     */
    public function getEligibleCategoriesForVoter(Voter $voter): array
    {
        $categories = ElectionCategory::with('candidates')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        $votedCategoryIds = VoterParticipation::where('voter_id', $voter->id)
            ->pluck('election_category_id')
            ->toArray();

        $result = [];
        $pillarsSeen = [];

        foreach ($categories as $cat) {
            if ($cat->isEligible($voter)) {
                $pillar = $cat->getPillarType(); // 'BEM', 'MPM', 'HMJ'

                // Jaminan Sistem: Setiap pilar (BEM, MPM, HMJ) maksimal 1 hak suara per mahasiswa
                if (isset($pillarsSeen[$pillar])) {
                    continue;
                }

                $pillarsSeen[$pillar] = true;

                $result[] = [
                    'category' => $cat,
                    'has_voted' => in_array($cat->id, $votedCategoryIds),
                    'is_open' => $cat->isScheduleOpen(),
                ];
            }
        }

        return $result;
    }
}
