<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'full', // 'full', 'mark', 'compact'
    'size' => 'md',      // 'xs', 'sm', 'md', 'lg', 'xl'
    'theme' => 'dark',   // 'dark', 'light'
    'title' => 'PEMIRA POLBENG',
    'subtitle' => 'Komisi Pemilihan Umum Mahasiswa'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'variant' => 'full', // 'full', 'mark', 'compact'
    'size' => 'md',      // 'xs', 'sm', 'md', 'lg', 'xl'
    'theme' => 'dark',   // 'dark', 'light'
    'title' => 'PEMIRA POLBENG',
    'subtitle' => 'Komisi Pemilihan Umum Mahasiswa'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $dimensions = match($size) {
        'xs' => ['box' => 'w-7 h-7', 'svg' => 'w-5 h-5', 'title' => 'text-xs', 'sub' => 'text-[9px]'],
        'sm' => ['box' => 'w-9 h-9', 'svg' => 'w-6 h-6', 'title' => 'text-sm', 'sub' => 'text-[10px]'],
        'md' => ['box' => 'w-11 h-11 sm:w-12 sm:h-12', 'svg' => 'w-7 h-7 sm:w-8 sm:h-8', 'title' => 'text-base sm:text-lg', 'sub' => 'text-[11px]'],
        'lg' => ['box' => 'w-14 h-14 sm:w-16 sm:h-16', 'svg' => 'w-9 h-9 sm:w-10 sm:h-10', 'title' => 'text-xl sm:text-2xl', 'sub' => 'text-xs sm:text-sm'],
        'xl' => ['box' => 'w-20 h-20 sm:w-24 sm:h-24', 'svg' => 'w-12 h-12 sm:w-14 sm:h-14', 'title' => 'text-2xl sm:text-3xl', 'sub' => 'text-sm sm:text-base'],
        default => ['box' => 'w-11 h-11', 'svg' => 'w-7 h-7', 'title' => 'text-base', 'sub' => 'text-[11px]'],
    };

    $titleColor = $theme === 'light' ? 'text-slate-900' : 'text-white';
    $subtitleColor = $theme === 'light' ? 'text-emerald-700' : 'text-emerald-400';
?>

