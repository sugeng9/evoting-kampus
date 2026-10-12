@php
    $tawkPropertyId = config('helpdesk.tawkto.property_id');
    $tawkWidgetId = config('helpdesk.tawkto.widget_id', 'default');
    $waNumber = config('helpdesk.whatsapp.number', '6285187953580');
    $waDisplay = config('helpdesk.whatsapp.display_number', '+62 851-8795-3580');
    $cleanWaNumber = preg_replace('/[^0-9]/', '', $waNumber);
    $waName = config('helpdesk.whatsapp.name', 'Panitia Helpdesk KPR Politeknik Negeri Bengkalis');
    $operatingHours = config('helpdesk.operating_hours', '08.00 - 17.00 WIB');
    $isVoterLoggedIn = Auth::guard('voter')->check();
    $currentVoter = $isVoterLoggedIn ? Auth::guard('voter')->user() : null;
@endphp

<!-- ======================================================== -->
<!-- FLOATING HELPDESK & LIVE CHAT TRIGGER BUTTON             -->
<!-- ======================================================== -->
<div id="helpdeskFloatingWidget" class="fixed bottom-5 right-5 z-40 flex flex-col items-end select-none">
    
    <!-- Tooltip Notification / Greeting (Muncul singkat saat awal muat) -->
    <div id="helpdeskGreetingBadge" class="hidden sm:flex items-center space-x-2 mb-2 px-3.5 py-1.5 bg-navy-950/95 text-white rounded-2xl shadow-xl border border-emerald-500/40 text-xs backdrop-blur-md animate-bounce">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-[11px] font-medium">Butuh bantuan? Tim KPR &amp; PanWas siap menjawab</span>
        <button type="button" onclick="document.getElementById('helpdeskGreetingBadge').classList.add('hidden')" class="text-slate-400 hover:text-white ml-1 text-sm font-bold leading-none">&times;</button>
    </div>

    <!-- Main Floating Action Button (FAB) -->
    <button type="button" 
            onclick="openHelpdeskModal('chat')" 
            id="helpdeskTriggerBtn"
            class="group flex items-center space-x-2.5 px-4 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-navy-900 hover:from-emerald-500 hover:to-teal-700 text-white rounded-2xl shadow-xl shadow-emerald-900/30 border border-emerald-400/40 transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-emerald-500/30"
            aria-label="Pusat Bantuan & Live Chat Helpdesk">
        
        <!-- Live Chat Icon with Ping Indicator -->
        <div class="relative flex items-center justify-center">
            <svg class="w-5 h-5 text-white transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
            </span>
        </div>

        <div class="flex flex-col text-left">
            <span class="text-xs font-black tracking-tight leading-tight flex items-center space-x-1">
                <span>Bantuan &amp; Live Chat</span>
            </span>
            <span class="text-[9px] text-emerald-200 font-mono tracking-wider font-semibold uppercase leading-none mt-0.5">Online &bull; Politeknik Negeri Bengkalis</span>
        </div>
    </button>
</div>

