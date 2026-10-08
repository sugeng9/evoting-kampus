<?php $__env->startSection('title', 'Bilik Suara - ' . $category->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-8">
    
    <!-- Top Bar: Back & Category Title -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <?php
                $pillar = $category->getPillarBadge();
            ?>
            <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider mb-1">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo e($pillar['badge_color']); ?>">
                    <?php echo e($pillar['tag']); ?>: <?php echo e($pillar['title']); ?>

                </span>
                <span class="text-slate-400">&bull;</span>
                <span class="text-emerald-600 flex items-center space-x-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Bilik Suara Digital Terenkripsi</span>
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1"><?php echo e($category->name); ?></h1>
            <p class="text-xs text-slate-500 mt-1">Silakan tinjau profil, visi, dan misi paslon sebelum menentukan pilihan Anda.</p>
        </div>
        <a href="<?php echo e(route('voter.dashboard')); ?>" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Sesi & Waktu Pemilihan Kategori Ini -->
    <?php
        $targetTimestamp = $category->getTargetTimestamp();
        $schedStatus = $category->getScheduleStatus();
    ?>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/40 border border-emerald-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-800 block">Jadwal Sesi Kategori: <?php echo e($category->name); ?></span>
                <span class="text-xs text-slate-600">
                    Mulai: <strong class="text-slate-800"><?php echo e($category->start_at ? $category->start_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Terbuka'); ?></strong>
                    &bull; 
                    Berakhir: <strong class="text-slate-800"><?php echo e($category->end_at ? $category->end_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Hingga Panitia Menutup'); ?></strong>
                </span>
            </div>
        </div>

        <?php if($targetTimestamp && $schedStatus === 'OPEN'): ?>
            <div class="bg-white px-4 py-2 rounded-xl border border-emerald-300 shadow-sm text-right flex-shrink-0 w-full sm:w-auto"
                 id="ballotTimer"
                 data-target="<?php echo e($targetTimestamp); ?>">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 block">Sisa Waktu Sesi Ini:</span>
                <span class="font-mono font-black text-lg text-emerald-900 tracking-wider" id="ballotTimerDisplay">-- : -- : --</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Candidate List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php $__currentLoopData = $category->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="candidate-card bg-white rounded-3xl border-2 border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col justify-between" 
                 id="card-<?php echo e($candidate->id); ?>">
                
                <div>
                    <!-- Header Paslon & Nomor Urut -->
                    <div class="p-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <span class="w-12 h-12 rounded-2xl bg-navy-900 text-white flex items-center justify-center font-black text-xl shadow-md">
                            0<?php echo e($candidate->candidate_number); ?>

                        </span>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                            <?php echo e($candidate->vice_leader_name ? 'Pasangan Calon #' . $candidate->candidate_number : 'Kandidat Calon #' . $candidate->candidate_number); ?>

                        </span>
                    </div>

                    <!-- Foto Kandidat -->
                    <div class="p-6 text-center">
                        <div class="w-36 h-36 mx-auto rounded-2xl bg-slate-100 overflow-hidden border-2 border-slate-200 shadow-inner flex items-center justify-center">
                            <?php if($candidate->photo_path): ?>
                                <img src="<?php echo e(asset($candidate->photo_path)); ?>" alt="<?php echo e($candidate->leader_name); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-16 h-16 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <!-- Nama Ketua & Wakil -->
                        <h3 class="text-lg font-black text-slate-900 mt-4 leading-tight">
                            <?php echo e($candidate->leader_name); ?>

                        </h3>
                        <?php if($candidate->leader_nim): ?>
                            <p class="text-[11px] font-mono text-slate-400">NIM: <?php echo e($candidate->leader_nim); ?></p>
                        <?php endif; ?>

                        <?php if($candidate->vice_leader_name): ?>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Calon Wakil:</span>
                                <h4 class="text-sm font-bold text-slate-700 leading-tight"><?php echo e($candidate->vice_leader_name); ?></h4>
                                <?php if($candidate->vice_leader_nim): ?>
                                    <p class="text-[11px] font-mono text-slate-400">NIM: <?php echo e($candidate->vice_leader_nim); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Visi & Misi Accordion -->
                    <div class="px-6 pb-6 text-xs text-slate-600 space-y-3">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-800 uppercase tracking-wide text-[10px] block mb-1">Visi Utama:</span>
                            <p class="italic text-slate-600 leading-relaxed font-serif">"<?php echo e($candidate->vision); ?>"</p>
                        </div>

                        <details class="cursor-pointer group">
                            <summary class="font-bold text-emerald-600 hover:text-emerald-700 text-xs select-none flex items-center justify-between">
                                <span>Lihat Misi & Program Kerja</span>
                                <svg class="w-4 h-4 transform group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 text-slate-600 leading-relaxed text-[11px] whitespace-pre-line">
                                <?php echo e($candidate->mission); ?>

                            </div>
                        </details>
                    </div>
                </div>

                <!-- Tombol Pilih Paslon -->
                <div class="p-6 bg-slate-50 border-t border-slate-100">
                    <button type="button" 
                            onclick="selectCandidate(<?php echo e($candidate->id); ?>, '0<?php echo e($candidate->candidate_number); ?> - <?php echo e(addslashes($candidate->leader_name)); ?>')"
                            class="select-btn w-full py-3 px-4 rounded-xl border-2 border-slate-300 hover:border-emerald-600 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold text-xs transition flex items-center justify-center space-x-2" 
                            id="btn-select-<?php echo e($candidate->id); ?>">
                        <span class="w-4 h-4 rounded-full border border-slate-400 flex items-center justify-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 hidden" id="dot-<?php echo e($candidate->id); ?>"></span>
                        </span>
                        <span>Pilih Paslon 0<?php echo e($candidate->candidate_number); ?></span>
                    </button>
                </div>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Floating Action Bar saat Paslon Dipilih -->
    <div id="floatingBar" class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-4 px-6 shadow-2xl z-30 hidden transition-all">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3 text-center sm:text-left">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-semibold uppercase tracking-wider">Paslon Yang Anda Pilih:</span>
                    <span id="selectedCandidateText" class="text-sm font-black text-slate-900"></span>
                </div>
            </div>

            <div class="flex items-center space-x-3 w-full sm:w-auto">
                <button type="button" 
                        onclick="openConfirmModal()"
                        class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold rounded-xl shadow-lg shadow-emerald-600/30 transition text-xs tracking-wider uppercase flex items-center justify-center space-x-2">
                    <span>Lanjutkan & Konfirmasi Suara</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI DUA LANGKAH (TWO-STEP FINAL SUBMIT) -->
    <div id="confirmModal" class="fixed inset-0 bg-navy-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-8 shadow-2xl border border-slate-200 transform transition-all">
            
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-xl font-black text-slate-900">Konfirmasi Final Pilihan Anda</h3>
                <p class="text-xs text-slate-500 mt-1">Kategori: <strong class="text-slate-700"><?php echo e($category->name); ?></strong></p>
            </div>

            <!-- Preview Pilihan -->
            <div class="my-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider block">Pilihan Suara Anda:</span>
                <p id="modalCandidateName" class="text-base font-black text-emerald-700 mt-1"></p>
                <p class="text-[11px] text-rose-600 font-semibold mt-2">
                    &bull; Peringatan: Suara yang telah disubmit TIDAK DAPAT DIUBAH atau dibatalkan kembali!
                </p>
            </div>

            <!-- Form Submit dengan Re-Verifikasi PIN -->
            <form action="<?php echo e(route('voter.ballot.submit')); ?>" method="POST" id="voteForm" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="category_id" value="<?php echo e($category->id); ?>">
                <input type="hidden" name="candidate_id" id="formCandidateId" value="">

                <div>
                    <label for="confirmation_pin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-center">
                        Masukkan PIN Anda untuk Membuka Gembok Suara:
                    </label>
                    <input type="password" 
                           name="confirmation_pin" 
                           id="confirmation_pin" 
                           required 
                           autocomplete="off"
                           placeholder="Masukkan PIN Anda" 
                           class="w-full text-center tracking-widest text-lg py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <?php $__errorArgs = ['confirmation_pin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-xs text-rose-600 font-semibold mt-1 text-center"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3">
                    <button type="button" 
                            onclick="closeConfirmModal()" 
                            class="py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                        Batal / Ubah
                    </button>

                    <button type="submit" 
                            id="submitVoteBtn"
                            class="py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2">
                        <span id="submitVoteText">Ya, Coblos Sekarang</span>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    let selectedId = null;
    let selectedLabel = '';

    function selectCandidate(id, label) {
        selectedId = id;
        selectedLabel = label;

        // Reset semua style card
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.classList.remove('border-emerald-500', 'ring-4', 'ring-emerald-500/20');
            card.classList.add('border-slate-200');
        });
        document.querySelectorAll('[id^="dot-"]').forEach(dot => dot.classList.add('hidden'));
        document.querySelectorAll('.select-btn').forEach(btn => {
            btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
            btn.classList.add('border-slate-300', 'text-slate-700');
        });

        // Highlight card yang dipilih
        const activeCard = document.getElementById('card-' + id);
        if (activeCard) {
            activeCard.classList.remove('border-slate-200');
            activeCard.classList.add('border-emerald-500', 'ring-4', 'ring-emerald-500/20');
        }

        const activeDot = document.getElementById('dot-' + id);
        if (activeDot) activeDot.classList.remove('hidden');

        const activeBtn = document.getElementById('btn-select-' + id);
        if (activeBtn) {
            activeBtn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
            activeBtn.classList.remove('text-slate-700');
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

    // Client-side Anti-Race Condition: Cegah multiple submit ganda
    document.getElementById('voteForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitVoteBtn');
        const text = document.getElementById('submitVoteText');
        const spinner = document.getElementById('voteSpinner');

        if (btn.disabled) {
            e.preventDefault();
            return false;
        }

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
                window.location.href = "<?php echo e(route('voter.dashboard')); ?>";
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/voter/ballot.blade.php ENDPATH**/ ?>