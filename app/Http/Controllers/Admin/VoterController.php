<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ElectionCategory;
use App\Models\PemilihMaster;
use App\Models\Voter;
use App\Models\VoterParticipation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class VoterController extends Controller
{
    /**
     * Tampilkan daftar DPT Mahasiswa dengan filter dan pencarian
     * Kebijakan: DPT terkunci (Immutable), hanya melayani penerbitan PIN Token Suara
     */
    public function index(Request $request)
    {
        Voter::ensureSchema();

        $search = trim($request->input('search'));
        $facultyFilter = $request->input('faculty');
        $academicFilter = $request->input('academic_status'); // 'aktif', 'magang'
        $tokenFilter = $request->input('token_status'); // 'has_token', 'no_token'
        $dptFilter = $request->input('dpt_status', 'DPT'); // default DPT sah



        $query = Voter::withCount('participations');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('study_program', 'like', "%{$search}%");
            });
        }

        if ($facultyFilter) {
            $query->where('faculty', $facultyFilter);
        }

        if ($academicFilter) {
            $query->where('academic_status', $academicFilter);
        }

        if ($tokenFilter === 'has_token') {
            $query->whereNotNull('token_plain')->where('token_plain', '!=', '');
        } elseif ($tokenFilter === 'no_token') {
            $query->where(function ($q) {
                $q->whereNull('token_plain')->orWhere('token_plain', '');
            });
        }

        $regFilter = $request->input('reg_status');
        if ($regFilter === 'aktif') {
            $query->where('pendaftaran_status', 'aktif');
        } elseif ($regFilter === 'pending') {
            $query->where('pendaftaran_status', 'pending');
        } elseif ($regFilter === 'unregistered') {
            $query->where(function ($q) {
                $q->whereNull('pendaftaran_status')->orWhere('pendaftaran_status', '');
            });
        }

        if ($dptFilter === 'DPT') {
            $query->where('is_active', true)->whereIn('academic_status', ['aktif', 'magang']);
        } elseif ($dptFilter === 'TMS') {
            $query->where(function ($q) {
                $q->where('is_active', false)->orWhereNotIn('academic_status', ['aktif', 'magang']);
            });
        }

        $voters = $query->orderBy('nim', 'asc')->paginate(20)->withQueryString();

        // Rekapitulasi Statistik Pemilih Lapangan & Ormawa
        $totalDPT = Voter::where('is_active', true)->whereIn('academic_status', ['aktif', 'magang'])->count();
        $aktifDPT = Voter::where('is_active', true)->where('academic_status', 'aktif')->count();
        $magangDPT = Voter::where('is_active', true)->where('academic_status', 'magang')->count();
        $tmsCount = Voter::where(function ($q) {
            $q->where('is_active', false)->orWhereNotIn('academic_status', ['aktif', 'magang']);
        })->count();
        
        $tokensIssuedCount = Voter::where('is_active', true)
            ->whereIn('academic_status', ['aktif', 'magang'])
            ->whereNotNull('token_plain')
            ->where('token_plain', '!=', '')
            ->count();
            
        $registeredCount = Voter::where('is_active', true)->where('pendaftaran_status', 'aktif')->count();
        $pendingCount = Voter::where('is_active', true)->where('pendaftaran_status', 'pending')->count();
        $unregisteredCount = Voter::where('is_active', true)->where(function ($q) {
            $q->whereNull('pendaftaran_status')->orWhere('pendaftaran_status', '');
        })->count();

        $votedCount = VoterParticipation::distinct('voter_id')->count('voter_id');

        $faculties = Voter::select('faculty')->distinct()->whereNotNull('faculty')->pluck('faculty');
        $totalCategories = ElectionCategory::where('is_active', true)->count();

        return view('admin.voters.index', compact(
            'voters',
            'search',
            'facultyFilter',
            'academicFilter',
            'tokenFilter',
            'regFilter',
            'dptFilter',
            'totalDPT',
            'aktifDPT',
            'magangDPT',
            'tmsCount',
            'tokensIssuedCount',
            'registeredCount',
            'pendingCount',
            'unregisteredCount',
            'votedCount',
            'faculties',
            'totalCategories'
        ));
    }

    /**
     * KUNCI INTEGRITAS DPT (ANTI-CRUD MANUAL):
     * Penambahan pemilih manual dilarang keras demi menjamin netralitas KPU
     * dan mencegah tuduhan pemilih fiktif dari Ormawa / Saksi Paslon.
     */
    public function create()
    {
        return redirect()->route('admin.voters.index')->with('warning', 
            '🔒 Akses Ditolak: Fitur Tambah Pemilih Manual dinonaktifkan demi netralitas KPU dan transparansi terhadap Ormawa. Data DPT hanya boleh bersumber dari hasil penyaringan resmi DPS Kampus.'
        );
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.voters.index')->with('warning', 
            '🔒 Akses Ditolak: Data DPT bersifat final dan terkunci. Penambahan pemilih manual dilarang keras demi menjaga integritas pemilu di hadapan seluruh Ormawa.'
        );
    }

    public function edit(int $id)
    {
        return redirect()->route('admin.voters.index')->with('warning', 
            '🔒 Akses Ditolak: Fitur Edit Identitas Pemilih dinonaktifkan. Panitia dilarang mengubah identitas mahasiswa yang telah disahkan di DPT. Anda hanya diperbolehkan membuat atau mereset PIN Token suara.'
        );
    }

    public function update(Request $request, int $id)
    {
        return redirect()->route('admin.voters.index')->with('warning', 
            '🔒 Akses Ditolak: Pengubahan data identitas DPT dilarang oleh Regulasi Pemira demi transparansi terhadap Ormawa.'
        );
    }

    public function destroy(int $id)
    {
        return redirect()->route('admin.voters.index')->with('warning', 
            '🔒 Akses Ditolak: Penghapusan sepihak pemilih dari DPT dilarang keras demi transparansi terhadap saksi paslon dan seluruh Ormawa. Data DPT bersifat terkunci.'
        );
    }

    /**
     * Menerbitkan (Generate) PIN Token Suara per Mahasiswa
     */
    public function generateToken(Request $request, int $id)
    {
        Voter::ensureSchema();
        $voter = Voter::findOrFail($id);

        if (!$voter->isEligible()) {
            return back()->with('error', "Gagal! Mahasiswa {$voter->name} (NIM: {$voter->nim}) berstatus '{$voter->academic_status_label}' (TMS) sehingga TIDAK BERHAK menerima PIN token suara.");
        }

        $plainToken = (string) mt_rand(100000, 999999);
        $voter->update([
            'password' => Hash::make($plainToken),
            'token_plain' => $plainToken,
            'token_generated_at' => now(),
        ]);

        AuditLog::log('VOTER_TOKEN_GENERATED', 'admin', Auth::id(), [
            'voter_id' => $voter->id,
            'nim' => $voter->nim,
            'name' => $voter->name,
            'academic_status' => $voter->academic_status,
        ]);

        return back()
            ->with('generated_token_data', [
                'nim' => $voter->nim,
                'name' => $voter->name,
                'token' => $plainToken,
                'prodi' => $voter->study_program,
                'time' => now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB',
            ])
            ->with('success', "PIN Token Suara untuk {$voter->name} (NIM: {$voter->nim}) berhasil dibuat: {$plainToken}");
    }

    /**
     * Menerbitkan PIN Token Massal (Bulk Token Generator)
     */
    public function bulkGenerateTokens(Request $request)
    {
        @set_time_limit(180);
        Voter::ensureSchema();
        $mode = $request->input('mode', 'only_untokened'); // 'only_untokened' atau 'regenerate_all'

        $query = Voter::where('is_active', true)->whereIn('academic_status', ['aktif', 'magang']);

        if ($mode === 'only_untokened') {
            $query->where(function ($q) {
                $q->whereNull('token_plain')->orWhere('token_plain', '');
            });
        }

        $count = 0;
        $query->chunkById(100, function ($voters) use (&$count) {
            @set_time_limit(60);
            foreach ($voters as $voter) {
                $plainToken = (string) mt_rand(100000, 999999);
                $voter->update([
                    'password' => Hash::make($plainToken, ['rounds' => 8]),
                    'token_plain' => $plainToken,
                    'token_generated_at' => now(),
                ]);
                $count++;
            }
        });

        AuditLog::log('VOTER_BULK_TOKENS_GENERATED', 'admin', Auth::id(), [
            'mode' => $mode,
            'count' => $count,
        ]);

        $modeLabel = $mode === 'only_untokened' ? 'yang belum memiliki token' : 'seluruh DPT berhak';
        return back()->with('success', "Berhasil menerbitkan {$count} PIN Token Suara baru untuk mahasiswa DPT ({$modeLabel})!");
    }

    /**
     * Unduh Berita Acara Distribusi Token Suara (Untuk Panitia)
     */
    public function exportTokens()
    {
        Voter::ensureSchema();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="berita_acara_distribusi_token_pemira_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIM', 'Nama Mahasiswa', 'Fakultas', 'Program Studi', 'Status Akademik', 'PIN Token Suara', 'Waktu Terbit Token', 'Status Coblos']);

            Voter::where('is_active', true)
                ->whereIn('academic_status', ['aktif', 'magang'])
                ->withCount('participations')
                ->orderBy('study_program', 'asc')
                ->orderBy('nim', 'asc')
                ->chunk(500, function ($voters) use ($handle) {
                    foreach ($voters as $v) {
                        fputcsv($handle, [
                            $v->nim,
                            $v->name,
                            $v->faculty,
                            $v->study_program,
                            $v->academic_status_label,
                            $v->token_plain ?: 'Belum Terbit',
                            $v->token_generated_at ? $v->token_generated_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') . ' WIB' : '-',
                            $v->participations_count > 0 ? "Sudah ({$v->participations_count}/3)" : 'Belum Memilih',
                        ]);
                    }
                });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Unduh Salinan DPT Resmi untuk Ormawa & Saksi Paslon (TANPA PIN TOKEN)
     * Menjamin kepatuhan asas Langsung, Umum, Bebas, dan RAHASIA.
     */
    public function exportOrmawaDpt()
    {
        Voter::ensureSchema();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="salinan_resmi_dpt_saksi_ormawa_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NO', 'NIM', 'NAMA LENGKAP', 'FAKULTAS', 'PROGRAM STUDI', 'STATUS AKADEMIK', 'STATUS DPT', 'KETERANGAN HAK SUARA']);

            $no = 1;
            Voter::where('is_active', true)
                ->whereIn('academic_status', ['aktif', 'magang'])
                ->orderBy('study_program', 'asc')
                ->orderBy('nim', 'asc')
                ->chunk(500, function ($voters) use ($handle, &$no) {
                    foreach ($voters as $v) {
                        fputcsv($handle, [
                            $no++,
                            $v->nim,
                            $v->name,
                            $v->faculty,
                            $v->study_program,
                            $v->academic_status_label,
                            'DPT SAH (MEMENUHI SYARAT)',
                            'BERHAK MEMILIH DI BILIK SUARA',
                        ]);
                    }
                });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilkan Halaman Tahapan & Saringan DPS Kampus ke DPT
     */
    public function dpsVerification(Request $request)
    {
        Voter::ensureSchema();

        $search = trim($request->input('search'));
        $statusFilter = $request->input('status'); // 'all', 'aktif', 'magang', 'cuti', 'do', 'wisuda', 'bermasalah'

        $query = Voter::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('study_program', 'like', "%{$search}%");
            });
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('academic_status', $statusFilter);
        }

        $dpsList = $query->orderBy('academic_status', 'asc')->orderBy('nim', 'asc')->paginate(25)->withQueryString();

        // Statistik Saringan DPS
        $totalDps = Voter::count();
        $aktifCount = Voter::where('academic_status', 'aktif')->count();
        $magangCount = Voter::where('academic_status', 'magang')->count();
        $cutiCount = Voter::where('academic_status', 'cuti')->count();
        $doCount = Voter::where('academic_status', 'do')->count();
        $wisudaCount = Voter::where('academic_status', 'wisuda')->count();
        $bermasalahCount = Voter::where('academic_status', 'bermasalah')->count();

        $dptSahCount = $aktifCount + $magangCount;
        $tmsCount = $cutiCount + $doCount + $wisudaCount + $bermasalahCount;

        return view('admin.voters.dps', compact(
            'dpsList',
            'search',
            'statusFilter',
            'totalDps',
            'aktifCount',
            'magangCount',
            'cutiCount',
            'doCount',
            'wisudaCount',
            'bermasalahCount',
            'dptSahCount',
            'tmsCount'
        ));
    }

    /**
     * Muat Dataset Simulasi Saringan DPS Kampus (70 Mahasiswa: 60 Sah DPT, 10 TMS Dicoret)
     * Dioptimalkan dengan 1x Pre-Hash Bcrypt di luar loop agar selesai dalam < 1 detik.
     */
    public function seedDpsSimulation(Request $request)
    {
        @set_time_limit(120);
        Voter::ensureSchema();

        // 1x Hash di luar loop untuk mencegah timeout 30 detik pada loop 70 data
        $hashedDefaultPin = Hash::make('123456');
        $hashedTmsLocked = Hash::make('TMS_ACCOUNT_LOCKED_2026');

        // 1. Muat 60 DPT Sah (48 Aktif, 12 Magang MBKM)
        $dummyList = self::getDummyDataset(10, '123456');
        foreach ($dummyList as $item) {
            Voter::updateOrCreate(
                ['nim' => $item['nim']],
                [
                    'name' => $item['name'],
                    'faculty' => $item['faculty'],
                    'study_program' => $item['study_program'],
                    'academic_status' => $item['academic_status'] ?? 'aktif',
                    'dpt_status' => 'DPT',
                    'token_plain' => $item['pin'],
                    'token_generated_at' => now(),
                    'password' => $hashedDefaultPin,
                    'is_active' => true,
                ]
            );
        }

        // 2. Tambahkan 10 Mahasiswa yang Tidak Memenuhi Syarat (TMS) untuk demonstrasi saringan DPS
        $tmsSamples = [
            ['nim' => '120140091', 'name' => 'Fandi Kurniawan', 'faculty' => 'Jurusan Teknik Informatika', 'study_program' => 'Teknik Informatika', 'academic_status' => 'cuti'],
            ['nim' => '120140092', 'name' => 'Sinta Maharani', 'faculty' => 'Jurusan Teknik Informatika', 'study_program' => 'Teknik Informatika', 'academic_status' => 'cuti'],
            ['nim' => '120150091', 'name' => 'Riko Saputra', 'faculty' => 'Jurusan Teknik Sipil', 'study_program' => 'Teknik Sipil', 'academic_status' => 'cuti'],
            ['nim' => '120120091', 'name' => 'Bambang Irawan', 'faculty' => 'Jurusan Teknik Mesin', 'study_program' => 'Teknik Mesin', 'academic_status' => 'do'],
            ['nim' => '120130091', 'name' => 'Toni Hermawan', 'faculty' => 'Jurusan Teknik Elektro', 'study_program' => 'Teknik Elektro', 'academic_status' => 'do'],
            ['nim' => '120160091', 'name' => 'Ardiansyah Pratama', 'faculty' => 'Jurusan Teknik Perkapalan', 'study_program' => 'Teknik Perkapalan', 'academic_status' => 'wisuda'],
            ['nim' => '120170091', 'name' => 'Lestari Handayani', 'faculty' => 'Jurusan Administrasi Niaga', 'study_program' => 'Administrasi Niaga', 'academic_status' => 'wisuda'],
            ['nim' => '120180091', 'name' => 'Dedi Gunawan', 'faculty' => 'Jurusan Bahasa', 'study_program' => 'Bahasa', 'academic_status' => 'wisuda'],
            ['nim' => '120190091', 'name' => 'Hendra Syahputra', 'faculty' => 'Jurusan Kemaritiman', 'study_program' => 'Kemaritiman', 'academic_status' => 'bermasalah'],
            ['nim' => '120140099', 'name' => 'Arya Wiguna', 'faculty' => 'Jurusan Teknik Informatika', 'study_program' => 'Teknik Informatika', 'academic_status' => 'bermasalah'],
        ];

        foreach ($tmsSamples as $t) {
            Voter::updateOrCreate(
                ['nim' => $t['nim']],
                [
                    'name' => $t['name'],
                    'faculty' => $t['faculty'],
                    'study_program' => $t['study_program'],
                    'academic_status' => $t['academic_status'],
                    'dpt_status' => 'TMS',
                    'token_plain' => null,
                    'token_generated_at' => null,
                    'password' => $hashedTmsLocked,
                    'is_active' => false,
                ]
            );
        }

        AuditLog::log('DPS_SIMULATION_SEEDED', 'admin', Auth::id(), [
            'total_dps' => 70,
            'dpt_sah' => 60,
            'tms_dicoret' => 10,
        ]);

        return redirect()->route('admin.voters.dps')
            ->with('success', 'Simulasi Saringan DPS berhasil dimuat! Dari 70 data mahasiswa kampus: 60 Mahasiswa (Aktif & Magang) Sah Masuk DPT, dan 10 Mahasiswa (Cuti, DO, Wisuda, Bermasalah) otomatis dicoret/TMS.');
    }

    /**
     * Saklar Cepat: Aktifkan / Nonaktifkan Hak Suara Mahasiswa (dengan Audit Log)
     */
    public function toggleStatus(int $id)
    {
        $voter = Voter::findOrFail($id);
        $voter->is_active = !$voter->is_active;
        $voter->save();

        $statusText = $voter->is_active ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';

        AuditLog::log('VOTER_STATUS_TOGGLED', 'admin', Auth::id(), [
            'voter_id' => $voter->id,
            'nim' => $voter->nim,
            'is_active' => $voter->is_active,
        ]);

        return back()->with('success', "Hak suara NIM {$voter->nim} berhasil {$statusText}.");
    }

    /**
     * Bantuan Darurat Admin: Reset Kata Sandi Mahasiswa (Lupa Password saat Hari Pemilihan)
     */
    public function resetPassword(Request $request, int $id)
    {
        $voter = Voter::findOrFail($id);

        $newPassword = $request->input('new_password') ?: 'Polbeng@' . substr($voter->nim, -4);

        $voter->update([
            'password'           => Hash::make($newPassword),
            'login_attempts'     => 0,
            'locked_until'       => null,
            'pendaftaran_status' => 'aktif',
            'is_active'          => true,
        ]);

        AuditLog::log('VOTER_PASSWORD_RESET_ADMIN', 'admin', Auth::id(), [
            'voter_id' => $voter->id,
            'nim'      => $voter->nim,
            'name'     => $voter->name,
        ]);

        return back()->with('success', "Kata Sandi mahasiswa {$voter->name} (NIM: {$voter->nim}) berhasil di-reset menjadi: {$newPassword}. Mahasiswa sekarang bisa login langsung dengan password tersebut.");
    }

    /**
     * Tampilkan halaman form upload CSV / Excel DPT
     */
    public function import()
    {
        return view('admin.voters.import');
    }

    /**
     * Download template CSV DPT
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_dps_dpt_polbeng.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['nim', 'nama', 'fakultas', 'prodi', 'angkatan', 'tanggal_lahir', 'status_akademik']);
            fputcsv($handle, ['120510001', 'Ahmad Fauzi Ramadan', 'Politeknik Negeri Bengkalis', 'Teknik Informatika', '2022', '2003-05-15', 'aktif']);
            fputcsv($handle, ['120510002', 'Bunga Lestari Putri', 'Politeknik Negeri Bengkalis', 'Teknik Informatika', '2022', '2003-07-22', 'magang']);
            fputcsv($handle, ['120610001', 'Dewi Anggraini', 'Politeknik Negeri Bengkalis', 'Akuntansi', '2021', '2003-03-12', 'cuti']);
            fputcsv($handle, ['120720001', 'Gilang Saputra', 'Politeknik Negeri Bengkalis', 'Teknik Mesin', '2020', '2002-06-17', 'do']);
            fputcsv($handle, ['120720004', 'Juwita Permata Sari', 'Politeknik Negeri Bengkalis', 'Teknik Mesin', '2019', '2001-08-28', 'wisuda']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Proses batch import Excel (.xlsx) atau CSV data pemilih kampus
     * Otomatis melakukan saringan akademik: DPS -> DPT (Aktif/Magang) vs TMS (Cuti/DO/Wisuda/Kasus)
     * Sekaligus mensinkronkan Data Master (PemilihMaster) untuk pendaftaran mandiri pemilih.
     */
    public function processImport(Request $request)
    {
        @set_time_limit(300); // 5 menit untuk file ribuan mahasiswa

        $request->validate([
            'excel_file' => ['required', 'file', 'max:20480'], // Maks. 20MB
        ], [
            'excel_file.required' => 'Pilih file Excel (.xlsx) atau CSV (.csv) data mahasiswa kampus terlebih dahulu.',
            'excel_file.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['xlsx', 'csv', 'txt'])) {
            return back()->with('error', 'Format file tidak didukung. Harap unggah file berekstensi .xlsx atau .csv.');
        }

        if ($extension === 'xlsx') {
            $rows = \App\Services\SimpleExcelReader::parseXlsx($file->getRealPath());
        } else {
            $rows = \App\Services\SimpleExcelReader::parseCsv($file->getRealPath());
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'File Excel/CSV kosong atau tidak memiliki baris data mahasiswa.');
        }

        // Baris pertama adalah header kolom
        $header = array_shift($rows);

        // Petakan indeks kolom secara fleksibel
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleaned = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$colName)));
            if (in_array($cleaned, ['nim', 'npm', 'student_id', 'no_induk', 'nomor_induk'])) {
                $headerMap['nim'] = $index;
            } elseif (in_array($cleaned, ['nama', 'name', 'nama_lengkap', 'student_name', 'nama_mahasiswa'])) {
                $headerMap['name'] = $index;
            } elseif (in_array($cleaned, ['fakultas', 'faculty', 'fak', 'jurusan'])) {
                $headerMap['faculty'] = $index;
            } elseif (in_array($cleaned, ['prodi', 'program_studi', 'study_program', 'major', 'jurusan_prodi'])) {
                $headerMap['study_program'] = $index;
            } elseif (in_array($cleaned, ['angkatan', 'tahun_masuk', 'tahun', 'batch'])) {
                $headerMap['angkatan'] = $index;
            } elseif (in_array($cleaned, ['tanggal_lahir', 'tgl_lahir', 'dob', 'birthdate', 'tgl_lahir_mahasiswa', 'lahir'])) {
                $headerMap['tanggal_lahir'] = $index;
            } elseif (in_array($cleaned, ['pin', 'password', 'token'])) {
                $headerMap['pin'] = $index;
            } elseif (in_array($cleaned, ['status', 'status_akademik', 'academic_status', 'kondisi', 'keterangan'])) {
                $headerMap['academic_status'] = $index;
            }
        }

        if (!isset($headerMap['nim']) || !isset($headerMap['name'])) {
            return back()->with('error', "Format kolom file tidak sesuai. Wajib memiliki kolom 'nim' dan 'nama'. Silakan unduh contoh template resmi.");
        }

        $pinMode = $request->input('pin_mode');
        $defaultPin = $request->input('default_pin') ?: '123456';
        $defaultHashedPin = Hash::make($defaultPin);
        $tmsHashedPassword = Hash::make('TMS_ACCOUNT_LOCKED_2026');

        $successCount = 0;
        $updatedCount = 0;
        $dptSahCount = 0;
        $tmsCount = 0;
        $masterCount = 0;
        $distributionList = [];

        foreach ($rows as $row) {
            if (empty($row) || !isset($row[$headerMap['nim']])) {
                continue;
            }

            $nim = trim((string)$row[$headerMap['nim']]);
            if (empty($nim)) {
                continue;
            }

            $name = trim((string)($row[$headerMap['name']] ?? 'Mahasiswa'));
            $faculty = isset($headerMap['faculty']) && isset($row[$headerMap['faculty']]) ? trim((string)$row[$headerMap['faculty']]) : 'Politeknik Negeri Bengkalis';
            $studyProgram = isset($headerMap['study_program']) && isset($row[$headerMap['study_program']]) ? trim((string)$row[$headerMap['study_program']]) : 'Umum';

            // Angkatan
            $angkatan = null;
            if (isset($headerMap['angkatan']) && isset($row[$headerMap['angkatan']])) {
                $angkatan = trim((string)$row[$headerMap['angkatan']]);
            }
            if (empty($angkatan) && strlen($nim) >= 4) {
                $angkatan = '20' . substr($nim, 2, 2);
            }

            // Klasifikasi Otomatis Status Akademik Kampus
            $rawStatus = isset($headerMap['academic_status']) && isset($row[$headerMap['academic_status']]) 
                ? strtolower(trim((string)$row[$headerMap['academic_status']])) 
                : 'aktif';

            $academicStatus = 'aktif';
            if (str_contains($rawStatus, 'magang') || str_contains($rawStatus, 'mbkm') || str_contains($rawStatus, 'pkl')) {
                $academicStatus = 'magang';
            } elseif (str_contains($rawStatus, 'cuti')) {
                $academicStatus = 'cuti';
            } elseif (str_contains($rawStatus, 'do') || str_contains($rawStatus, 'drop')) {
                $academicStatus = 'do';
            } elseif (str_contains($rawStatus, 'wisuda') || str_contains($rawStatus, 'lulus') || str_contains($rawStatus, 'alumni')) {
                $academicStatus = 'wisuda';
            } elseif (str_contains($rawStatus, 'masalah') || str_contains($rawStatus, 'skors') || str_contains($rawStatus, 'kasus')) {
                $academicStatus = 'bermasalah';
            }

            $isEligible = in_array($academicStatus, ['aktif', 'magang']);
            $dptStatus = $isEligible ? 'DPT' : 'TMS';

            // Parsing Tanggal Lahir (Mendukung YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY)
            $formattedTgl = null;
            if (isset($headerMap['tanggal_lahir']) && isset($row[$headerMap['tanggal_lahir']])) {
                $rawTgl = trim((string)$row[$headerMap['tanggal_lahir']]);
                if (!empty($rawTgl)) {
                    try {
                        $formattedTgl = Carbon::parse($rawTgl)->format('Y-m-d');
                    } catch (\Throwable) {
                        $formattedTgl = null;
                    }
                }
            }

            // SINKRONISASI DATA MASTER (PemilihMaster)
            if ($isEligible) {
                $dptSahCount++;

                // Masukkan ke master jika ada tanggal lahir
                if ($formattedTgl) {
                    PemilihMaster::updateOrCreate(
                        ['nim' => $nim],
                        [
                            'nama'               => $name,
                            'prodi'              => $studyProgram,
                            'angkatan'           => $angkatan,
                            'tanggal_lahir_hash' => hash('sha256', $formattedTgl),
                        ]
                    );
                    $masterCount++;
                }
            } else {
                $tmsCount++;
                // Mahasiswa TMS dicoret dari master agar tidak bisa daftar mandiri
                PemilihMaster::where('nim', $nim)->delete();
            }

            // Penentuan PIN
            $plainPin = null;
            if ($isEligible) {
                if ($pinMode === 'from_file' && isset($headerMap['pin']) && !empty($row[$headerMap['pin']])) {
                    $plainPin = trim((string)$row[$headerMap['pin']]);
                } elseif ($pinMode === 'auto_generate') {
                    $plainPin = (string) mt_rand(100000, 999999);
                } else {
                    $plainPin = $defaultPin;
                }
            }

            // SINKRONISASI TABEL VOTERS
            $existingVoter = Voter::where('nim', $nim)->first();
            if ($existingVoter) {
                $existingVoter->update([
                    'name'            => $name,
                    'faculty'         => $faculty ?: 'Politeknik Negeri Bengkalis',
                    'study_program'   => $studyProgram ?: 'Umum',
                    'angkatan'        => $angkatan,
                    'academic_status' => $academicStatus,
                    'dpt_status'      => $dptStatus,
                    'is_active'       => $isEligible,
                ]);
                $updatedCount++;
            } else {
                Voter::create([
                    'nim'                => $nim,
                    'name'               => $name,
                    'faculty'            => $faculty ?: 'Politeknik Negeri Bengkalis',
                    'study_program'      => $studyProgram ?: 'Umum',
                    'angkatan'           => $angkatan,
                    'academic_status'    => $academicStatus,
                    'dpt_status'         => $dptStatus,
                    'password'           => $plainPin ? ($plainPin === $defaultPin ? $defaultHashedPin : Hash::make($plainPin)) : $tmsHashedPassword,
                    'token_plain'        => $plainPin,
                    'token_generated_at' => $plainPin ? now() : null,
                    'is_active'          => $isEligible,
                    'pendaftaran_status' => $isEligible ? 'pending' : 'dicabut',
                ]);
                $successCount++;
            }

            if ($plainPin) {
                $distributionList[] = [
                    'nim' => $nim,
                    'name' => $name,
                    'faculty' => $faculty,
                    'study_program' => $studyProgram,
                    'pin' => $plainPin,
                ];
            }
        }

        AuditLog::log('DPT_BATCH_IMPORTED_WEB', 'admin', Auth::id(), [
            'total_processed' => $successCount + $updatedCount,
            'baru_ditambah'   => $successCount,
            'diperbarui'      => $updatedCount,
            'dpt_sah'         => $dptSahCount,
            'tms_dicoret'     => $tmsCount,
            'master_terdaftar'=> $masterCount,
            'file_type'       => $extension,
        ]);

        $totalProses = $successCount + $updatedCount;
        $msg = "Penyaringan DPS ke DPT Berhasil! Memproses {$totalProses} mahasiswa kampus: " .
               "{$dptSahCount} Sah Masuk DPT (Aktif/Magang), " .
               "{$tmsCount} Otomatis Dicoret/TMS (Cuti/DO/Wisuda/Kasus).";

        if ($masterCount > 0) {
            $msg .= " Sebanyak {$masterCount} data mahasiswa berhasil disinkronkan ke Data Master Registrasi Mahasiswa.";
        }

        return redirect()->route('admin.voters.index')->with('success', $msg);
    }

    /**
     * Unduh daftar DPT Mahasiswa lengkap dalam format spreadsheet
     */
    public function exportDpt()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rekap_dpt_pemira_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIM', 'Nama Mahasiswa', 'Fakultas', 'Program Studi', 'Status Akademik', 'Status Hak Suara', 'Total Suara Masuk', 'Login Terakhir (WIB)']);
            
            Voter::withCount('participations')->chunk(500, function ($voters) use ($handle) {
                foreach ($voters as $v) {
                    fputcsv($handle, [
                        $v->nim,
                        $v->name,
                        $v->faculty,
                        $v->study_program,
                        $v->academic_status_label,
                        $v->is_active ? 'Aktif (DPT)' : 'Non-Aktif (TMS)',
                        $v->participations_count,
                        $v->last_login_at ? $v->last_login_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') . ' WIB' : 'Belum Pernah',
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generator Data Dummy Pemilih Mahasiswa (Untuk Uji Coba Sesi Voting)
     */
    public function generateDummyData(Request $request)
    {
        $defaultPin = $request->input('default_pin', '123456');
        $countPerProdi = (int) $request->input('count_per_prodi', 10);
        if ($countPerProdi < 1 || $countPerProdi > 10) {
            $countPerProdi = 10;
        }

        $dummyList = self::getDummyDataset($countPerProdi, $defaultPin);
        $inserted = 0;
        $updated = 0;
        $hashedPin = Hash::make($defaultPin);

        foreach ($dummyList as $item) {
            $voter = Voter::where('nim', $item['nim'])->first();
            $dataToSave = [
                'name' => $item['name'],
                'faculty' => $item['faculty'],
                'study_program' => $item['study_program'],
                'academic_status' => $item['academic_status'] ?? 'aktif',
                'dpt_status' => 'DPT',
                'password' => $hashedPin,
                'token_plain' => $defaultPin,
                'token_generated_at' => now(),
                'is_active' => true,
            ];

            if ($voter) {
                $voter->update($dataToSave);
                $updated++;
            } else {
                $dataToSave['nim'] = $item['nim'];
                Voter::create($dataToSave);
                $inserted++;
            }
        }

        AuditLog::log('DUMMY_VOTERS_GENERATED', 'admin', Auth::id(), [
            'total' => count($dummyList),
            'inserted' => $inserted,
            'updated' => $updated,
            'default_pin' => $defaultPin,
        ]);

        return redirect()->route('admin.voters.index')
            ->with('success', "Berhasil memuat " . count($dummyList) . " Data Pemilih Tetap Dummy (Aktif & Magang) dengan PIN default: {$defaultPin}. Siap digunakan untuk simulasi voting!");
    }

    /**
     * Unduh file CSV berisi data dummy pemilih untuk dibuka di Excel atau pengujian import
     */
    public function downloadDummyExcel()
    {
        $dummyList = self::getDummyDataset(10, '123456');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="data_dummy_pemilih_60_mahasiswa.csv"',
        ];

        $callback = function () use ($dummyList) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['nim', 'nama', 'fakultas', 'prodi', 'pin']);
            foreach ($dummyList as $row) {
                fputcsv($handle, [
                    $row['nim'],
                    $row['name'],
                    $row['faculty'],
                    $row['study_program'],
                    $row['pin'],
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bersihkan Data Dummy Pemilih yang belum pernah mencoblos
     */
    public function clearDummyData(Request $request)
    {
        \Illuminate\Support\Facades\Artisan::call('pemira:clean');

        AuditLog::log('DUMMY_DATA_CLEARED_FOR_PRESENTATION', 'admin', Auth::id(), [
            'timestamp' => now()->toIso8601String(),
        ]);

        return redirect()->route('admin.voters.index')
            ->with('success', 'Seluruh data dummy pemilih, kuitansi suara simulasi, dan histori uji coba telah berhasil dibersihkan! Website E-Voting kini bersih dan resmi siap untuk Sosialisasi Pemira Polbeng.');
    }

    /**
     * Dataset Master 60 Mahasiswa Dummy mencakup 6 Prodi / HMJ & 3 Fakultas
     */
    public static function getDummyDataset(int $countPerProdi = 10, string $defaultPin = '123456'): array
    {
        $prodis = [
            [
                'faculty' => 'Jurusan Teknik Mesin',
                'study_program' => 'Teknik Mesin',
                'prefix' => '120120',
                'names' => [
                    'Danang Surya', 'Bagas Satria', 'Eko Prasetyo', 'Teguh Wibowo',
                    'Agus Supriyadi', 'Arif Setiawan', 'Doni Kurnia', 'Tri Wahyudi',
                    'Galih Permana', 'Joko Susilo',
                ],
            ],
            [
                'faculty' => 'Jurusan Teknik Perkapalan',
                'study_program' => 'Teknik Perkapalan',
                'prefix' => '120160',
                'names' => [
                    'Rian Hidayat', 'Siti Nur Aini', 'Dimas Pratama', 'Putri Anggraini',
                    'Farhan Nugraha', 'Anisa Safitri', 'Kevin Sanjaya', 'Zahra Aulia',
                    'Gilang Ramadhan', 'Nadia Amanda',
                ],
            ],
            [
                'faculty' => 'Jurusan Teknik Elektro',
                'study_program' => 'Teknik Elektro',
                'prefix' => '120130',
                'names' => [
                    'Bambang Setiawan', 'Aulia Rahma', 'Rendy Saputra', 'Fitri Handayani',
                    'Hendra Wijaya', 'Larasati Utami', 'Bayu Kurniawan', 'Bella Safira',
                    'Fajar Maulana', 'Salsabila Putri',
                ],
            ],
            [
                'faculty' => 'Jurusan Administrasi Niaga',
                'study_program' => 'Administrasi Niaga',
                'prefix' => '120170',
                'names' => [
                    'Dwi Handayani', 'Reza Fahlevi', 'Aditya Pratama', 'Tiara Maharani',
                    'Ilham Fauzan', 'Rina Fitriani', 'Wahyu Hidayat', 'Maya Kusuma',
                    'Bagas Saputra', 'Syifa Nurul',
                ],
            ],
            [
                'faculty' => 'Jurusan Teknik Informatika',
                'study_program' => 'Teknik Informatika',
                'prefix' => '120140',
                'names' => [
                    'Rizky Alamsyah', 'Dewi Lestari', 'Budi Santoso', 'Siti Rahmawati',
                    'Ilham Pratama', 'Tiara Ramadhani', 'Kevin Sanjaya', 'Zahra Aulia',
                    'Ahmad Fauzi', 'Nurul Hidayah',
                ],
            ],
            [
                'faculty' => 'Jurusan Bahasa',
                'study_program' => 'Bahasa',
                'prefix' => '120180',
                'names' => [
                    'Annisa Fitriani', 'M. Iqbal Syahputra', 'Alifia Nuraini', 'Dinda Ayu Permata',
                    'Fajar Sidiq', 'Hana Novitasari', 'Irfan Maulana', 'Kartika Chandra',
                    'Lukman Hakim', 'Melati Sukma',
                ],
            ],
            [
                'faculty' => 'Jurusan Kemaritiman',
                'study_program' => 'Kemaritiman',
                'prefix' => '120190',
                'names' => [
                    'Surya Pratama', 'Aldi Kurniawan', 'Panji Asmoro', 'Raden Mas Bagus',
                    'Sandi Kurniawan', 'Tari Puspita', 'Umar Said', 'Vina Panduwinata',
                    'Wawan Setiawan', 'Yoga Pratama',
                ],
            ],
            [
                'faculty' => 'Jurusan Teknik Sipil',
                'study_program' => 'Teknik Sipil',
                'prefix' => '120150',
                'names' => [
                    'Fajar Pratama', 'Wahyu Ramadhan', 'Ilham Saputra', 'Bayu Anggoro',
                    'Dimas Aditya', 'Rian Kurnia', 'Siti Maulida', 'Rina Wulandari',
                    'Teguh Firmansyah', 'Agus Santoso',
                ],
            ],
        ];

        $dataset = [];
        foreach ($prodis as $p) {
            $names = array_slice($p['names'], 0, $countPerProdi);
            foreach ($names as $idx => $name) {
                $num = str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT);
                // Mahasiswa ke-9 dan ke-10 (indeks >= 8) adalah mahasiswa magang/MBKM yang sah ikut memilih e-voting, sisanya aktif
                $academicStatus = ($idx >= 8) ? 'magang' : 'aktif';
                $dataset[] = [
                    'nim' => $p['prefix'] . $num,
                    'name' => $name,
                    'faculty' => $p['faculty'],
                    'study_program' => $p['study_program'],
                    'academic_status' => $academicStatus,
                    'pin' => $defaultPin,
                ];
            }
        }

        return $dataset;
    }
}