<!-- ======================================================== -->
<!-- MODAL PUSAT BANTUAN & HELPDESK MAHASISWA                 -->
<!-- ======================================================== -->
<div id="voterHelpdeskModal" 
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="helpdeskModalTitle">
    
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[85vh] animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Banner Modal -->
        <div class="bg-gradient-to-r from-navy-950 via-slate-900 to-navy-900 p-5 text-white relative overflow-hidden flex-shrink-0">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-teal-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-md shadow-emerald-500/30 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 id="helpdeskModalTitle" class="text-base font-black text-white tracking-tight">Helpdesk Pemira Politeknik Negeri Bengkalis</h3>
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Online</span>
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">Layanan Informasi &amp; Solusi Kendala Pemilih Mahasiswa</p>
                    </div>
                </div>

                <!-- Tombol Tutup (X) -->
                <button type="button" 
                        onclick="closeHelpdeskModal()" 
                        class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition focus:outline-none"
                        aria-label="Tutup jendela bantuan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Jam Operasional Info Bar -->
            <div class="mt-3 flex items-center justify-between text-[11px] text-slate-300 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700/80 font-mono">
                <span class="flex items-center space-x-1.5">
                    <span>🕒 Jam Layanan: <strong>{{ $operatingHours }}</strong></span>
                </span>
                <span>KPR &amp; PanWas Politeknik Negeri Bengkalis</span>
            </div>
        </div>

        <!-- Navigation Tabs (4 Fitur Helpdesk) -->
        <div class="px-5 pt-4 pb-2 bg-slate-50 border-b border-slate-200 flex-shrink-0">
            <div class="grid grid-cols-4 gap-1 p-1 bg-slate-200/80 rounded-2xl text-xs font-bold">
                <button type="button" 
                        id="helpdesk-tab-btn-chat" 
                        onclick="switchHelpdeskTab('chat')" 
                        class="py-2 px-1 rounded-xl transition flex items-center justify-center space-x-1 text-slate-900 bg-white shadow-sm border border-slate-200/60">
                    <span>💬</span>
                    <span class="truncate">Live Chat</span>
                </button>
                <button type="button" 
                        id="helpdesk-tab-btn-wa" 
                        onclick="switchHelpdeskTab('wa')" 
                        class="py-2 px-1 rounded-xl transition flex items-center justify-center space-x-1 text-slate-600 hover:text-slate-900">
                    <span>📱</span>
                    <span class="truncate">WhatsApp</span>
                </button>
                <button type="button" 
                        id="helpdesk-tab-btn-faq" 
                        onclick="switchHelpdeskTab('faq')" 
                        class="py-2 px-1 rounded-xl transition flex items-center justify-center space-x-1 text-slate-600 hover:text-slate-900">
                    <span>❓</span>
                    <span class="truncate">FAQ Kendala</span>
                </button>
                <button type="button" 
                        id="helpdesk-tab-btn-sengketa" 
                        onclick="switchHelpdeskTab('sengketa')" 
                        class="py-2 px-1 rounded-xl transition flex items-center justify-center space-x-1 text-slate-600 hover:text-slate-900">
                    <span>⚖️</span>
                    <span class="truncate">Sengketa</span>
                </button>
            </div>
        </div>

        <!-- Modal Body (Scrollable Container) -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-4 text-slate-700">

            <!-- ============================================== -->
            <!-- 1. TAB LIVE CHAT (TAWK.TO INTEGRATION)         -->
            <!-- ============================================== -->
            <div id="helpdesk-panel-chat" class="space-y-4">
                
                @if(!empty($tawkPropertyId))
                    <!-- Jika Tawk.to Property ID sudah diatur di .env -->
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md flex-shrink-0">
                                💬
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-emerald-950">Live Chat Tawk.to Aktif</h4>
                                <p class="text-xs text-emerald-800 mt-0.5">Petugas panitia terhubung langsung untuk merespons pertanyaan Anda.</p>
                            </div>
                        </div>

                        @if($isVoterLoggedIn && $currentVoter)
                            <div class="p-3 bg-white/80 rounded-xl border border-emerald-200 text-xs space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Identitas Pemilih Terhubung:</span>
                                <div class="font-bold text-slate-800">{{ $currentVoter->name }} (NIM: {{ $currentVoter->nim }})</div>
                                <div class="text-slate-500 text-[11px]">{{ $currentVoter->study_program }} &bull; {{ $currentVoter->faculty }}</div>
                            </div>
                        @endif

                        <button type="button" 
                                onclick="startTawkChat()" 
                                class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2 text-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>Buka Obrolan Live Chat Sekarang</span>
                        </button>
                    </div>
                @else
                    <!-- Panduan / Status Tawk.to Free Live Chat -->
                    <div class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-200 space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow flex-shrink-0 mt-0.5">
                                ⚡
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-sm font-black text-indigo-950">Integrasi Live Chat Gratis (Tawk.to)</h4>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">100% Free</span>
                                </div>
                                <p class="text-xs text-indigo-900/80 mt-1 leading-relaxed">
                                    Sistem telah mendukung widget obrolan langsung gratis menggunakan <strong>Tawk.to</strong> (tanpa batasan pesan &amp; tersedia aplikasi mobile gratis untuk panitia).
                                </p>
                            </div>
                        </div>

                        <!-- 3 Langkah Aktivasi untuk Panitia / Admin -->
                        <div class="p-3 bg-white rounded-xl border border-indigo-100 text-xs space-y-2">
                            <span class="text-[11px] font-bold text-slate-800 block">Cara Menghubungkan Tawk.to ke Aplikasi:</span>
                            <ol class="list-decimal list-inside space-y-1 text-slate-600 text-[11px]">
                                <li>Daftar akun gratis di <a href="https://www.tawk.to/" target="_blank" class="text-indigo-600 font-bold hover:underline">tawk.to &nearr;</a></li>
                                <li>Masuk ke <strong>Administration &rarr; Chat Widget</strong> dan salin <strong>Property ID</strong> &amp; <strong>Widget ID</strong> Anda.</li>
                                <li>Masukkan ke file <code class="bg-slate-100 px-1 py-0.5 rounded text-rose-600 font-mono font-bold">.env</code> pada baris: <code class="font-mono text-emerald-700">TAWKTO_PROPERTY_ID=...</code></li>
                            </ol>
                        </div>

                        <!-- Fallback Cepat: WhatsApp Panitia -->
                        <div class="pt-1">
                            <button type="button" 
                                    onclick="switchHelpdeskTab('wa')" 
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center space-x-2 text-xs">
                                <span>📱 Hubungi Petugas via WhatsApp Sekarang</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Kontak Alternatif Cepat -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <button type="button" 
                            onclick="switchHelpdeskTab('wa')" 
                            class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-left transition flex items-center space-x-2.5">
                        <span class="text-xl">📱</span>
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">WhatsApp KPR</span>
                            <span class="text-[10px] text-slate-500">Respon dalam 5 menit</span>
                        </div>
                    </button>
                    <button type="button" 
                            onclick="switchHelpdeskTab('faq')" 
                            class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-left transition flex items-center space-x-2.5">
                        <span class="text-xl">💡</span>
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">FAQ &amp; Solusi</span>
                            <span class="text-[10px] text-slate-500">Jawaban instan mandiri</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 2. TAB WHATSAPP PANITIA KPR & PANWAS           -->
            <!-- ============================================== -->
            <div id="helpdesk-panel-wa" class="hidden space-y-4">
                <div class="p-4 bg-emerald-50/90 rounded-2xl border border-emerald-200 text-xs text-emerald-950 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-emerald-900 flex items-center space-x-1.5">
                            <span>📱 Layanan WhatsApp Resmi Helpdesk:</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Aktif &bull; {{ $operatingHours }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between flex-wrap gap-2 pt-1 bg-white/80 p-3 rounded-xl border border-emerald-200/80">
                        <div>
                            <div class="text-sm font-black font-mono text-emerald-800 tracking-wide">{{ $waDisplay }}</div>
                            <div class="text-[11px] text-emerald-700 font-medium">{{ $waName }}</div>
                        </div>
                        <a href="https://wa.me/{{ $cleanWaNumber }}?text={{ urlencode('Halo Panitia Helpdesk Pemira Politeknik Negeri Bengkalis, saya memerlukan bantuan teknis.') }}" 
                           target="_blank" 
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-sm text-xs transition">
                            <span>Chat Langsung</span>
                            <span class="font-mono text-xs">&nearr;</span>
                        </a>
                    </div>
                    <p class="text-[11px] text-emerald-800 leading-relaxed">
                        Isi formulir rincian kendala di bawah ini agar pesan otomatis tersusun rapi beserta NIM Anda, atau klik tombol <strong>Chat Langsung</strong> di atas.
                    </p>
                </div>

                <form id="helpdeskWaForm" onsubmit="submitWaHelpdesk(event)" class="space-y-3.5 text-xs">
                    <!-- Nama Mahasiswa -->
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1 text-[11px]">Nama Lengkap</label>
                        <input type="text" 
                               id="wa_sender_name" 
                               value="{{ $currentVoter ? $currentVoter->name : '' }}" 
                               required 
                               placeholder="Nama Mahasiswa" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white text-xs">
                    </div>

                    <!-- NIM Mahasiswa -->
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1 text-[11px]">Nomor Induk Mahasiswa (NIM)</label>
                        <input type="text" 
                               id="wa_sender_nim" 
                               value="{{ $currentVoter ? $currentVoter->nim : '' }}" 
                               required 
                               placeholder="Contoh: 120140001" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white text-xs">
                    </div>

                    <!-- Kategori Masalah -->
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1 text-[11px]">Topik Kendala yang Dihadapi</label>
                        <select id="wa_issue_topic" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white text-xs">
                            <option value="NIM Tidak Terdaftar di Portal">NIM Tidak Terdaftar / Tidak Ditemukan di Portal</option>
                            <option value="Kode OTP Email Tidak Masuk">Kode OTP Verifikasi Email Tidak Terkirim / Masuk</option>
                            <option value="Lupa Kata Sandi / Akun Dikunci">Lupa Kata Sandi / Akun Pemilih Terkunci</option>
                            <option value="NIM Sudah Terdaftar oleh Orang Lain">NIM Digunakan / Didaftarkan oleh Orang Lain</option>
                            <option value="Kendala Teknis saat Mencoblos di Bilik Suara">Kendala Teknis saat Memilih di Bilik Suara</option>
                            <option value="Pertanyaan Seputar Paslon & Jadwal">Pertanyaan Seputar Paslon &amp; Jadwal Pemira</option>
                            <option value="Pertanyaan Lainnya">Pertanyaan Umum Lainnya</option>
                        </select>
                    </div>

                    <!-- Detail Pesan -->
                    <div>
                        <label class="block font-bold uppercase tracking-wider text-slate-700 mb-1 text-[11px]">Rincian Kendala / Pesan Singkat</label>
                        <textarea id="wa_issue_notes" 
                                  rows="2" 
                                  placeholder="Jelaskan secara singkat kendala yang Anda alami..." 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white text-xs"></textarea>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center justify-center space-x-2 text-xs">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Kirim Pesan ke WhatsApp Petugas &rarr;</span>
                    </button>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- 3. TAB FAQ & SOLUSI KENDALA CEPAT              -->
            <!-- ============================================== -->
            <div id="helpdesk-panel-faq" class="hidden space-y-3 text-xs">
                
                <div class="text-[11px] text-slate-500 mb-2">
                    Klik pertanyaan di bawah untuk melihat solusi langkah-demi-langkah secara mandiri:
                </div>

                <!-- FAQ 1 -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    <button type="button" 
                            onclick="toggleFaqAccordion('faq-ans-1', 'faq-icon-1')" 
                            class="w-full p-3.5 text-left font-bold text-slate-800 hover:text-emerald-700 flex items-center justify-between transition">
                        <span>1. NIM Saya Tidak Ditemukan / Tidak Terdaftar?</span>
                        <span id="faq-icon-1" class="text-slate-400 font-mono text-sm">&plus;</span>
                    </button>
                    <div id="faq-ans-1" class="hidden px-4 pb-3.5 text-slate-600 leading-relaxed border-t border-slate-200/60 pt-2 text-[11px] space-y-1.5">
                        <p>Pastikan Anda memasukkan NIM sesuai Kartu Tanda Mahasiswa (KTM) tanpa spasi atau tanda baca. Jika Anda berstatus mahasiswa aktif Politeknik Negeri Bengkalis namun belum terdaftar di DPT, silakan hubungi Helpdesk via WhatsApp dengan melampirkan foto KTM &amp; KRS semester aktif untuk saringan DPS manual oleh panitia KPR (Komisi Pemilihan Raya) &amp; PanWas (Panitia Pengawas).</p>
                        <a href="{{ route('nim.status.form') }}" class="inline-block text-emerald-600 font-bold hover:underline">Periksa status NIM di sini &rarr;</a>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    <button type="button" 
                            onclick="toggleFaqAccordion('faq-ans-2', 'faq-icon-2')" 
                            class="w-full p-3.5 text-left font-bold text-slate-800 hover:text-emerald-700 flex items-center justify-between transition">
                        <span>2. Kode OTP Email Tidak Muncul di Kotak Masuk?</span>
                        <span id="faq-icon-2" class="text-slate-400 font-mono text-sm">&plus;</span>
                    </button>
                    <div id="faq-ans-2" class="hidden px-4 pb-3.5 text-slate-600 leading-relaxed border-t border-slate-200/60 pt-2 text-[11px] space-y-1">
                        <p>1. Periksa folder <strong>Spam</strong>, <strong>Junk</strong>, atau tab <strong>Promotions</strong> di aplikasi Gmail / email Anda.</p>
                        <p>2. Tunggu 60 detik sebelum menekan tombol "Kirim Ulang OTP" agar tidak terkena limitasi sistem.</p>
                        <p>3. Pastikan penulisan alamat email aktif dan tidak ada kesalahan ketik (misal: <em>@gmail.com</em> bukan <em>@gmai.com</em>).</p>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    <button type="button" 
                            onclick="toggleFaqAccordion('faq-ans-3', 'faq-icon-3')" 
                            class="w-full p-3.5 text-left font-bold text-slate-800 hover:text-emerald-700 flex items-center justify-between transition">
                        <span>3. NIM Saya Sudah Didaftarkan oleh Orang Lain?</span>
                        <span id="faq-icon-3" class="text-slate-400 font-mono text-sm">&plus;</span>
                    </button>
                    <div id="faq-ans-3" class="hidden px-4 pb-3.5 text-slate-600 leading-relaxed border-t border-slate-200/60 pt-2 text-[11px] space-y-1.5">
                        <p>Jika NIM Anda disalahgunakan atau didaftarkan pihak lain tanpa izin, Anda berhak mengajukan <strong>Sengketa Hak Suara</strong>. Panitia Helpdesk akan memverifikasi KTM asli Anda dan mencabut akun palsu tersebut agar Anda dapat mendaftar ulang secara sah.</p>
                        <a href="{{ route('nim.sengketa.form') }}" class="inline-block text-rose-600 font-bold hover:underline">Buka Formulir Sengketa KTM Resmi &rarr;</a>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    <button type="button" 
                            onclick="toggleFaqAccordion('faq-ans-4', 'faq-icon-4')" 
                            class="w-full p-3.5 text-left font-bold text-slate-800 hover:text-emerald-700 flex items-center justify-between transition">
                        <span>4. Apakah Pilihan Paslon Saya Bisa Terlihat oleh Panitia/Dosen?</span>
                        <span id="faq-icon-4" class="text-slate-400 font-mono text-sm">&plus;</span>
                    </button>
                    <div id="faq-ans-4" class="hidden px-4 pb-3.5 text-slate-600 leading-relaxed border-t border-slate-200/60 pt-2 text-[11px]">
                        <p><strong>100% RAHASIA (Asas LUBER JURDIL).</strong> Sistem E-Voting Kampus menggunakan arsitektur <em>Secret Ballot Engine</em> yang memisahkan identitas NIM pemilih dari kertas suara yang dicoblos menggunakan pengacakan token SHA-256. Tidak ada administrator, dosen, maupun panitia yang dapat mengetahui pilihan Anda.</p>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    <button type="button" 
                            onclick="toggleFaqAccordion('faq-ans-5', 'faq-icon-5')" 
                            class="w-full p-3.5 text-left font-bold text-slate-800 hover:text-emerald-700 flex items-center justify-between transition">
                        <span>5. Bagaimana Cara Memverifikasi Suara Saya Sudah Sah Masuk?</span>
                        <span id="faq-icon-5" class="text-slate-400 font-mono text-sm">&plus;</span>
                    </button>
                    <div id="faq-ans-5" class="hidden px-4 pb-3.5 text-slate-600 leading-relaxed border-t border-slate-200/60 pt-2 text-[11px] space-y-1.5">
                        <p>Setelah mencoblos di bilik suara, Anda akan menerima <strong>Kuitansi Suara Digital</strong> berisikan Kode Verifikasi / Hash Kriptografi. Salin kode tersebut dan buka menu <strong>Audit Kuitansi Suara</strong> untuk memastikan kuitansi Anda tercatat sah di kotak suara digital.</p>
                        <a href="{{ route('verify_ballot') }}" class="inline-block text-emerald-600 font-bold hover:underline">Buka Layar Audit Kuitansi Suara &rarr;</a>
                    </div>
                </div>

                <!-- FAQ 6 -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
                    <button type="button" 
                            onclick="toggleFaqAccordion('faq-ans-6', 'faq-icon-6')" 
                            class="w-full p-3.5 text-left font-bold text-slate-800 hover:text-emerald-700 flex items-center justify-between transition">
                        <span>6. Koneksi Terputus saat Memilih di Bilik Suara?</span>
                        <span id="faq-icon-6" class="text-slate-400 font-mono text-sm">&plus;</span>
                    </button>
                    <div id="faq-ans-6" class="hidden px-4 pb-3.5 text-slate-600 leading-relaxed border-t border-slate-200/60 pt-2 text-[11px]">
                        <p>Jika internet Anda terputus sebelum Anda menekan tombol konfirmasi dan memasukkan PIN validasi, suara Anda <strong>belum terhitung</strong>. Cukup muat ulang (refresh) halaman bilik suara atau masuk kembali dari dashboard untuk mengulang pencoblosan.</p>
                    </div>
                </div>

            </div>

            <!-- ============================================== -->
            <!-- 4. TAB SENGKETA HAK SUARA RESMI                -->
            <!-- ============================================== -->
            <div id="helpdesk-panel-sengketa" class="hidden space-y-4">
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-2 text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">⚖️</span>
                        <h4 class="font-black text-amber-900">Layanan Sengketa Kepemilikan Akun</h4>
                    </div>
                    <p class="text-amber-800 leading-relaxed">
                        Fasilitas resmi untuk mahasiswa yang mengalami pembajakan NIM, salah input data, atau penyalahgunaan akun oleh orang lain.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-3">
                    <h5 class="font-bold text-slate-800">Alur Penyelesaian Sengketa oleh Helpdesk:</h5>
                    <div class="space-y-2 text-slate-600 text-[11px]">
                        <div class="flex items-start space-x-2">
                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold flex-shrink-0 text-[10px]">1</span>
                            <span>Pelapor mengisi formulir sengketa resmi dengan menyertakan kontak aktif.</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold flex-shrink-0 text-[10px]">2</span>
                            <span>Petugas Helpdesk KPR (Komisi Pemilihan Raya) &amp; PanWas (Panitia Pengawas) memverifikasi identitas fisik / foto KTM asli pemilik sah.</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold flex-shrink-0 text-[10px]">3</span>
                            <span>Setelah disetujui 2 petugas berwenang, akun palsu dicabut dan pemilik sah diberikan kode pemulihan.</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('nim.sengketa.form') }}" 
                           class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-bold rounded-xl shadow-md transition flex items-center justify-center space-x-2 text-xs">
                            <span>Buka Formulir Laporan Sengketa Resmi</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Modal -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500 flex-shrink-0">
            <span class="flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Pusat Bantuan Resmi KPU-M Polbeng</span>
            </span>
            <button type="button" onclick="closeHelpdeskModal()" class="font-bold text-slate-600 hover:text-slate-900 transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- SCRIPT: HELPDESK CONTROLS & TAWK.TO FREE SCRIPT LOADER   -->
