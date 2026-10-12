<!-- ======================================================== -->
<!-- KOMPONEN PENGELOLAAN COOKIE & PRIVASI (UU PDP No. 27/2022) -->
<!-- ======================================================== -->

<!-- 1. FLOATING COOKIE CONSENT BANNER (Muncul jika belum ada keputusan) -->
<div id="cookieConsentBanner" 
     class="hidden fixed bottom-4 left-4 right-4 sm:left-6 sm:right-auto sm:max-w-lg z-50 transition-all duration-500 transform translate-y-8 opacity-0">
    <div class="bg-navy-950/95 text-white p-5 rounded-2xl shadow-2xl border border-slate-700/80 backdrop-blur-md">
        <div class="flex items-start space-x-3.5">
            <!-- Icon Cookie & Security -->
            <div class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex-shrink-0 mt-0.5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                    <h3 class="text-sm font-bold text-white tracking-tight">Privasi &amp; Pengelolaan Cookie</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        UU PDP 2022
                    </span>
                </div>
                <p class="mt-1.5 text-xs text-slate-300 leading-relaxed">
                    Portal Pemira Politeknik Negeri Bengkalis hanya menggunakan <strong class="text-white">Cookie Esensial</strong> untuk autentikasi bilik suara, keamanan sesi CSRF, dan perlindungan anti-bot. Kami <strong class="text-emerald-300">tidak menggunakan cookie pelacak iklan komersial</strong> atau menjual data Anda.
                </p>

                <!-- Actions -->
                <div class="mt-3.5 flex flex-wrap items-center gap-2">
                    <button type="button" 
                            onclick="acceptAllCookies()" 
                            class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition shadow-sm shadow-emerald-900/30 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        Setujui Semua
                    </button>
                    <button type="button" 
                            onclick="acceptEssentialOnly()" 
                            class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-slate-500">
                        Hanya Esensial
                    </button>
                    <button type="button" 
                            onclick="openCookiePreferences()" 
                            class="text-xs text-slate-400 hover:text-white underline underline-offset-4 ml-1 transition focus:outline-none">
                        Atur Preferensi
                    </button>
                </div>
            </div>

            <!-- Dismiss / Close button -->
            <button type="button" 
                    onclick="acceptEssentialOnly()" 
                    title="Tutup &amp; Gunakan Esensial"
                    class="text-slate-400 hover:text-white p-1 rounded-lg transition focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- 2. MODAL PUSAT PREFERENSI & TRANSPARANSI COOKIE -->
<div id="cookiePreferencesModal" 
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm transition-opacity duration-300" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="cookieModalTitle">
    
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Modal -->
        <div class="bg-gradient-to-r from-navy-950 via-slate-900 to-navy-900 text-white p-5 sm:p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2.5 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="cookieModalTitle" class="text-base sm:text-lg font-heading font-black tracking-tight text-white">
                        Pusat Privasi &amp; Kebijakan Cookie
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Kepatuhan Standar UU PDP No. 27 Tahun 2022 &bull; Komisi Pemilihan Raya
                    </p>
                </div>
            </div>
            <button type="button" 
                    onclick="closeCookiePreferences()" 
                    class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition focus:outline-none"
                    aria-label="Tutup Dialog">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Body Scrollable -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5 text-sm text-slate-700 divide-y divide-slate-100">
            
            <!-- Penjelasan Pembuka -->
            <div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Kami menghormati hak privasi dan kerahasiaan suara seluruh sivitas akademika Politeknik Negeri Bengkalis. Transparansi di bawah ini merinci seluruh informasi teknis yang disimpan di peramban (browser) Anda.
                </p>
            </div>

            <!-- Kategori 1: Cookie Esensial Keamanan (Wajib) -->
            <div class="pt-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-900 text-sm">1. Cookie Esensial &amp; Keamanan Bilik Suara</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Wajib (Selalu Aktif)
                        </span>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">Terkunci</span>
                </div>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    Cookie ini mutlak diperlukan agar Anda dapat masuk ke bilik suara, melakukan pencoblosan yang sah, dan mencegah pemalsuan permintaan (CSRF) serta serangan siber.
                </p>

                <!-- Daftar Cookie Teknis -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-2 text-xs">
                    <div class="flex items-start justify-between border-b border-slate-200/80 pb-2">
                        <div>
                            <span class="font-mono font-bold text-slate-800">e_voting_kampus_session</span>
                            <p class="text-[11px] text-slate-500">Menyimpan identifikasi sesi terenkripsi saat pemilih aktif di bilik suara.</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono bg-white px-2 py-0.5 rounded border border-slate-200">15 Menit Idle / Sesi</span>
                    </div>

                    <div class="flex items-start justify-between border-b border-slate-200/80 pb-2">
                        <div>
                            <span class="font-mono font-bold text-slate-800">XSRF-TOKEN</span>
                            <p class="text-[11px] text-slate-500">Mencegah serangan Cross-Site Request Forgery pada formulir coblos suara.</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono bg-white px-2 py-0.5 rounded border border-slate-200">Sesi Browser</span>
                    </div>

                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono font-bold text-slate-800">cf_turnstile / cf_clearance</span>
                            <p class="text-[11px] text-slate-500">Verifikasi anti-bot Cloudflare berbasis privasi tanpa melacak riwayat penjelajahan.</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono bg-white px-2 py-0.5 rounded border border-slate-200">1 Jam / Sesi</span>
                    </div>
                </div>
            </div>

            <!-- Kategori 2: Preferensi Pengguna (Opsional) -->
            <div class="pt-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-900 text-sm">2. Preferensi Antarmuka Pengguna</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200">
                            Opsional
                        </span>
                    </div>
                    <!-- Toggle switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="prefPreferencesToggle" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-2">
                    Mengingat pilihan konfirmasi privasi Anda dan status tampilan widget bantuan helpdesk di perangkat ini agar Anda tidak perlu mengaturnya kembali setiap membuka halaman.
                </p>
                <div class="text-[11px] font-mono text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 inline-block">
                    Penyimpanan Lokal: <strong class="text-slate-700">localStorage (evoting_cookie_consent_v1)</strong>
                </div>
            </div>

            <!-- Kategori 3: Iklan & Pelacak Pihak Ketiga (Dinonaktifkan Permanen) -->
            <div class="pt-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-slate-900 text-sm">3. Pelacak Pihak Ketiga &amp; Komersial</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                            Tidak Digunakan (0 Cookie)
                        </span>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">Nonaktif</span>
                </div>
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-emerald-800 leading-relaxed">
                        <strong>Jaminan KPR (Komisi Pemilihan Raya) Politeknik Negeri Bengkalis:</strong> Portal ini beroperasi murni untuk kepentingan akademik dan demokrasi kampus. Tidak ada Google Ads, Meta Pixel, TikTok Tracker, atau pelacak monetisasi apapun yang dipasang di sistem ini.
                    </p>
                </div>
            </div>

        </div>

        <!-- Footer Modal Buttons -->
        <div class="bg-slate-50 border-t border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-end gap-2.5">
            <button type="button" 
                    onclick="acceptEssentialOnly()" 
                    class="w-full sm:w-auto px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition focus:outline-none">
                Gunakan Hanya Esensial
            </button>
            <button type="button" 
                    onclick="saveCustomPreferences()" 
                    class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition focus:outline-none">
                Simpan Preferensi
            </button>
            <button type="button" 
                    onclick="acceptAllCookies()" 
                    class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm focus:outline-none">
                Setujui Semua
            </button>
        </div>

    </div>
