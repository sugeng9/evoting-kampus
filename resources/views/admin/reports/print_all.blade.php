<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Berita Acara & Laporan Rekapitulasi 10 Ormawa - KPU Polbeng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background-color: #f3f4f6;
        }

        .sans-font {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .double-border {
            border-top: 3px solid #000000;
            border-bottom: 1px solid #000000;
            height: 5px;
            margin: 8px 0 16px 0;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }
    </style>
</head>
<body class="py-8 px-4">

    <!-- Floating Top Bar (Disembunyikan saat mencetak) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-slate-900 text-white p-4 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 sans-font sticky top-4 z-50">
        <div class="flex items-center space-x-3">
            <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block">Buku Laporan Lengkap (10 Ormawa Polbeng)</span>
                <span class="text-sm font-black text-white">Cover + Ringkasan Eksekutif + 10 Berita Acara Resmi</span>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition">
                &larr; Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Buku Laporan Lengkap (PDF)</span>
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 1: COVER RESMI BUKU LAPORAN PEMIRA               -->
    <!-- ======================================================== -->
    <div class="page-container page-break max-w-4xl mx-auto bg-white p-12 rounded-3xl shadow-xl border border-slate-200 text-black leading-relaxed flex flex-col justify-between min-h-[950px] mb-8 text-center">
        
        <!-- Header Atas Cover -->
        <div class="space-y-1">
            <h3 class="text-xs uppercase tracking-widest font-bold">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
            <h2 class="text-base font-extrabold uppercase tracking-wide">POLITEKNIK NEGERI BENGKALIS</h2>
            <div class="w-32 h-1 bg-black mx-auto mt-2"></div>
        </div>

        <!-- Logo Tengah -->
        <div class="my-8 flex flex-col items-center justify-center space-y-3">
            <x-app-logo size="xl" variant="mark" theme="light" />
            <span class="text-xs font-mono font-bold tracking-widest text-slate-500 uppercase">KPU-M POLBENG &bull; SECRET BALLOT CRYPTOGRAPHIC ENGINE</span>
        </div>

        <!-- Judul Utama Dokumen -->
        <div class="space-y-3 my-6">
            <h1 class="text-xl sm:text-2xl font-black uppercase tracking-wider leading-snug">
                BUKU DOKUMEN REKAPITULASI &amp; BERITA ACARA<br>
                PENETAPAN HASIL TETAP PERHITUNGAN SUARA<br>
                PEMILIHAN RAYA MAHASISWA (PEMIRA)
            </h1>
            <h2 class="text-lg font-bold uppercase tracking-widest text-slate-800">
                TAHUN AKADEMIK {{ date('Y') }}/{{ date('Y')+1 }}
            </h2>
            <div class="border-t-2 border-b-2 border-black py-2 w-96 mx-auto mt-4 font-mono font-bold text-xs">
                NOMOR DOKUMEN INDUK: KPU-POLBENG/PEMIRA/{{ date('Y') }}/FULL-REP
            </div>
        </div>

        <!-- Daftar 10 Ormawa Polbeng -->
        <div class="max-w-xl mx-auto text-left text-xs bg-slate-50 p-5 rounded-2xl border border-slate-300 font-sans my-4">
            <div class="font-bold uppercase tracking-wider text-[11px] mb-2 text-center text-slate-900">
                Mencakup 10 Organisasi Mahasiswa Politeknik Negeri Bengkalis:
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[11px] text-slate-800">
                <div>1. BEM KM Polbeng (Presma)</div>
                <div>2. MPM KM Polbeng (Legislatif)</div>
                <div>3. HMM (Teknik Mesin)</div>
                <div>4. HIMKA (Teknik Perkapalan)</div>
                <div>5. HIMATRO (Teknik Elektro)</div>
                <div>6. HIMANIA (Administrasi Niaga)</div>
                <div>7. HMTI (Teknik Informatika)</div>
                <div>8. HMTS (Teknik Sipil)</div>
                <div>9. LEDSA (Bahasa)</div>
                <div>10. HTK (Kemaritiman)</div>
            </div>
        </div>

        <!-- Footer Penerbit Cover -->
        <div class="space-y-1 pt-6 text-xs">
            <h3 class="font-black uppercase tracking-wider text-sm">KOMISI PEMILIHAN UMUM MAHASISWA (KPU-M)</h3>
            <p class="font-bold">POLITEKNIK NEGERI BENGKALIS</p>
            <p class="text-[11px] text-slate-600">BENGKALIS - RIAU</p>
            <p class="font-mono text-[10px] text-slate-500 pt-2">Dicetak Resmi pada: {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</p>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 2: RINGKASAN EKSEKUTIF 10 ORMAWA TERPILIH       -->
    <!-- ======================================================== -->
    <div class="page-container page-break max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200 text-black leading-relaxed mb-8">
        
        <!-- Header Ringkasan -->
        <div class="text-center pb-4 border-b border-black">
            <h2 class="text-base font-black uppercase tracking-wider">RINGKASAN EKSEKUTIF HASIL PEMIRA 10 ORMAWA</h2>
            <h3 class="text-xs font-bold uppercase tracking-wide mt-1">PEMILIHAN RAYA MAHASISWA POLITEKNIK NEGERI BENGKALIS TAHUN {{ date('Y') }}</h3>
        </div>

        <div class="text-xs text-justify my-4 leading-relaxed">
            Berikut adalah rekapitulasi lengkap hasil perhitungan suara dan penetapan pasangan calon / calon ketua pada Sidang Pleno Terbuka Komisi Pemilihan Umum Mahasiswa Politeknik Negeri Bengkalis untuk 10 Organisasi Mahasiswa:
        </div>

        <!-- Tabel Ringkasan Eksekutif -->
        <table class="w-full border-collapse border border-slate-400 text-xs mb-6">
            <thead>
                <tr class="bg-slate-100 font-sans">
                    <th class="border border-slate-400 px-2 py-2 text-center w-8">No</th>
                    <th class="border border-slate-400 px-3 py-2 text-left">Organisasi Mahasiswa</th>
                    <th class="border border-slate-400 px-3 py-2 text-left">Kandidat Pemenang / Unggul</th>
                    <th class="border border-slate-400 px-2 py-2 text-center w-16">Paslon</th>
                    <th class="border border-slate-400 px-2 py-2 text-right w-20">Suara Sah</th>
                    <th class="border border-slate-400 px-2 py-2 text-right w-16">% Suara</th>
                    <th class="border border-slate-400 px-2 py-2 text-center w-24">Status Hasil</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $rep)
                    @php
                        $cat = $rep['category'];
                        $winner = $rep['winner'];
                        $isEnded = $rep['is_ended'];
                    @endphp
                    <tr>
                        <td class="border border-slate-400 px-2 py-2 text-center font-mono font-bold">{{ $cat->display_order }}</td>
                        <td class="border border-slate-400 px-3 py-2">
                            <strong class="block text-slate-900">{{ $cat->name }}</strong>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $cat->code }}</span>
                        </td>
                        <td class="border border-slate-400 px-3 py-2">
                            @if($winner)
                                <div class="font-extrabold text-slate-900">
                                    {{ $winner['leader_name'] }}
                                    @if($isEnded)
                                        <span class="text-emerald-700 text-[10px]">👑</span>
                                    @endif
                                </div>
                                @if($winner['vice_leader_name'])
                                    <div class="text-[10px] text-slate-600">&amp; {{ $winner['vice_leader_name'] }}</div>
                                @endif
                            @else
                                <span class="text-slate-400 italic">Belum Ada Suara Sah</span>
                            @endif
                        </td>
                        <td class="border border-slate-400 px-2 py-2 text-center font-mono font-bold">
                            {{ $winner ? '0' . $winner['number'] : '-' }}
                        </td>
                        <td class="border border-slate-400 px-2 py-2 text-right font-mono font-bold">
                            {{ $winner ? number_format($winner['votes'] ?? 0) : '-' }}
                        </td>
                        <td class="border border-slate-400 px-2 py-2 text-right font-mono font-bold">
                            @if($winner && $rep['total_ballots'] > 0)
                                {{ $winner['percentage'] }}%
                            @else
                                -
                            @endif
                        </td>
                        <td class="border border-slate-400 px-2 py-2 text-center font-sans text-[10px]">
                            @if($isEnded)
                                <span class="inline-block px-1.5 py-0.5 rounded font-black text-emerald-900 bg-emerald-100 border border-emerald-300">
                                    HASIL TETAP
                                </span>
                            @else
                                <span class="inline-block px-1.5 py-0.5 rounded font-bold text-amber-900 bg-amber-100 border border-amber-300">
                                    SEMENTARA
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Catatan Keabsahan Pleno -->
        <div class="border border-slate-300 p-3 rounded-lg text-[11px] leading-relaxed mb-6 bg-slate-50">
            <strong>Catatan Keabsahan KPU-M Polbeng:</strong> Seluruh perolehan suara sah di atas dihitung melalui basis data kriptografis anonim yang tidak merekam identitas NIM pemilih pada kotak suara digital (Secret Ballot Engine). Hasil pemilihan yang telah melewati batas waktu resmi berstatus <strong>HASIL TETAP RESMI</strong> dan mengikat secara sah.
        </div>

        <!-- Tanda Tangan Ringkasan Eksekutif -->
        <div class="pt-2 text-xs font-sans">
            <div class="text-right mb-4">
                Bengkalis, {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
            </div>

            <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                    <span class="block font-bold">Ketua KPU Mahasiswa Polbeng</span>
                    <div class="h-16 flex items-end justify-center">
                        <span class="text-[10px] text-slate-400 italic">[ Cap Stempel &amp; Tanda Tangan ]</span>
                    </div>
                    <div class="border-b border-black w-48 mx-auto mt-2"></div>
                    <span class="block text-xs font-black mt-1">Danang Prakoso</span>
                    <span class="block text-[10px] font-mono text-slate-600">NIM: 120120001</span>
                </div>

                <div>
                    <span class="block font-bold">Sekretaris KPU Mahasiswa Polbeng</span>
                    <div class="h-16 flex items-end justify-center">
                        <span class="text-[10px] text-slate-400 italic">[ Tanda Tangan ]</span>
                    </div>
                    <div class="border-b border-black w-48 mx-auto mt-2"></div>
                    <span class="block text-xs font-black mt-1">Rizky Alamsyah</span>
                    <span class="block text-[10px] font-mono text-slate-600">NIM: 120140025</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 3 S/D 12: BERITA ACARA MASING-MASING ORMAWA     -->
    <!-- ======================================================== -->
    @foreach($reports as $index => $report)
        @php
            $isEnded = $report['is_ended'];
        @endphp
        <div class="page-container page-break max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200 text-black leading-relaxed mb-8">
            
            <!-- KOP SURAT RESMI -->
            <div class="flex items-center justify-between gap-4 pb-2">
                <!-- Logo Polbeng / Lambang Institusi -->
                <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <div class="w-14 h-14 rounded-xl border border-slate-700/40 bg-slate-50 flex flex-col items-center justify-center font-bold text-slate-800 text-center leading-tight text-[9px] p-1 shadow-sm">
                        <span class="text-[10px] font-black">POLBENG</span>
                        <span class="text-[7px] text-slate-500 uppercase tracking-tighter">Bengkalis</span>
                    </div>
                </div>

                <div class="text-center flex-1">
                    <h3 class="text-[11px] uppercase tracking-widest font-bold">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</h3>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide mt-0.5">Politeknik Negeri Bengkalis</h2>
                    <h1 class="text-base font-black uppercase tracking-wider mt-0.5">Komisi Pemilihan Umum Mahasiswa (KPU-M)</h1>
                    <p class="text-[9px] mt-0.5 text-slate-700">
                        Jalan Bathin Alam, Sungai Alam, Bengkalis, Riau 28711 &bull; Pos-el: <em>kpu@polbeng.ac.id</em>
                    </p>
                </div>

                <!-- Logo KPU-M Handcrafted Vector Emblem -->
                <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <x-app-logo size="md" variant="mark" theme="light" />
                </div>
            </div>

            <div class="double-border"></div>

            <!-- Banner Jika Sesi Belum Habis -->
            @if(!$isEnded)
                <div class="border border-dashed border-amber-600 bg-amber-50 p-2 rounded-lg text-center text-[11px] font-bold text-amber-900 mb-3">
                    DRAFT REKAPITULASI SEMENTARA &bull; Pemungutan suara masih berlangsung sampai {{ $report['end_time_formatted'] }}.
                </div>
            @endif

            <!-- JUDUL BERITA ACARA -->
            <div class="text-center my-4 space-y-0.5">
                <h2 class="text-sm font-black uppercase tracking-wider underline">
                    @if($isEnded)
                        BERITA ACARA &amp; SERTIFIKAT PENETAPAN HASIL TETAP
                    @else
                        DRAFT REKAPITULASI SEMENTARA PERHITUNGAN SUARA
                    @endif
                </h2>
                <h3 class="text-xs font-bold uppercase tracking-wide">
                    PEMILIHAN RAYA MAHASISWA POLITEKNIK NEGERI BENGKALIS TAHUN {{ date('Y') }}
                </h3>
                <p class="text-[11px] font-mono font-bold mt-1">
                    Nomor: {{ $report['ba_number'] }}
                </p>
            </div>

            <!-- DETAIL KATEGORI -->
            <div class="bg-slate-50 border border-slate-300 p-2.5 rounded-lg text-[11px] mb-4">
                <table class="w-full">
                    <tr>
                        <td class="w-32 font-bold py-0.5">Nama Pemilihan</td>
                        <td class="w-3">:</td>
                        <td class="font-extrabold uppercase">{{ $report['category']->name }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-0.5">Klasifikasi</td>
                        <td>:</td>
                        <td>{{ $report['pillar_badge']['title'] }} ({{ $report['category']->study_program_filter ? 'Jurusan: ' . $report['category']->study_program_filter : 'Terbuka Seluruh Mahasiswa Polbeng' }})</td>
                    </tr>
                    <tr>
                        <td class="font-bold py-0.5">Status Waktu</td>
                        <td>:</td>
                        <td class="font-bold {{ $isEnded ? 'text-emerald-800' : 'text-amber-800' }}">
                            {{ $isEnded ? 'Waktu Pemilihan Telah Berakhir (' . $report['end_time_formatted'] . ')' : 'Sedang Berlangsung (Batas: ' . $report['end_time_formatted'] . ')' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- BAGIAN 1: DATA PEMILIH -->
            <div class="mb-4">
                <h4 class="text-[11px] font-bold uppercase tracking-wider mb-1.5 font-sans">
                    I. DATA PEMILIH DAN PENGGUNAAN HAK SUARA
                </h4>
                <table class="w-full border-collapse border border-slate-400 text-[11px]">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-400 px-2 py-1 text-center w-10">No</th>
                            <th class="border border-slate-400 px-3 py-1 text-left">Uraian Data Partisipasi</th>
                            <th class="border border-slate-400 px-3 py-1 text-right w-32">Jumlah</th>
                            <th class="border border-slate-400 px-3 py-1 text-right w-24">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-slate-400 px-2 py-0.5 text-center font-mono">1</td>
                            <td class="border border-slate-400 px-3 py-0.5">Total DPT Sah yang Berhak Memilih</td>
                            <td class="border border-slate-400 px-3 py-0.5 text-right font-mono font-bold">{{ number_format($report['category_dpt']) }}</td>
                            <td class="border border-slate-400 px-3 py-0.5 text-right font-mono">100.0%</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-400 px-2 py-0.5 text-center font-mono">2</td>
                            <td class="border border-slate-400 px-3 py-0.5 font-semibold">Jumlah Suara Masuk Sah (Terenkripsi)</td>
                            <td class="border border-slate-400 px-3 py-0.5 text-right font-mono font-bold">{{ number_format($report['total_ballots']) }}</td>
                            <td class="border border-slate-400 px-3 py-0.5 text-right font-mono font-bold">{{ $report['turnout_percentage'] }}%</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-400 px-2 py-0.5 text-center font-mono">3</td>
                            <td class="border border-slate-400 px-3 py-0.5">Jumlah Tidak Memilih (Golput)</td>
                            <td class="border border-slate-400 px-3 py-0.5 text-right font-mono font-bold">{{ number_format($report['not_voted_count']) }}</td>
                            <td class="border border-slate-400 px-3 py-0.5 text-right font-mono">{{ $report['not_voted_percentage'] }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BAGIAN 2: RINCIAN SUARA PASLON -->
            <div class="mb-4">
                <h4 class="text-[11px] font-bold uppercase tracking-wider mb-1.5 font-sans">
                    II. RINCIAN PEROLEHAN SUARA SAH PASANGAN CALON / KANDIDAT
                </h4>
                <table class="w-full border-collapse border border-slate-400 text-[11px]">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-400 px-2 py-1 text-center w-12">No. Urut</th>
                            <th class="border border-slate-400 px-3 py-1 text-left">Nama Pasangan Calon / Kandidat</th>
                            <th class="border border-slate-400 px-2 py-1 text-center w-28">NIM</th>
                            <th class="border border-slate-400 px-2 py-1 text-right w-24">Suara Sah</th>
                            <th class="border border-slate-400 px-2 py-1 text-right w-20">% Suara</th>
                            <th class="border border-slate-400 px-2 py-1 text-center w-28">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['candidates'] as $c)
                            @php
                                $isTop = $report['winner'] && $report['winner']['id'] === $c['id'];
                            @endphp
                            <tr class="{{ $isTop ? ($isEnded ? 'bg-emerald-50/50 font-bold' : 'bg-amber-50/50 font-bold') : '' }}">
                                <td class="border border-slate-400 px-2 py-1 text-center font-mono font-bold">0{{ $c['number'] }}</td>
                                <td class="border border-slate-400 px-3 py-1">
                                    <div class="font-extrabold">{{ $c['leader_name'] }}</div>
                                    @if($c['vice_leader_name'])
                                        <div class="text-[10px] text-slate-700">Wakil: {{ $c['vice_leader_name'] }}</div>
                                    @endif
                                </td>
                                <td class="border border-slate-400 px-2 py-1 text-center font-mono text-[10px]">
                                    {{ $c['leader_nim'] ?: '-' }}
                                </td>
                                <td class="border border-slate-400 px-2 py-1 text-right font-mono font-bold">
                                    {{ number_format($c['votes']) }}
                                </td>
                                <td class="border border-slate-400 px-2 py-1 text-right font-mono font-bold">
                                    {{ $c['percentage'] }}%
                                </td>
                                <td class="border border-slate-400 px-2 py-1 text-center text-[10px]">
                                    @if($isTop)
                                        @if($isEnded)
                                            <span class="font-black text-emerald-900 uppercase">★ TERPILIH</span>
                                        @else
                                            <span class="font-bold text-amber-900 uppercase">UNGGUL SEMENTARA</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- BAGIAN 3: KEPUTUSAN PENETAPAN -->
            <div class="border border-black p-3 rounded-lg mb-4 bg-slate-50 text-[11px]">
                <h4 class="font-black uppercase tracking-wider text-center underline mb-1">
                    @if($isEnded)
                        PENETAPAN PEMENANG RESMI PLENO KPU MAHASISWA
                    @else
                        STATUS PEROLEHAN SUARA SEMENTARA
                    @endif
                </h4>
                @if($isEnded && $report['winner'])
                    <div class="text-center py-1">
                        MENETAPKAN: Paslon Nomor Urut <strong>0{{ $report['winner']['number'] }}</strong> atas nama 
                        <strong>{{ $report['winner']['leader_name'] }}</strong>
                        @if($report['winner']['vice_leader_name'])
                            &amp; <strong>{{ $report['winner']['vice_leader_name'] }}</strong>
                        @endif
                        sebagai <strong>{{ $report['category']->getPillarType() === 'BEM' ? 'Presiden & Wakil Presiden Mahasiswa' : ($report['category']->getPillarType() === 'MPM' ? 'Ketua Umum MPM' : 'Ketua Himpunan') }} Terpilih</strong> Periode 2026/2027 Berdasarkan Hasil Tetap Perhitungan Suara.
                    </div>
                @elseif(!$isEnded)
                    <div class="text-center text-amber-900 py-1">
                        Pemungutan suara masih berlangsung sampai <strong>{{ $report['end_time_formatted'] }}</strong>.
                        Kandidat Paslon <strong>0{{ $report['winner']['number'] ?? '-' }}</strong> saat ini unggul sementara. Penetapan sah akan disahkan setelah waktu pemilihan habis.
                    </div>
                @else
                    <div class="text-center text-slate-500 italic py-1">
                        Belum ada perolehan suara definitif untuk penetapan pemenang kategori ini.
                    </div>
                @endif
            </div>

            <!-- Hash Dokumen -->
            <div class="p-2 rounded border border-slate-300 text-[9px] font-mono text-slate-500 mb-6 flex justify-between">
                <span><strong>SHA-256 Hash:</strong> {{ $report['document_checksum'] }}</span>
                <span>Waktu Cetak: {{ $report['generated_at'] }}</span>
            </div>

            <!-- Tanda Tangan Panitia & Saksi -->
            <div class="text-[11px] font-sans">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <span class="block font-medium">Saksi Paslon 01</span>
                        <div class="h-12 flex items-end justify-center">
                            <span class="text-[9px] text-slate-400 italic">(Tanda Tangan)</span>
                        </div>
                        <div class="border-b border-black w-36 mx-auto mt-1"></div>
                    </div>

                    <div>
                        <span class="block font-medium">Saksi Paslon 02</span>
                        <div class="h-12 flex items-end justify-center">
                            <span class="text-[9px] text-slate-400 italic">(Tanda Tangan)</span>
                        </div>
                        <div class="border-b border-black w-36 mx-auto mt-1"></div>
                    </div>

                    <div>
                        <span class="block font-bold">Ketua KPU Mahasiswa</span>
                        <div class="h-12 flex items-end justify-center">
                            <span class="text-[9px] text-slate-400 italic">(Cap &amp; Ttd)</span>
                        </div>
                        <div class="border-b border-black w-36 mx-auto mt-1"></div>
                        <span class="block font-bold mt-0.5">Danang Prakoso</span>
                    </div>
                </div>
            </div>

        </div>
    @endforeach

</body>
</html>