<div class="inline-flex items-center space-x-3 select-none">
    <!-- EMBLEM / SEAL LOGO ICON (HANDCRAFTED VECTOR ART) -->
    <div class="<?php echo e($dimensions['box']); ?> relative flex items-center justify-center flex-shrink-0 group">
        <!-- Ambient Glow Effect -->
        <div class="absolute -inset-1 bg-gradient-to-tr from-emerald-500/40 via-teal-400/30 to-amber-400/20 rounded-2xl blur-md group-hover:blur-lg transition duration-300 opacity-80 pointer-events-none"></div>

        <!-- Shield Container -->
        <div class="relative w-full h-full rounded-2xl bg-gradient-to-br from-navy-900 via-slate-900 to-emerald-950 p-[1.5px] shadow-xl border border-white/10 overflow-hidden flex items-center justify-center">
            
            <!-- Subtle Radial Gradient Background -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-500/20 via-transparent to-transparent"></div>

            <!-- Master Vector Art Emblem -->
            <svg class="<?php echo e($dimensions['svg']); ?> relative z-10 filter drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <!-- Gradien Warna Utama Logo -->
                    <linearGradient id="pemiShieldGrad" x1="4" y1="4" x2="44" y2="44" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#059669"/>
                        <stop offset="50%" stop-color="#10B981"/>
                        <stop offset="100%" stop-color="#06B6D4"/>
                    </linearGradient>

                    <linearGradient id="pemiGoldGrad" x1="16" y1="8" x2="32" y2="28" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#FDE047"/>
                        <stop offset="50%" stop-color="#F59E0B"/>
                        <stop offset="100%" stop-color="#D97706"/>
                    </linearGradient>

                    <linearGradient id="pemiPaperGrad" x1="18" y1="6" x2="30" y2="22" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#FFFFFF"/>
                        <stop offset="100%" stop-color="#D1FAE5"/>
                    </linearGradient>

                    <filter id="pemiGlow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="1" stdDeviation="1.5" flood-color="#10B981" flood-opacity="0.4"/>
                    </filter>
                </defs>

                <!-- 1. Perisai Geometris Luar (Shield of Integrity) -->
                <path d="M24 3L40 9V22C40 32.5 33.2 41.8 24 45C14.8 41.8 8 32.5 8 22V9L24 3Z" 
                      fill="url(#pemiShieldGrad)" 
                      fill-opacity="0.15" 
                      stroke="url(#pemiShieldGrad)" 
                      stroke-width="1.8" 
                      stroke-linejoin="round"/>

                <!-- 2. Sayap Laurel Demokrasi Minimalis (Kiri & Kanan) -->
                <!-- Daun Kiri -->
                <path d="M13 18C13 18 15 21 15 25C15 29 13 32 13 32C13 32 17 31 18 27C19 23 18 19 18 19" 
                      fill="url(#pemiGoldGrad)" 
                      opacity="0.9"/>
                <circle cx="14" cy="15" r="1.3" fill="url(#pemiGoldGrad)"/>
                <circle cx="12" cy="22" r="1.2" fill="url(#pemiGoldGrad)"/>
                <circle cx="13" cy="29" r="1.2" fill="url(#pemiGoldGrad)"/>

                <!-- Daun Kanan -->
                <path d="M35 18C35 18 33 21 33 25C33 29 35 32 35 32C35 32 31 31 30 27C29 23 30 19 30 19" 
                      fill="url(#pemiGoldGrad)" 
                      opacity="0.9"/>
                <circle cx="34" cy="15" r="1.3" fill="url(#pemiGoldGrad)"/>
                <circle cx="36" cy="22" r="1.2" fill="url(#pemiGoldGrad)"/>
                <circle cx="35" cy="29" r="1.2" fill="url(#pemiGoldGrad)"/>

                <!-- 3. Kotak Suara Digital (Ballot Box 3D Isometric) -->
                <!-- Badan Kotak Suara -->
                <rect x="16" y="22" width="16" height="15" rx="3.5" 
                      fill="#0F172A" 
                      stroke="url(#pemiShieldGrad)" 
                      stroke-width="1.8"/>

                <!-- Aksen Garis & Kunci Gembok Digital pada Kotak -->
                <line x1="16" y1="27" x2="32" y2="27" stroke="url(#pemiShieldGrad)" stroke-width="1" opacity="0.6"/>
                <circle cx="24" cy="32" r="1.6" fill="url(#pemiGoldGrad)"/>
                <path d="M24 33.6V35" stroke="url(#pemiGoldGrad)" stroke-width="1.2" stroke-linecap="round"/>

                <!-- 4. Surat Suara Masuk (Ballot Cast with Checkmark) -->
                <!-- Kertas Suara Melayang Masuk Celah -->
                <path d="M19 10C19 8.89543 19.8954 8 21 8H27C28.1046 8 29 8.89543 29 10V22H19V10Z" 
                      fill="url(#pemiPaperGrad)" 
                      stroke="#FFFFFF" 
                      stroke-width="1" 
                      filter="url(#pemiGlow)"/>

                <!-- Cap Centang Emas Kedaulatan Mahasiswa -->
                <path d="M21.5 14L23.5 16.2L26.8 12.5" 
                      stroke="url(#pemiGoldGrad)" 
                      stroke-width="1.8" 
                      stroke-linecap="round" 
                      stroke-linejoin="round"/>

                <!-- Celah Kotak Suara (Slot) -->
                <rect x="18" y="21" width="12" height="2" rx="1" fill="#020617" stroke="url(#pemiShieldGrad)" stroke-width="0.8"/>

                <!-- 5. Bintang Kejujuran & Transparansi 4-Sumbu di Puncak (Star of Truth) -->
                <path d="M24 2.5L25 5L27.5 6L25 7L24 9.5L23 7L20.5 6L23 5L24 2.5Z" 
                      fill="url(#pemiGoldGrad)"/>
            </svg>
        </div>
    </div>

    <!-- TYPOGRAPHY / LOGO TEXT -->
    <?php if($variant === 'full'): ?>
        <div class="flex flex-col min-w-0">
            <span class="font-heading font-black <?php echo e($dimensions['title']); ?> <?php echo e($titleColor); ?> tracking-tight leading-none uppercase truncate">
                <?php echo e($title); ?>

            </span>
            <span class="font-sans font-bold <?php echo e($dimensions['sub']); ?> <?php echo e($subtitleColor); ?> tracking-wider uppercase mt-1 truncate">
                <?php echo $subtitle; ?>

            </span>
        </div>
    <?php elseif($variant === 'compact'): ?>
        <div class="flex flex-col min-w-0">
            <span class="font-heading font-black <?php echo e($dimensions['title']); ?> <?php echo e($titleColor); ?> tracking-tight leading-tight truncate">
                <?php echo e($title); ?>

            </span>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/components/app-logo.blade.php ENDPATH**/ ?>