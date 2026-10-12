<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'faculty_filter',
        'study_program_filter',
        'start_at',
        'end_at',
        'status_override',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * Memeriksa apakah sesi pemilihan untuk kategori ini saat ini dibuka
     */
    public function isScheduleOpen(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->status_override === 'FORCE_OPEN') {
            return true;
        }

        if ($this->status_override === 'FORCE_CLOSED') {
            return false;
        }

        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Memeriksa apakah waktu pemilihan kategori ini telah habis / ditutup
     */
    public function isEnded(): bool
    {
        if (!$this->is_active || $this->status_override === 'FORCE_CLOSED') {
            return true;
        }

        if ($this->status_override === 'FORCE_OPEN') {
            return false;
        }

        $now = now();
        if ($this->end_at && $now->gte($this->end_at)) {
            return true;
        }

        return false;
    }

    /**
     * Memeriksa apakah pemilihan sedang aktif berjalan (belum habis)
     */
    public function isOngoing(): bool
    {
        return $this->is_active && !$this->isEnded() && $this->isScheduleOpen();
    }

    /**
     * Mendapatkan kode status jadwal terkini
     */
    public function getScheduleStatus(): string
    {
        if (!$this->is_active || $this->status_override === 'FORCE_CLOSED') {
            return 'FORCE_CLOSED';
        }

        if ($this->status_override === 'FORCE_OPEN') {
            return 'FORCE_OPEN';
        }

        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return 'UPCOMING';
        }

        if ($this->end_at && $now->gte($this->end_at)) {
            return 'ENDED';
        }

        return 'OPEN';
    }

    /**
     * Mendapatkan label human-readable untuk status jadwal
     */
    public function getStatusLabel(): string
    {
        return match ($this->getScheduleStatus()) {
            'OPEN' => 'Sedang Berlangsung',
            'UPCOMING' => 'Belum Dimulai',
            'ENDED' => 'Telah Berakhir',
            'FORCE_OPEN' => 'Dipaksa Buka (Override)',
            'FORCE_CLOSED' => 'Ditutup Panitia',
            default => 'Ditutup',
        };
    }

    /**
     * Mendapatkan target timestamp unix untuk hitung mundur JavaScript
     */
    public function getTargetTimestamp(): ?int
    {
        $status = $this->getScheduleStatus();
        if ($status === 'UPCOMING' && $this->start_at) {
            return $this->start_at->timestamp;
        }
        if ($status === 'OPEN' && $this->end_at) {
            return $this->end_at->timestamp;
        }
        return null;
    }

    /**
     * Memeriksa apakah kategori berstatus Aklamasi (Tepat 1 Calon Tunggal - Opsi B)
     */
    public function isAklamasi(): bool
    {
        return $this->candidates()->count() === 1;
    }

    /**
     * Memeriksa apakah kategori berstatus Nihil (0 Calon terdaftar / ditunda)
     */
    public function isNihil(): bool
    {
        return $this->candidates()->count() === 0;
    }

    /**
     * Memeriksa apakah kategori memerlukan pemungutan suara di bilik suara digital
     * Sesuai OPSI B: Hanya dibuka bilik suara jika terdapat minimal 2 pasangan calon (kompetisi)
     */
    public function isVotingRequired(): bool
    {
        return $this->is_active && $this->candidates()->count() >= 2;
    }

    /**
     * Mendapatkan calon tunggal yang ditetapkan secara aklamasi
     */
    public function getAklamasiCandidate(): ?Candidate
    {
        return $this->candidates()->first();
    }

    /**
     * Mendapatkan klasifikasi status kompetisi paslon
     */
    public function getCompetitionStatus(): string
    {
        $count = $this->candidates()->count();
        if ($count >= 2) {
            return 'COMPETITIVE';
        } elseif ($count === 1) {
            return 'AKLAMASI';
        }
        return 'NIHIL';
    }

    /**
     * Mendapatkan metadata lencana status kompetisi / aklamasi
     */
    public function getCompetitionBadge(): array
    {
        return match ($this->getCompetitionStatus()) {
            'COMPETITIVE' => [
                'status' => 'COMPETITIVE',
                'label' => 'Pemungutan Suara Aktif',
                'short_label' => 'Kompetisi',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'dot_color' => 'bg-emerald-500',
                'description' => 'Terdapat 2 atau lebih calon. Bilik suara digital dibuka.',
            ],
            'AKLAMASI' => [
                'status' => 'AKLAMASI',
                'label' => 'Ditetapkan Aklamasi (Calon Tunggal)',
                'short_label' => 'Aklamasi',
                'badge_color' => 'bg-amber-100 text-amber-900 border-amber-300',
                'dot_color' => 'bg-amber-500',
                'description' => 'Hanya 1 calon tunggal. Ditetapkan sah tanpa pemungutan suara.',
            ],
            'NIHIL' => [
                'status' => 'NIHIL',
                'label' => 'Nihil / Ditunda (0 Paslon)',
                'short_label' => 'Nihil',
                'badge_color' => 'bg-slate-200 text-slate-700 border-slate-300',
                'dot_color' => 'bg-slate-400',
                'description' => 'Belum ada calon terdaftar. Pemilihan ditiadakan.',
            ],
        };
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->where('is_active', true)->orderBy('candidate_number', 'asc');
    }

    public function allCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('candidate_number', 'asc');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(VoterParticipation::class);
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(BallotBox::class);
    }

    /**
     * Cek kelayakan pemilih berdasarkan aturan 3 Hak Suara per NIM:
     * 1. BEM Universitas (Terbuka seluruh mahasiswa)
     * 2. MPM Universitas (Terbuka seluruh mahasiswa)
     * 3. Himpunan Mahasiswa Jurusan (HMJ sesuai jurusan NIM pemilih)
     */
    public function isEligible(Voter $voter): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Kategori khusus fakultas (jika ada) dinonaktifkan dari bilik suara agar pas 3 hak suara
        if ($this->faculty_filter && !$this->study_program_filter) {
            return false;
        }

        // Filter Program Studi / HMJ: Wajib cocok dengan jurusan dari NIM mahasiswa
        if ($this->study_program_filter) {
            return strcasecmp(trim($this->study_program_filter), trim($voter->study_program)) === 0;
        }

        // Kategori tingkat universitas (BEM & MPM) terbuka untuk seluruh mahasiswa
        return true;
    }

    /**
     * Mendapatkan klasifikasi pilar hak suara: 'BEM', 'MPM', atau 'HMJ'
     */
    public function getPillarType(): string
    {
        if ($this->study_program_filter) {
            return 'HMJ';
        }

        $code = strtoupper($this->code ?? '');
        $name = strtoupper($this->name ?? '');

        if (str_contains($code, 'MPM') || str_contains($code, 'DPM') || str_contains($name, 'MPM') || str_contains($name, 'PERMUSYAWARATAN') || str_contains($name, 'LEGISLATIF')) {
            return 'MPM';
        }

        return 'BEM';
    }

    /**
     * Mendapatkan metadata lencana pilar hak suara untuk tampilan pemilih
     */
    public function getPillarBadge(): array
    {
        $type = $this->getPillarType();
        return match ($type) {
            'BEM' => [
                'tag' => 'HAK SUARA 1',
                'title' => 'BEM Universitas',
                'description' => 'Presiden & Wakil Presiden Mahasiswa',
                'level' => 'Tingkat Universitas (Seluruh Mahasiswa)',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'dot_color' => 'bg-emerald-500',
            ],
            'MPM' => [
                'tag' => 'HAK SUARA 2',
                'title' => 'MPM Universitas',
                'description' => 'Majelis Permusyawaratan Mahasiswa',
                'level' => 'Tingkat Universitas (Seluruh Mahasiswa)',
                'badge_color' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'dot_color' => 'bg-indigo-500',
            ],
            'HMJ' => [
                'tag' => 'HAK SUARA 3',
                'title' => 'Himpunan Jurusan (HMJ)',
                'description' => 'Ketua Himpunan ' . ($this->study_program_filter ?: 'Jurusan'),
                'level' => 'Khusus Jurusan ' . ($this->study_program_filter ?: 'Mahasiswa'),
                'badge_color' => 'bg-purple-100 text-purple-800 border-purple-200',
                'dot_color' => 'bg-purple-500',
            ],
        };
    }

    /**
     * Sinkronisasi & Pembersihan Otomatis Kategori Duplikat (Menjamin Pas 3 Hak Suara)
     */
    public static function cleanupLegacyDuplicates(): void
    {
        // 1. Bersihkan duplikasi BEM: PRESMA_2026 vs BEM_KM_2026
        $bem = self::where('code', 'BEM_KM_2026')->first();
        $presma = self::where('code', 'PRESMA_2026')->first();
        if ($bem && $presma && $bem->id !== $presma->id) {
            \App\Models\BallotBox::where('election_category_id', $presma->id)->update(['election_category_id' => $bem->id]);
            \App\Models\VoterParticipation::where('election_category_id', $presma->id)->update(['election_category_id' => $bem->id]);
            $presma->update(['is_active' => false]);
        } elseif ($presma && !$bem) {
            $presma->update(['code' => 'BEM_KM_2026', 'is_active' => true]);
        }

        // 2. Bersihkan duplikasi MPM: DPM_KM_2026 vs MPM_KM_2026 & pastikan hanya Ketua Umum (tanpa wakil)
        $mpm = self::where('code', 'MPM_KM_2026')->first();
        $dpm = self::where('code', 'DPM_KM_2026')->first();
        if ($mpm && $dpm && $mpm->id !== $dpm->id) {
            \App\Models\BallotBox::where('election_category_id', $dpm->id)->update(['election_category_id' => $mpm->id]);
            \App\Models\VoterParticipation::where('election_category_id', $dpm->id)->update(['election_category_id' => $mpm->id]);
            $dpm->update(['is_active' => false]);
        } elseif ($dpm && !$mpm) {
            $dpm->update(['code' => 'MPM_KM_2026', 'is_active' => true]);
            $mpm = $dpm;
        }

        if ($mpm) {
            $mpm->update([
                'name' => 'Pemilihan Ketua Umum Majelis Permusyawaratan Mahasiswa (MPM)',
                'description' => '[HAK SUARA 2] Pemilihan Ketua Umum Lembaga Legislatif & Permusyawaratan Mahasiswa Politeknik Negeri Bengkalis (Hanya Ketua Umum, Tanpa Wakil).',
                'is_active' => true,
            ]);

            // Hapus wakil untuk kandidat MPM (hanya Ketua Umum saja)
            \App\Models\Candidate::where('election_category_id', $mpm->id)->update([
                'vice_leader_name' => null,
                'vice_leader_nim' => null,
            ]);
        }

        // 3. Nonaktifkan kategori tingkat fakultas
        self::whereIn('code', ['BEM_FTI_2026', 'BEM_FTE_2026'])->update(['is_active' => false]);
        self::whereNotNull('faculty_filter')->whereNull('study_program_filter')->update(['is_active' => false]);

        // 4. Bersihkan duplikasi Teknik Sipil: HIMASIP (warisan lama) digabung & dihapus demi HMTS (resmi Polbeng)
        $himasip = self::where('code', 'KAHIM_HIMASIP_2026')->first();
        $hmts = self::where('code', 'KAHIM_HMTS_2026')->first();
        if ($himasip) {
            if ($hmts) {
                \App\Models\BallotBox::where('election_category_id', $himasip->id)->update(['election_category_id' => $hmts->id]);
                \App\Models\VoterParticipation::where('election_category_id', $himasip->id)->update(['election_category_id' => $hmts->id]);
            }
            \App\Models\Candidate::where('election_category_id', $himasip->id)->delete();
            $himasip->delete();
        }

        // Hapus kategori dummy warisan lama yang tidak terpakai
        $himasisfo = self::where('code', 'KAHIM_HIMASISFO_2026')->first();
        if ($himasisfo) {
            \App\Models\Candidate::where('election_category_id', $himasisfo->id)->delete();
            $himasisfo->delete();
        }

        self::whereIn('code', ['PRESMA_2026', 'DPM_KM_2026', 'BEM_FTI_2026', 'BEM_FTE_2026'])
            ->whereDoesntHave('ballots')
            ->delete();

        // 5. Inisialisasi & pastikan 8 HMJ resmi Politeknik Negeri Bengkalis aktif berurutan
        $polbengHmj = [
            'KAHIM_HMM_2026' => ['name' => 'Pemilihan Ketua Himpunan Mahasiswa Mesin (HMM)', 'prodi' => 'Teknik Mesin', 'order' => 3, 'c1' => 'Danang Surya', 'c2' => 'Bagas Satria'],
            'KAHIM_HIMKA_2026' => ['name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Perkapalan (HIMKA)', 'prodi' => 'Teknik Perkapalan', 'order' => 4, 'c1' => 'Rian Hidayat', 'c2' => 'Siti Nur Aini'],
            'KAHIM_HIMATRO_2026' => ['name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Elektro (HIMATRO)', 'prodi' => 'Teknik Elektro', 'order' => 5, 'c1' => 'Bambang Setiawan', 'c2' => 'Aulia Rahma'],
            'KAHIM_HIMANIA_2026' => ['name' => 'Pemilihan Ketua Himpunan Mahasiswa Administrasi Niaga (HIMANIA)', 'prodi' => 'Administrasi Niaga', 'order' => 6, 'c1' => 'Dwi Handayani', 'c2' => 'Reza Fahlevi'],
            'KAHIM_HMTI_2026' => ['name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Informatika (HMTI)', 'prodi' => 'Teknik Informatika', 'order' => 7, 'c1' => 'Rizky Alamsyah', 'c2' => 'Dewi Lestari'],
            'KAHIM_HMTS_2026' => ['name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Sipil (HMTS)', 'prodi' => 'Teknik Sipil', 'order' => 8, 'c1' => 'Fajar Pratama', 'c2' => 'Wahyu Ramadhan'],
            'KAHIM_LEDSA_2026' => ['name' => 'Pemilihan Ketua Language Department Students Association (LEDSA)', 'prodi' => 'Bahasa', 'order' => 9, 'c1' => 'Annisa Fitriani', 'c2' => 'M. Iqbal Syahputra'],
            'KAHIM_HTK_2026' => ['name' => 'Pemilihan Ketua Himpunan Taruna Kemaritiman (HTK)', 'prodi' => 'Kemaritiman', 'order' => 10, 'c1' => 'Surya Pratama', 'c2' => 'Aldi Kurniawan'],
        ];

        foreach ($polbengHmj as $code => $info) {
            $cat = self::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $info['name'],
                    'description' => "[HAK SUARA 3] Himpunan Mahasiswa Jurusan {$info['prodi']} Politeknik Negeri Bengkalis.",
                    'faculty_filter' => null,
                    'study_program_filter' => $info['prodi'],
                    'display_order' => $info['order'],
                    'is_active' => true,
                    'status_override' => 'AUTO',
                ]
            );

            if ($cat->allCandidates()->count() === 0) {
                \App\Models\Candidate::create([
                    'election_category_id' => $cat->id,
                    'candidate_number' => 1,
                    'leader_name' => $info['c1'],
                    'leader_nim' => '1201' . $info['order'] . '0011',
                    'vice_leader_name' => null,
                    'vice_leader_nim' => null,
                    'vision' => 'Mewujudkan ' . substr($info['name'], 16) . ' yang berintegritas, mandiri, dan berprestasi.',
                    'mission' => "1. Peningkatan kompetensi akademik & keahlian terapan mahasiswa.\n2. Solidaritas dan pengabdian bagi Politeknik Negeri Bengkalis.",
                    'is_active' => true,
                ]);
                \App\Models\Candidate::create([
                    'election_category_id' => $cat->id,
                    'candidate_number' => 2,
                    'leader_name' => $info['c2'],
                    'leader_nim' => '1201' . $info['order'] . '0022',
                    'vice_leader_name' => null,
                    'vice_leader_nim' => null,
                    'vision' => 'Sinergi kolaboratif mahasiswa jurusan menuju keunggulan nasional.',
                    'mission' => "1. Membangun ruang aspirasi inklusif dan pemberdayaan bakat.\n2. Kolaborasi riset terapan dan kemitraan industri kemahasiswaan.",
                    'is_active' => true,
                ]);
            }
        }
    }
}