</div>

<!-- 3. JAVASCRIPT CONTROLLER KELOLA COOKIE -->
<script>
    const COOKIE_CONSENT_KEY = 'evoting_cookie_consent_v1';

    function getCookieConsent() {
        try {
            const raw = localStorage.getItem(COOKIE_CONSENT_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function setCookieConsent(preferences) {
        try {
            const data = {
                necessary: true,
                preferences: !!preferences.preferences,
                status: preferences.status || 'custom',
                updated_at: new Date().toISOString()
            };
            localStorage.setItem(COOKIE_CONSENT_KEY, JSON.stringify(data));
        } catch (e) {
            console.warn('LocalStorage tidak dapat diakses untuk cookie consent:', e);
        }
    }

    function showBanner() {
        const banner = document.getElementById('cookieConsentBanner');
        if (!banner) return;
        banner.classList.remove('hidden');
        setTimeout(() => {
            banner.classList.remove('translate-y-8', 'opacity-0');
            banner.classList.add('translate-y-0', 'opacity-100');
        }, 50);
    }

    function hideBanner() {
        const banner = document.getElementById('cookieConsentBanner');
        if (!banner) return;
        banner.classList.add('translate-y-8', 'opacity-0');
        banner.classList.remove('translate-y-0', 'opacity-100');
        setTimeout(() => {
            banner.classList.add('hidden');
        }, 500);
    }

    function openCookiePreferences() {
        const modal = document.getElementById('cookiePreferencesModal');
        const prefToggle = document.getElementById('prefPreferencesToggle');
        const consent = getCookieConsent();

        if (prefToggle) {
            prefToggle.checked = consent ? (consent.preferences !== false) : true;
        }

        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeCookiePreferences() {
        const modal = document.getElementById('cookiePreferencesModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function acceptAllCookies() {
        setCookieConsent({ preferences: true, status: 'all' });
        hideBanner();
        closeCookiePreferences();
        showConsentFeedback('Semua cookie keamanan dan preferensi disetujui.');
    }

    function acceptEssentialOnly() {
        setCookieConsent({ preferences: false, status: 'essential_only' });
        hideBanner();
        closeCookiePreferences();
        showConsentFeedback('Hanya cookie esensial keamanan yang aktif.');
    }

    function saveCustomPreferences() {
        const prefToggle = document.getElementById('prefPreferencesToggle');
        const isPrefChecked = prefToggle ? prefToggle.checked : true;
        setCookieConsent({ preferences: isPrefChecked, status: 'custom' });
        hideBanner();
        closeCookiePreferences();
        showConsentFeedback('Preferensi cookie berhasil disimpan.');
    }

    function showConsentFeedback(msg) {
        // Tampilkan pesan konfirmasi singkat (toast) yang elegan
        let toast = document.getElementById('cookieToastFeedback');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'cookieToastFeedback';
            toast.className = 'fixed bottom-4 right-4 z-50 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-semibold shadow-xl border border-emerald-500/40 flex items-center space-x-2 transition-all duration-300 transform translate-y-6 opacity-0';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400"></span><span>${msg}</span>`;
        toast.classList.remove('translate-y-6', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        setTimeout(() => {
            toast.classList.add('translate-y-6', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 3000);
    }

    // Inisialisasi saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function () {
        const consent = getCookieConsent();
        if (!consent) {
            // Berikan jeda sejenak (800ms) agar halaman utama render mulus terlebih dahulu
            setTimeout(showBanner, 800);
        }
    });

    // Ekspos ke global window agar link di footer bisa memanggil langsung
    window.openCookiePreferences = openCookiePreferences;
    window.acceptAllCookies = acceptAllCookies;
    window.acceptEssentialOnly = acceptEssentialOnly;
    window.saveCustomPreferences = saveCustomPreferences;
</script>
