<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BallotBox;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class CandidateController extends Controller
{
    /**
     * Tampilkan daftar pasangan calon (Paslon)
     */
    public function index()
    {
        $categories = ElectionCategory::with('allCandidates')
            ->orderBy('display_order', 'asc')
            ->get();

        return view('admin.candidates.index', compact('categories'));
    }

    /**
     * Form tambah paslon baru
     */
    public function create()
    {
        // Tampilkan semua kategori ormawa (termasuk yang non-aktif) agar admin dapat mendaftarkan paslon kapan saja
        $categories = ElectionCategory::orderBy('display_order', 'asc')->get();
        return view('admin.candidates.create', compact('categories'));
    }

    /**
     * Simpan paslon baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'election_category_id' => ['required', 'exists:election_categories,id'],
            'candidate_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates')->where(fn ($query) => 
                    $query->where('election_category_id', $request->election_category_id)
                ),
            ],
            'leader_name' => ['required', 'string', 'max:150'],
            'leader_nim' => ['nullable', 'string', 'max:20'],
            'vice_leader_name' => ['nullable', 'string', 'max:150'],
            'vice_leader_nim' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Maksimal 2MB
            'vision' => ['required', 'string'],
            'mission' => ['required', 'string'],
        ], [
            'candidate_number.unique' => 'Nomor urut ini sudah digunakan oleh paslon lain pada kategori pemilihan tersebut.',
            'photo.image' => 'File foto harus berupa gambar valid (JPG, PNG, WebP).',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'paslon_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/candidates');
            
            if (!File::isDirectory($destination)) {
                File::makeDirectory($destination, 0755, true, true);
            }

            $file->move($destination, $filename);
            $photoPath = 'uploads/candidates/' . $filename;
        }

        $candidate = Candidate::create([
            'election_category_id' => $request->election_category_id,
            'candidate_number' => $request->candidate_number,
            'leader_name' => $request->leader_name,
            'leader_nim' => $request->leader_nim,
            'vice_leader_name' => $request->vice_leader_name,
            'vice_leader_nim' => $request->vice_leader_nim,
            'photo_path' => $photoPath,
            'vision' => $request->vision,
            'mission' => $request->mission,
            'is_active' => true,
        ]);

        AuditLog::log('CANDIDATE_ADDED', 'admin', Auth::id(), [
            'candidate_id' => $candidate->id,
            'leader_name' => $candidate->leader_name,
            'category_id' => $candidate->election_category_id,
        ]);

        return redirect()->route('admin.candidates.index')
            ->with('success', "Pasangan Calon No. 0{$candidate->candidate_number} ({$candidate->leader_name}) berhasil ditambahkan!");
    }

    /**
     * Form edit data paslon
     */
    public function edit(int $id)
    {
        $candidate = Candidate::findOrFail($id);
        $categories = ElectionCategory::orderBy('display_order', 'asc')->get();

        return view('admin.candidates.edit', compact('candidate', 'categories'));
    }

    /**
     * Perbarui data paslon
     */
    public function update(Request $request, int $id)
    {
        $candidate = Candidate::findOrFail($id);

        $request->validate([
            'election_category_id' => ['required', 'exists:election_categories,id'],
            'candidate_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates')->where(fn ($query) => 
                    $query->where('election_category_id', $request->election_category_id)
                )->ignore($candidate->id),
            ],
            'leader_name' => ['required', 'string', 'max:150'],
            'leader_nim' => ['nullable', 'string', 'max:20'],
            'vice_leader_name' => ['nullable', 'string', 'max:150'],
            'vice_leader_nim' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'vision' => ['required', 'string'],
            'mission' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $photoPath = $candidate->photo_path;
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($photoPath && File::exists(public_path($photoPath))) {
                File::delete(public_path($photoPath));
            }

            $file = $request->file('photo');
            $filename = 'paslon_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/candidates');
            
            if (!File::isDirectory($destination)) {
                File::makeDirectory($destination, 0755, true, true);
            }

            $file->move($destination, $filename);
            $photoPath = 'uploads/candidates/' . $filename;
        }

        $candidate->update([
            'election_category_id' => $request->election_category_id,
            'candidate_number' => $request->candidate_number,
            'leader_name' => $request->leader_name,
            'leader_nim' => $request->leader_nim,
            'vice_leader_name' => $request->vice_leader_name,
            'vice_leader_nim' => $request->vice_leader_nim,
            'photo_path' => $photoPath,
            'vision' => $request->vision,
            'mission' => $request->mission,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::log('CANDIDATE_UPDATED', 'admin', Auth::id(), [
            'candidate_id' => $candidate->id,
            'leader_name' => $candidate->leader_name,
        ]);

        return redirect()->route('admin.candidates.index')
            ->with('success', "Data Paslon No. 0{$candidate->candidate_number} berhasil diperbarui!");
    }

    /**
     * Hapus kandidat (dengan perlindungan integritas kotak suara)
     */
    public function destroy(int $id)
    {
        $candidate = Candidate::findOrFail($id);

        // PROTEKSI INTEGRITAS: Cek apakah paslon sudah memiliki suara di kotak suara
        $hasVotes = BallotBox::where('candidate_id', $candidate->id)->exists();
        if ($hasVotes) {
            return back()->with('error', "Paslon ini TIDAK BISA DIHAPUS karena telah menerima suara sah di dalam kotak suara digital! Anda hanya dapat menonaktifkan statusnya.");
        }

        if ($candidate->photo_path && File::exists(public_path($candidate->photo_path))) {
            File::delete(public_path($candidate->photo_path));
        }

        $candidateName = $candidate->leader_name;
        $candidate->delete();

        AuditLog::log('CANDIDATE_DELETED', 'admin', Auth::id(), [
            'candidate_id' => $id,
            'leader_name' => $candidateName,
        ]);

        return redirect()->route('admin.candidates.index')
            ->with('success', "Paslon {$candidateName} berhasil dihapus dari sistem.");
    }
}
