@extends('layouts.app')

@section('title', 'Bilik Suara - ' . $category->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-7 pb-28 sm:pb-12">
    
    <!-- Top Bar: Navigasi Kembali & Kategori Surat Suara -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="min-w-0 w-full sm:w-auto">
            @php
                $pillar = $category->getPillarBadge();
            @endphp
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                    {{ $pillar['tag'] }}: {{ $pillar['title'] }}
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-slate-700 font-medium">Bilik Suara Terenkripsi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $category->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau visi, misi, dan profil pasangan calon sebelum menentukan pilihan Anda.</p>
        </div>
        <a href="{{ route('voter.dashboard') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center space-x-1.5 flex-shrink-0 border border-slate-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <!-- Sesi & Waktu Pemilihan Kategori Ini -->
    @php
        $targetTimestamp = $category->getTargetTimestamp();
        $schedStatus = $category->getScheduleStatus();
    @endphp
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center flex-shrink-0 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-xs text-slate-600">
                <span class="font-bold text-slate-800 uppercase tracking-wider block text-[11px]">Jadwal Sesi Kategori:</span>
                <span>
                    Mulai: <strong class="text-slate-800">{{ $category->start_at ? $category->start_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Terbuka' }}</strong>
                    <span class="text-slate-300 mx-1">&bull;</span>
                    Berakhir: <strong class="text-slate-800">{{ $category->end_at ? $category->end_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Hingga Panitia Menutup' }}</strong>
                </span>
            </div>
        </div>

        @if($targetTimestamp && $schedStatus === 'OPEN')
            <div class="bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 text-right flex-shrink-0 w-full sm:w-auto shadow-sm"
                 id="ballotTimer"
                 data-target="{{ $targetTimestamp }}">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Sisa Waktu Sesi Ini:</span>
                <span class="font-mono font-bold text-sm text-slate-900 tracking-wider" id="ballotTimerDisplay">-- : -- : --</span>
            </div>
        @endif
    </div>

    <!-- Candidate List Grid (Surat Suara Paslon) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($category->candidates as $candidate)
            <div class="candidate-card bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-sm hover:border-slate-400 transition flex flex-col justify-between" 
                 id="card-{{ $candidate->id }}">
                
                <div>
                    <!-- Header Paslon & Nomor Urut -->
                    <div class="p-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <span class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-mono font-bold text-base shadow-sm">
                            0{{ $candidate->candidate_number }}
                        </span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            {{ $candidate->vice_leader_name ? 'Pasangan Calon #' . $candidate->candidate_number : 'Kandidat Calon #' . $candidate->candidate_number }}
                        </span>
                    </div>

                    <!-- Foto Kandidat -->
                    <div class="p-5 text-center">
                        <div class="w-32 h-32 mx-auto rounded-xl bg-slate-100 overflow-hidden border border-slate-200 flex items-center justify-center">
                            @if($candidate->photo_path)
                                <img src="{{ asset($candidate->photo_path) }}" alt="{{ $candidate->leader_name }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-14 h-14 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Nama Ketua & Wakil -->
                        <h3 class="text-base font-bold text-slate-900 mt-3 leading-tight">
                            {{ $candidate->leader_name }}
                        </h3>
                        @if($candidate->leader_nim)
                            <p class="text-[11px] font-mono text-slate-400 mt-0.5">NIM: {{ $candidate->leader_nim }}</p>
                        @endif

                        @if($candidate->vice_leader_name)
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Calon Wakil:</span>
                                <h4 class="text-xs font-semibold text-slate-700 leading-tight">{{ $candidate->vice_leader_name }}</h4>
                                @if($candidate->vice_leader_nim)
                                    <p class="text-[11px] font-mono text-slate-400">NIM: {{ $candidate->vice_leader_nim }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Visi & Misi Accordion -->
                    <div class="px-5 pb-5 text-xs text-slate-600 space-y-2.5">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-700 uppercase tracking-wide text-[10px] block mb-1">Visi:</span>
                            <p class="italic text-slate-600 leading-relaxed">"{{ $candidate->vision }}"</p>
                        </div>

                        <details class="cursor-pointer group">
                            <summary class="font-semibold text-slate-800 hover:text-slate-900 text-xs select-none flex items-center justify-between">
                                <span>Lihat Misi &amp; Program Kerja</span>
                                <svg class="w-3.5 h-3.5 transform group-open:rotate-180 transition text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="mt-2 pt-2 border-t border-slate-100 text-slate-600 leading-relaxed text-[11px] whitespace-pre-line">
                                {{ $candidate->mission }}
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Tombol Pilih Paslon -->
                <div class="p-5 bg-slate-50 border-t border-slate-100">
                    <button type="button" 
                            onclick="selectCandidate({{ $candidate->id }}, '0{{ $candidate->candidate_number }} - {{ addslashes($candidate->leader_name) }}')"
                            class="select-btn w-full py-2.5 px-4 rounded-xl border border-slate-300 hover:border-slate-800 text-slate-800 font-semibold text-xs transition flex items-center justify-center space-x-2 bg-white" 
                            id="btn-select-{{ $candidate->id }}">
                        <span class="w-3.5 h-3.5 rounded-full border border-slate-400 flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-white hidden" id="dot-{{ $candidate->id }}"></span>
                        </span>
                        <span>Pilih Paslon 0{{ $candidate->candidate_number }}</span>
                    </button>
                </div>

            </div>
        @endforeach
    </div>

    <!-- Floating Action Bar saat Paslon Dipilih -->
    <div id="floatingBar" class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-3 px-4 sm:py-3.5 sm:px-6 shadow-2xl z-30 hidden transition-all">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3 text-left min-w-0">
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] text-slate-400 block font-semibold uppercase tracking-wider">Pasangan Calon Yang Dipilih:</span>
                    <span id="selectedCandidateText" class="text-xs sm:text-sm font-bold text-slate-900 truncate block"></span>
                </div>
            </div>

            <div class="flex items-center space-x-3 w-full sm:w-auto flex-shrink-0">
                <button type="button" 
                        onclick="openConfirmModal()"
                        class="w-full sm:w-auto px-6 py-2.5 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold rounded-xl transition text-xs tracking-wider uppercase flex items-center justify-center space-x-2 shadow-sm">
                    <span>Lanjutkan ke Konfirmasi Suara</span>
                    <span class="font-mono">&rarr;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI DUA LANGKAH (TWO-STEP FINAL SUBMIT) -->
    <div id="confirmModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-7 shadow-2xl border border-slate-200 transform transition-all max-h-[88vh] overflow-y-auto">
            
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 mx-auto flex items-center justify-center mb-3 border border-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-slate-900">Konfirmasi Pilihan Suara</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kategori: <strong class="text-slate-800">{{ $category->name }}</strong></p>
            </div>

            <!-- Preview Pilihan -->
            <div class="my-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider block">Pilihan Suara Anda:</span>
                <p id="modalCandidateName" class="text-base font-bold text-slate-900 mt-1"></p>
                <p class="text-[11px] text-slate-500 font-medium mt-2">
                    Peringatan: Pilihan suara yang telah disubmit bersifat <strong>final</strong> dan tidak dapat diubah kembali.
                </p>
            </div>

            <!-- Form Submit dengan Re-Verifikasi PIN -->
            <form action="{{ route('voter.ballot.submit') }}" method="POST" id="voteForm" class="space-y-4">
                @csrf
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                <input type="hidden" name="candidate_id" id="formCandidateId" value="">
                <input type="hidden" name="voting_token" value="{{ $votingToken ?? '' }}">

                <div>
                    <label for="confirmation_pin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-center">
                        Masukkan PIN Anda untuk Memvalidasi Suara:
                    </label>
                    <input type="password" 
                           name="confirmation_pin" 
                           id="confirmation_pin" 
                           required 
                           autocomplete="off"
                           placeholder="Masukkan PIN Anda" 
                           class="w-full text-center tracking-widest text-lg py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl font-mono focus:ring-2 focus:ring-slate-800 focus:bg-white outline-none">
                    @error('confirmation_pin')
                        <p class="text-xs text-rose-600 font-semibold mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button" 
                            onclick="closeConfirmModal()" 
                            class="py-2.5 px-4 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition">
                        Batal / Ubah
                    </button>

                    <button type="submit" 
                            id="submitVoteBtn"
                            class="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-bold text-xs shadow-sm transition flex items-center justify-center space-x-2">
                        <span id="submitVoteText">Masukkan ke Kotak Suara</span>
                        <svg id="voteSpinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let selectedId = null;
    let selectedLabel = '';

    function selectCandidate(id, label) {
        selectedId = id;
        selectedLabel = label;

        // Reset semua style card
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.classList.remove('border-slate-900', 'ring-2', 'ring-slate-900/10');
            card.classList.add('border-slate-200');
        });
        document.querySelectorAll('[id^="dot-"]').forEach(dot => dot.classList.add('hidden'));
        document.querySelectorAll('.select-btn').forEach(btn => {
            btn.classList.remove('bg-slate-900', 'text-white', 'border-slate-900');
            btn.classList.add('border-slate-300', 'text-slate-800', 'bg-white');
        });

        // Highlight card yang dipilih
        const activeCard = document.getElementById('card-' + id);
        if (activeCard) {
            activeCard.classList.remove('border-slate-200');
            activeCard.classList.add('border-slate-900', 'ring-2', 'ring-slate-900/10');
        }

        const activeDot = document.getElementById('dot-' + id);
        if (activeDot) activeDot.classList.remove('hidden');

        const activeBtn = document.getElementById('btn-select-' + id);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-slate-800', 'border-slate-300');
            activeBtn.classList.add('bg-slate-900', 'text-white', 'border-slate-900');
        }

        // Tampilkan floating bottom bar
        document.getElementById('selectedCandidateText').innerText = label;
        document.getElementById('floatingBar').classList.remove('hidden');
    }

    function openConfirmModal() {
        if (!selectedId) return;
        document.getElementById('formCandidateId').value = selectedId;
        document.getElementById('modalCandidateName').innerText = selectedLabel;
        document.getElementById('confirmModal').classList.remove('hidden');
        document.getElementById('confirmation_pin').focus();
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        document.getElementById('confirmation_pin').value = '';
    }

    // Client-side Anti-Race Condition: Cegah multiple submit ganda (Idempotency Guard)
    let isSubmittingVote = false;
    document.getElementById('voteForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitVoteBtn');
        const text = document.getElementById('submitVoteText');
        const spinner = document.getElementById('voteSpinner');

        if (isSubmittingVote || btn.disabled) {
            e.preventDefault();
            return false;
        }

        isSubmittingVote = true;
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        text.innerText = 'Mengamankan Suara...';
        spinner.classList.remove('hidden');
    });

    // Real-time countdown timer untuk bilik suara kategori ini
    const ballotTimerEl = document.getElementById('ballotTimer');
    if (ballotTimerEl) {
        const target = parseInt(ballotTimerEl.getAttribute('data-target'));
        const display = document.getElementById('ballotTimerDisplay');

        function updateBallotTimer() {
            const now = Math.floor(Date.now() / 1000);
            const diff = target - now;

            if (diff <= 0) {
                display.innerText = 'WAKTU HABIS';
                display.classList.add('text-rose-600');
                const submitBtn = document.getElementById('submitVoteBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Sesi Ditutup';
                }
                alert('Pemberitahuan: Waktu sesi pemilihan untuk kategori ini telah berakhir.');
                window.location.href = "{{ route('voter.dashboard') }}";
                return;
            }

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            const pad = (n) => n.toString().padStart(2, '0');

            display.innerText = `${pad(hours)} : ${pad(minutes)} : ${pad(seconds)}`;
        }

        updateBallotTimer();
        setInterval(updateBallotTimer, 1000);
    }
</script>
@endpush