<!-- ======================================================== -->
<script>
    // Tab Switcher pada Modal Helpdesk
    function switchHelpdeskTab(tab) {
        const tabs = ['chat', 'wa', 'faq', 'sengketa'];
        tabs.forEach(t => {
            const btn = document.getElementById('helpdesk-tab-btn-' + t);
            const panel = document.getElementById('helpdesk-panel-' + t);

            if (t === tab) {
                if (btn) {
                    btn.classList.add('text-slate-900', 'bg-white', 'shadow-sm', 'border', 'border-slate-200/60');
                    btn.classList.remove('text-slate-600', 'hover:text-slate-900');
                }
                if (panel) panel.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.classList.remove('text-slate-900', 'bg-white', 'shadow-sm', 'border', 'border-slate-200/60');
                    btn.classList.add('text-slate-600', 'hover:text-slate-900');
                }
                if (panel) panel.classList.add('hidden');
            }
        });
    }

    // Buka Modal Helpdesk
    function openHelpdeskModal(defaultTab = 'chat') {
        const modal = document.getElementById('voterHelpdeskModal');
        if (modal) {
            modal.classList.remove('hidden');
            switchHelpdeskTab(defaultTab);
            document.body.style.overflow = 'hidden';
        }
    }

    // Tutup Modal Helpdesk
    function closeHelpdeskModal() {
        const modal = document.getElementById('voterHelpdeskModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Toggle FAQ Accordion Item
    function toggleFaqAccordion(ansId, iconId) {
        const ans = document.getElementById(ansId);
        const icon = document.getElementById(iconId);
        if (!ans) return;

        if (ans.classList.contains('hidden')) {
            ans.classList.remove('hidden');
            if (icon) icon.innerHTML = '&minus;';
        } else {
            ans.classList.add('hidden');
            if (icon) icon.innerHTML = '&plus;';
        }
    }

    // Kirim Pesan ke WhatsApp Panitia
    function submitWaHelpdesk(e) {
        e.preventDefault();
        const name = document.getElementById('wa_sender_name').value.trim();
        const nim = document.getElementById('wa_sender_nim').value.trim();
        const topic = document.getElementById('wa_issue_topic').value;
        const notes = document.getElementById('wa_issue_notes').value.trim();
        const waNumber = '{{ $cleanWaNumber }}';

        let message = `Halo Panitia Pemira Polbeng, saya memerlukan bantuan teknis:\n\n`;
        message += `👤 Nama: ${name}\n`;
        message += `🆔 NIM: ${nim}\n`;
        message += `📌 Topik Kendala: ${topic}\n`;
        if (notes) {
            message += `📝 Keterangan: ${notes}\n`;
        }
        message += `\nMohon bantuannya, terima kasih.`;

        const waUrl = `https://wa.me/${waNumber}?text=${encodeURIComponent(message)}`;
        window.open(waUrl, '_blank');
    }

    // Handler untuk membuka obrolan langsung Tawk.to
    function startTawkChat() {
        if (typeof Tawk_API !== 'undefined' && typeof Tawk_API.maximize === 'function') {
            closeHelpdeskModal();
            Tawk_API.maximize();
        } else {
            switchHelpdeskTab('wa');
        }
    }

    // Tutup modal jika user menekan tombol Escape atau klik area di luar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeHelpdeskModal();
        }
    });

    document.getElementById('voterHelpdeskModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeHelpdeskModal();
        }
    });

    // Otomatis buka jika ada hash #helpdesk atau parameter ?helpdesk=open di URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (window.location.hash === '#helpdesk' || urlParams.get('helpdesk') === 'open') {
            openHelpdeskModal('chat');
        }
    });
</script>

@if(!empty($tawkPropertyId))
    <!-- ======================================================== -->
    <!-- TAWK.TO 100% FREE LIVE CHAT ASYNC EMBED SCRIPT           -->
    <!-- ======================================================== -->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {};
        var Tawk_LoadStart = new Date();

        @if($isVoterLoggedIn && $currentVoter)
            // Teruskan identitas mahasiswa ke dasbor obrolan panitia
            Tawk_API.visitor = {
                name: @json($currentVoter->name),
                email: @json($currentVoter->email ?? '')
            };

            Tawk_API.onLoad = function(){
                Tawk_API.setAttributes({
                    'nim': @json($currentVoter->nim),
                    'prodi': @json($currentVoter->study_program ?? ''),
                    'angkatan': @json($currentVoter->angkatan ?? ''),
                    'fakultas': @json($currentVoter->faculty ?? '')
                }, function(error){});
            };
        @endif

        (function(){
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/{{ $tawkPropertyId }}/{{ $tawkWidgetId }}';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
@endif
