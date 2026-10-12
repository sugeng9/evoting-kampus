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
    public function castVote(Voter $voter, int $categoryId, int $candidateId, ?string $votingToken = null): array
    {
        // 1. Validasi Sesi Pemilihan (Global Status & Waktu)
        $setting = ElectionSetting::current();
        if (!$setting->isOpen()) {
            throw new VotingClosedException("Sesi e-voting saat ini sedang DITUTUP atau berada di luar jadwal resmi.");
        }

        // 1b. Proteksi Idempotency: Validasi Token Sekali Pakai (One-Time Voting Token)
        $tokenSessionKey = 'voting_token_' . $categoryId;
        $expectedToken = session($tokenSessionKey);
        if ($votingToken !== null && (! $expectedToken || ! hash_equals((string) $expectedToken, (string) $votingToken))) {
            AuditLog::log('IDEMPOTENCY_TOKEN_REJECTED', 'voter', $voter->id, [
                'category_id' => $categoryId,
                'nim' => $voter->nim,
                'ip' => request()->ip(),
            ]);
            throw new DoubleVotingException("Token bilik suara tidak valid atau telah kedaluwarsa. Suara tidak diproses untuk mencegah voting ganda.");
        }
        // Segera hanguskan token agar tidak dapat dipakai kembali (Single-Use Consume)
        session()->forget($tokenSessionKey);

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

            // Validasi OPSI B: Bilik suara hanya melayani kategori yang memerlukan pemungutan suara (>= 2 paslon)
            if (!$category->isVotingRequired()) {
                if ($category->isAklamasi()) {
                    throw new IneligibleVoterException("Kategori {$category->name} berstatus AKLAMASI (Calon Tunggal) dan tidak memerlukan pemungutan suara.");
                } else {
                    throw new IneligibleVoterException("Kategori {$category->name} tidak memiliki pasangan calon terdaftar (Nihil) sehingga bilik suara ditiadakan.");
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
            // Menggunakan UUID acak sebagai ID untuk mencegah korelasi auto-increment
            // Ditambahkan jitter waktu acak (5 - 45 detik) untuk mencegah Timing Correlation Attack
            $ballotJitterSeconds = random_int(5, 45);
            $ballot = BallotBox::create([
                'id' => (string) Str::uuid(),
                'election_category_id' => $categoryId,
                'candidate_id' => $candidate->id,
                'ballot_hash' => $ballotHash,
                'verification_code' => $verificationCode,
                'created_at' => now()->subSeconds($ballotJitterSeconds),
            ]);

            // B. Catat Presensi Partisipasi Pemilih dengan Proteksi Database Unique Constraint
            // Menyimpan BAHWA voter telah memilih kategori ini agar tidak bisa memilih dua kali.
            // TIDAK ADA kolom candidate_id atau ballot_id di sini!
            try {
                $participation = VoterParticipation::create([
                    'voter_id' => $lockedVoter->id,
                    'election_category_id' => $categoryId,
                    'receipt_hash' => $receiptHash,
                    'ip_address' => request()->ip(),
                    'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
                    'voted_at' => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Tangkap pelanggaran Unique Constraint (SQLSTATE 23000 / Error 1062)
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'voter_cat_unique_vote')) {
                    AuditLog::log('DB_UNIQUE_CONSTRAINT_BLOCKED', 'voter', $lockedVoter->id, [
                        'category_id' => $categoryId,
                        'nim' => $lockedVoter->nim,
                    ]);
                    throw new DoubleVotingException("Pelanggaran: Database menolak suara ganda untuk NIM {$lockedVoter->nim} pada kategori ini.");
                }
                throw $e;
            }

            // C. Perbarui Status Pemilih: Set sudah_memilih = true dan rekam waktu
            $lockedVoter->sudah_memilih = true;
            $lockedVoter->voted_at = now();
            $lockedVoter->save();

            // D. Audit Trail System (Kerahasiaan Suara Terjamin / Secret Ballot)
            // PENTING: DILARANG MENYIMPAN verification_code di AuditLog bersama voter_id
            // agar relasi antara pemilih dan kandidat pilihan tidak pernah dapat direkonstruksi.
            AuditLog::log('VOTE_SUCCESSFULLY_CAST', 'voter', $lockedVoter->id, [
                'category_id' => $categoryId,
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
                    'requires_voting' => $cat->isVotingRequired(),
                    'is_aklamasi' => $cat->isAklamasi(),
                    'is_nihil' => $cat->isNihil(),
                    'aklamasi_candidate' => $cat->isAklamasi() ? $cat->getAklamasiCandidate() : null,
                    'competition_badge' => $cat->getCompetitionBadge(),
                ];
            }
        }

        return $result;
    }
}
