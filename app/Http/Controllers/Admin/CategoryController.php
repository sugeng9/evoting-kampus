<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar seluruh kategori pemilihan organisasi mahasiswa
     */
    public function index()
    {
        // 1. Bersihkan & sinkronisasi duplikasi kategori lama (PRESMA, DPM, Fakultas)
        ElectionCategory::cleanupLegacyDuplicates();

        // 2. Pastikan BEM KM berlabel jelas
        $bem = ElectionCategory::where('code', 'BEM_KM_2026')->first();
        if ($bem && !str_contains($bem->name, 'BEM')) {
            $bem->update([
                'name' => 'Pemilihan Presiden & Wakil Presiden Mahasiswa (BEM KM)',
            ]);
        }

        // 4. Pastikan kategori MPM memiliki kandidat untuk uji coba jika belum ada
        $mpm = ElectionCategory::where('code', 'MPM_KM_2026')->first();
        if ($mpm && $mpm->allCandidates()->count() === 0) {
            Candidate::updateOrCreate(
                ['election_category_id' => $mpm->id, 'candidate_number' => 1],
                [
                    'leader_name' => 'Raka Aditya Pratama',
                    'leader_nim' => '120140025',
                    'vision' => 'MPM Kritis, Solutif, dan Berintegritas Mengawal Aspirasi Seluruh Mahasiswa Kampus.',
                    'mission' => "1. Transparansi legislasi dan anggaran kemahasiswaan.\n2. Optimalisasi kanal dengar pendapat terbuka mahasiswa.",
                    'is_active' => true,
                ]
            );
            Candidate::updateOrCreate(
                ['election_category_id' => $mpm->id, 'candidate_number' => 2],
                [
                    'leader_name' => 'Farah Hanifah',
                    'leader_nim' => '120130060',
                    'vision' => 'Sinergi Legislasi Mahasiswa yang Inklusif, Independen, dan Berdaya Dampak.',
                    'mission' => "1. Reformasi tata kelola peraturan kemahasiswaan yang berkeadilan.\n2. Advokasi aktif hak-hak akademik dan fasilitas kampus.",
                    'is_active' => true,
                ]
            );
        }

        $categories = ElectionCategory::withCount(['candidates', 'ballots', 'participations'])
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalCategories = $categories->count();
        $activeCategories = $categories->where('is_active', true)->count();

        return view('admin.categories.index', compact('categories', 'totalCategories', 'activeCategories'));
    }

    /**
     * Form tambah kategori organisasi baru
     */
    public function create()
    {
        // Ambil data referensi fakultas & prodi dari DPT untuk memudahkan admin
        $faculties = Voter::whereNotNull('faculty')->where('faculty', '!=', '')->distinct()->pluck('faculty');
        $studyPrograms = Voter::whereNotNull('study_program')->where('study_program', '!=', '')->distinct()->pluck('study_program');

        $nextOrder = (ElectionCategory::max('display_order') ?? 0) + 1;

        return view('admin.categories.create', compact('faculties', 'studyPrograms', 'nextOrder'));
    }

    /**
     * Simpan kategori ormawa baru ke database
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        $category = ElectionCategory::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'faculty_filter' => !empty($validated['faculty_filter']) ? trim($validated['faculty_filter']) : null,
            'study_program_filter' => !empty($validated['study_program_filter']) ? trim($validated['study_program_filter']) : null,
            'start_at' => !empty($validated['start_at']) ? Carbon::parse($validated['start_at'], 'Asia/Jakarta') : null,
            'end_at' => !empty($validated['end_at']) ? Carbon::parse($validated['end_at'], 'Asia/Jakarta') : null,
            'status_override' => $validated['status_override'],
            'display_order' => $validated['display_order'],
            'is_active' => $request->has('is_active'),
        ]);

        AuditLog::log('CATEGORY_CREATED', 'admin', Auth::id(), [
            'category_id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori pemilihan '{$category->name}' ({$category->code}) berhasil ditambahkan!");
    }

    /**
     * Form edit kategori organisasi
     */
    public function edit(int $id)
    {
        $category = ElectionCategory::withCount(['candidates', 'ballots'])->findOrFail($id);

        $faculties = Voter::whereNotNull('faculty')->where('faculty', '!=', '')->distinct()->pluck('faculty');
        $studyPrograms = Voter::whereNotNull('study_program')->where('study_program', '!=', '')->distinct()->pluck('study_program');

        return view('admin.categories.edit', compact('category', 'faculties', 'studyPrograms'));
    }

    /**
     * Perbarui data kategori organisasi
     */
    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = ElectionCategory::findOrFail($id);

        $validated = $request->validated();

        $category->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'faculty_filter' => !empty($validated['faculty_filter']) ? trim($validated['faculty_filter']) : null,
            'study_program_filter' => !empty($validated['study_program_filter']) ? trim($validated['study_program_filter']) : null,
            'start_at' => !empty($validated['start_at']) ? Carbon::parse($validated['start_at'], 'Asia/Jakarta') : null,
            'end_at' => !empty($validated['end_at']) ? Carbon::parse($validated['end_at'], 'Asia/Jakarta') : null,
            'status_override' => $validated['status_override'],
            'display_order' => $validated['display_order'],
            'is_active' => $request->has('is_active'),
        ]);

        AuditLog::log('CATEGORY_UPDATED', 'admin', Auth::id(), [
            'category_id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori pemilihan '{$category->name}' berhasil diperbarui!");
    }

    /**
     * Hapus kategori organisasi (Dilengkapi proteksi suara masuk)
     */
    public function destroy(int $id)
    {
        $category = ElectionCategory::withCount('ballots')->findOrFail($id);

        // PROTEKSI INTEGRITAS KPU: Jangan biarkan kategori dihapus jika sudah ada suara yang dicoblos!
        if ($category->ballots_count > 0) {
            return back()->with('error', "Penolakan Keamanan: Kategori '{$category->name}' tidak boleh dihapus karena sudah terdapat {$category->ballots_count} suara pemilih yang masuk di bilik suara! Anda dapat menggunakan opsi 'Nonaktifkan' jika ingin menutup aksesnya.");
        }

        $categoryName = $category->name;
        $category->delete();

        AuditLog::log('CATEGORY_DELETED', 'admin', Auth::id(), [
            'category_id' => $id,
            'name' => $categoryName,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$categoryName}' beserta kandidat terkait berhasil dihapus.");
    }

    /**
     * Toggle status aktif/nonaktif kategori dengan satu klik
     */
    public function toggleStatus(int $id)
    {
        $category = ElectionCategory::findOrFail($id);
        $newStatus = !$category->is_active;

        $category->update(['is_active' => $newStatus]);

        AuditLog::log('CATEGORY_STATUS_TOGGLED', 'admin', Auth::id(), [
            'category_id' => $category->id,
            'is_active' => $newStatus,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori '{$category->name}' berhasil {$statusText}.");
    }

    /**
     * Fitur Cepat: Inisialisasi 3 Pilar Organisasi Mahasiswa Standar (BEM, MPM, & HMJ Jurusan)
     */
    public function seedTenOrganizations()
    {
        $presets = [
            [
                'code' => 'BEM_KM_2026',
                'name' => 'Pemilihan Presiden & Wakil Presiden Mahasiswa (BEM Polbeng)',
                'description' => '[HAK SUARA 1] Badan Eksekutif Mahasiswa (BEM) Politeknik Negeri Bengkalis (Seluruh Mahasiswa).',
                'faculty_filter' => null,
                'study_program_filter' => null,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'MPM_KM_2026',
                'name' => 'Pemilihan Ketua Umum Majelis Permusyawaratan Mahasiswa (MPM)',
                'description' => '[HAK SUARA 2] Pemilihan Ketua Umum Lembaga legislatif & permusyawaratan tertinggi mahasiswa Politeknik Negeri Bengkalis (Hanya Ketua Umum, Tanpa Wakil).',
                'faculty_filter' => null,
                'study_program_filter' => null,
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HMM_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Mesin (HMM)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Teknik Mesin Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Teknik Mesin',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HIMKA_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Perkapalan (HIMKA)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Teknik Perkapalan Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Teknik Perkapalan',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HIMATRO_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Elektro (HIMATRO)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Teknik Elektro Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Teknik Elektro',
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HIMANIA_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Administrasi Niaga (HIMANIA)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Administrasi Niaga Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Administrasi Niaga',
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HMTI_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Informatika (HMTI)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Teknik Informatika Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Teknik Informatika',
                'display_order' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HMTS_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Sipil (HMTS)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Teknik Sipil Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Teknik Sipil',
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_LEDSA_2026',
                'name' => 'Pemilihan Ketua Language Department Students Association (LEDSA)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Bahasa Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Bahasa',
                'display_order' => 9,
                'is_active' => true,
            ],
            [
                'code' => 'KAHIM_HTK_2026',
                'name' => 'Pemilihan Ketua Himpunan Taruna Kemaritiman (HTK)',
                'description' => '[HAK SUARA 3] Himpunan Mahasiswa Jurusan Kemaritiman Polbeng.',
                'faculty_filter' => null,
                'study_program_filter' => 'Kemaritiman',
                'display_order' => 10,
                'is_active' => true,
            ],
        ];

        // Nonaktifkan kategori tingkat fakultas atau kategori warisan
        ElectionCategory::cleanupLegacyDuplicates();

        $insertedCount = 0;
        foreach ($presets as $p) {
            $cat = ElectionCategory::updateOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'faculty_filter' => $p['faculty_filter'],
                    'study_program_filter' => $p['study_program_filter'],
                    'display_order' => $p['display_order'],
                    'is_active' => $p['is_active'],
                    'status_override' => 'AUTO',
                ]
            );
            $insertedCount++;

            // Jika MPM, pastikan kandidat tidak memiliki wakil (hanya Ketua Umum)
            if ($cat->code === 'MPM_KM_2026') {
                if ($cat->allCandidates()->count() === 0) {
                    Candidate::create([
                        'election_category_id' => $cat->id,
                        'candidate_number' => 1,
                        'leader_name' => 'Raka Aditya Pratama',
                        'leader_nim' => '120140025',
                        'vice_leader_name' => null,
                        'vice_leader_nim' => null,
                        'vision' => 'MPM Kritis, Solutif, dan Berintegritas Mengawal Aspirasi Seluruh Mahasiswa Polbeng.',
                        'mission' => "1. Transparansi legislasi dan anggaran kemahasiswaan Polbeng.\n2. Optimalisasi kanal dengar pendapat terbuka mahasiswa.",
                        'is_active' => true,
                    ]);
                    Candidate::create([
                        'election_category_id' => $cat->id,
                        'candidate_number' => 2,
                        'leader_name' => 'Farah Hanifah',
                        'leader_nim' => '120130060',
                        'vice_leader_name' => null,
                        'vice_leader_nim' => null,
                        'vision' => 'Sinergi Legislasi Mahasiswa yang Inklusif, Independen, dan Berdaya Dampak.',
                        'mission' => "1. Reformasi tata kelola peraturan kemahasiswaan yang berkeadilan.\n2. Advokasi aktif hak-hak akademik dan fasilitas kampus.",
                        'is_active' => true,
                    ]);
                } else {
                    Candidate::where('election_category_id', $cat->id)->update([
                        'vice_leader_name' => null,
                        'vice_leader_nim' => null,
                    ]);
                }
            }
        }

        AuditLog::log('POLBENG_ORMAWA_SEEDED', 'admin', Auth::id(), [
            'count' => $insertedCount,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return redirect()->route('admin.categories.index')
            ->with('success', "Struktur 9 Organisasi Mahasiswa Politeknik Negeri Bengkalis (BEM, MPM, & 7 Himpunan Jurusan) berhasil disiapkan!");
    }
}
