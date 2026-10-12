<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report['is_ended'] ? 'Berita Acara Penetapan Hasil Tetap' : 'Draft Rekapitulasi Sementara' }} - {{ $report['category']->name }}</title>
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
                margin: 0 auto !important;
                max-width: 100% !important;
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
    </style>
</head>
<body class="py-8 px-4">

    <!-- Floating Top Bar (Disembunyikan saat mencetak) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-slate-900 text-white p-4 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 sans-font">
        <div class="flex items-center space-x-3">
            @if($report['is_ended'])
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block">🔒 HASIL TETAP RESMI (WAKTU HABIS)</span>
                    <span class="text-sm font-black text-white">{{ $report['category']->name }}</span>
                </div>
            @else
                <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping"></span>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-400 block">⏳ DRAFT SEMENTARA (BERJALAN HINGGA: {{ $report['end_time_formatted'] }})</span>
                    <span class="text-sm font-black text-white">{{ $report['category']->name }}</span>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition">
                &larr; Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl {{ $report['is_ended'] ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-amber-600 hover:bg-amber-500' }} text-white text-xs font-black shadow-lg transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>{{ $report['is_ended'] ? 'Cetak Berita Acara Penetapan (PDF)' : 'Cetak Draft Rekapitulasi Sementara' }}</span>
            </button>
        </div>
    </div>

    <!-- Lembar Dokumen Berita Acara Resmi (A4 Paper Container) -->
    <div class="page-container max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200 text-black leading-relaxed">
        
        <!-- KOP SURAT RESMI -->
        <div class="flex items-center justify-between gap-4 pb-2">
            <!-- Logo Polbeng / Lambang Institusi -->
            <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                <div class="w-16 h-16 rounded-2xl border-2 border-slate-800/40 bg-slate-50 flex flex-col items-center justify-center font-bold text-slate-800 text-center leading-tight text-[10px] p-1.5 shadow-sm">
                    <span class="text-xs font-black">POLBENG</span>
                    <span class="text-[8px] text-slate-500 uppercase tracking-tighter">Bengkalis</span>
                </div>
            </div>

            <!-- Teks Kop Surat -->
            <div class="text-center flex-1">
                <h3 class="text-xs uppercase tracking-widest font-bold">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</h3>
                <h2 class="text-base font-extrabold uppercase tracking-wide mt-0.5">Politeknik Negeri Bengkalis</h2>
                <h1 class="text-lg font-black uppercase tracking-wider mt-0.5">Komisi Pemilihan Raya (KPR)</h1>
                <p class="text-[10px] mt-1 text-slate-700">
                    Jalan Bathin Alam, Sungai Alam, Bengkalis, Riau 28711<br>
                    Laman Resmi: <em>www.polbeng.ac.id</em> &bull; Pos-el: <em>kpr@polbeng.ac.id</em>
                </p>
            </div>

            <!-- Logo KPU-M Handcrafted Vector Emblem -->
            <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                <x-app-logo size="md" variant="mark" theme="light" />
            </div>
        </div>

        <!-- Garis Ganda Pemisah Kop Surat -->
        <div class="double-border"></div>

        <!-- Banner Peringatan Jika Waktu Belum Habis -->
        @if(!$report['is_ended'])
            <div class="border-2 border-dashed border-amber-600 bg-amber-50 p-3 rounded-xl text-center text-xs font-bold text-amber-900 mb-5 leading-normal">
                PERHATIAN: DOKUMEN INI ADALAH DRAFT REKAPITULASI SEMENTARA.<br>
                <span class="font-normal text-[11px] text-amber-800">
                    Pemungutan suara masih berlangsung sampai <strong>{{ $report['end_time_formatted'] }}</strong>.
                    Kandidat unggul saat ini belum ditetapkan sebagai pemenang resmi. Penetapan sah akan diterbitkan secara otomatis setelah waktu pemilihan resmi berakhir.
                </span>
            </div>
        @endif

        <!-- JUDUL BERITA ACARA -->
        <div class="text-center my-6 space-y-1">
            <h2 class="text-base font-black uppercase tracking-wider underline">
                @if($report['is_ended'])
                    BERITA ACARA &amp; SERTIFIKAT PENETAPAN HASIL TETAP
                @else
                    DRAFT REKAPITULASI SEMENTARA PERHITUNGAN SUARA
                @endif
            </h2>
            <h3 class="text-sm font-bold uppercase tracking-wide">
                PEMILIHAN RAYA MAHASISWA (PEMIRA) POLITEKNIK NEGERI BENGKALIS TAHUN {{ date('Y') }}
            </h3>
            <p class="text-xs font-mono font-bold mt-1">
                Nomor: {{ $report['ba_number'] }}
            </p>
        </div>

        <!-- PARAGRAF PEMBUKA SIDANG PLENO -->
        <div class="text-xs text-justify leading-relaxed mb-5 indent-8">
            Pada hari ini, <strong>{{ $report['generated_at'] }}</strong>, bertempat di Sekretariat Komisi Pemilihan Raya (KPR) &amp; PanWas (Panitia Pengawas) Politeknik Negeri Bengkalis, telah diselenggarakan <strong>Rapat Pleno Terbuka Rekapitulasi {{ $report['is_ended'] ? 'dan Penetapan Hasil Tetap' : 'Perolehan Suara Sementara' }}</strong> secara digital melalui Sistem E-Voting Berbasis Enkripsi Kriptografis (Secret Ballot Engine) untuk kategori pemilihan:
        </div>

        <!-- DETAIL KATEGORI -->
        <div class="bg-slate-50 border border-slate-300 p-3 rounded-lg text-xs mb-5">
            <table class="w-full">
                <tr>
                    <td class="w-36 font-bold py-1">Nama Pemilihan</td>
                    <td class="w-4">:</td>
                    <td class="font-extrabold uppercase">{{ $report['category']->name }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Kode Organisasi</td>
                    <td>:</td>
                    <td class="font-mono font-bold">{{ $report['category']->code }}</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Klasifikasi Ormawa</td>
                    <td>:</td>
                    <td>{{ $report['pillar_badge']['title'] }} ({{ $report['category']->study_program_filter ? 'Jurusan: ' . $report['category']->study_program_filter : 'Terbuka Seluruh Mahasiswa Polbeng' }})</td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Status Waktu Pemilihan</td>
                    <td>:</td>
                    <td class="font-bold {{ $report['is_ended'] ? 'text-emerald-800' : 'text-amber-800' }}">
                        {{ $report['is_ended'] ? 'Waktu Pemilihan Telah Berakhir (' . $report['end_time_formatted'] . ')' : 'Sedang Berlangsung (Batas Waktu: ' . $report['end_time_formatted'] . ')' }}
                    </td>
                </tr>
                <tr>
                    <td class="font-bold py-1">Metode Pemilihan</td>
                    <td>:</td>
                    <td>E-Voting Anonim Terenkripsi SHA-256 (3 Hak Suara per Mahasiswa Terdaftar)</td>
                </tr>
            </table>
        </div>

        <!-- BAGIAN 1: DATA PEMILIH & PENGGUNA HAK PILIH -->
        <div class="mb-5">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-2 font-sans">
                I. DATA PEMILIH DAN PENGGUNAAN HAK SUARA
            </h4>
            <table class="w-full border-collapse border border-slate-400 text-xs">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="border border-slate-400 px-3 py-1.5 text-center w-12 font-bold">No</th>
                        <th class="border border-slate-400 px-3 py-1.5 text-left font-bold">Uraian Data Partisipasi</th>
                        <th class="border border-slate-400 px-3 py-1.5 text-right w-36 font-bold">Jumlah (Angka)</th>
                        <th class="border border-slate-400 px-3 py-1.5 text-right w-28 font-bold">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-slate-400 px-3 py-1 text-center font-mono">1</td>
                        <td class="border border-slate-400 px-3 py-1 font-semibold">Total Daftar Pemilih Tetap (DPT) Sah yang Berhak Memilih</td>
                        <td class="border border-slate-400 px-3 py-1 text-right font-mono font-bold">{{ number_format($report['category_dpt']) }}</td>
                        <td class="border border-slate-400 px-3 py-1 text-right font-mono">100.0%</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-400 px-3 py-1 text-center font-mono">2</td>
                        <td class="border border-slate-400 px-3 py-1 font-semibold">Jumlah Suara Masuk Sah di Kotak Suara Digital (Terenkripsi)</td>
                        <td class="border border-slate-400 px-3 py-1 text-right font-mono font-bold">{{ number_format($report['total_ballots']) }}</td>
                        <td class="border border-slate-400 px-3 py-1 text-right font-mono font-bold text-slate-900">{{ $report['turnout_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-400 px-3 py-1 text-center font-mono">3</td>
                        <td class="border border-slate-400 px-3 py-1 font-semibold">Jumlah Mahasiswa Tidak Menggunakan Hak Suara (Golput)</td>
                        <td class="border border-slate-400 px-3 py-1 text-right font-mono font-bold">{{ number_format($report['not_voted_count']) }}</td>
                        <td class="border border-slate-400 px-3 py-1 text-right font-mono">{{ $report['not_voted_percentage'] }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- BAGIAN 2: RINCIAN PEROLEHAN SUARA KANDIDAT -->
        <div class="mb-5">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-2 font-sans">
                II. RINCIAN PEROLEHAN SUARA SAH PASANGAN CALON / KANDIDAT
            </h4>
            <table class="w-full border-collapse border border-slate-400 text-xs">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="border border-slate-400 px-3 py-2 text-center w-16 font-bold">No. Urut</th>
                        <th class="border border-slate-400 px-3 py-2 text-left font-bold">Nama Pasangan Calon / Kandidat</th>
                        <th class="border border-slate-400 px-3 py-2 text-center w-32 font-bold">NIM</th>
                        <th class="border border-slate-400 px-3 py-2 text-right w-28 font-bold">Suara Sah</th>
                        <th class="border border-slate-400 px-3 py-2 text-right w-24 font-bold">Persentase</th>
                        <th class="border border-slate-400 px-3 py-2 text-center w-36 font-bold">Status Penetapan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['candidates'] as $c)
                        @php
                            $isTop = $report['winner'] && $report['winner']['id'] === $c['id'];
                        @endphp
                        <tr class="{{ $isTop ? ($report['is_ended'] ? 'bg-emerald-50/50 font-bold' : 'bg-amber-50/50 font-bold') : '' }}">
                            <td class="border border-slate-400 px-3 py-2 text-center font-mono font-bold text-sm">
                                0{{ $c['number'] }}
                            </td>
                            <td class="border border-slate-400 px-3 py-2">
                                <div class="font-extrabold text-sm">{{ $c['leader_name'] }}</div>
                                @if($c['vice_leader_name'])
                                    <div class="text-[11px] text-slate-700 font-medium">Wakil: {{ $c['vice_leader_name'] }}</div>
                                @endif
                            </td>
                            <td class="border border-slate-400 px-3 py-2 text-center font-mono text-[11px]">
                                <div>{{ $c['leader_nim'] ?: '-' }}</div>
                                @if($c['vice_leader_nim'])
                                    <div>{{ $c['vice_leader_nim'] }}</div>
                                @endif
                            </td>
                            <td class="border border-slate-400 px-3 py-2 text-right font-mono font-bold text-sm">
                                {{ number_format($c['votes']) }}
                            </td>
                            <td class="border border-slate-400 px-3 py-2 text-right font-mono font-bold">
                                {{ $c['percentage'] }}%
                            </td>
                            <td class="border border-slate-400 px-3 py-2 text-center text-xs">
                                @if($isTop)
                                    @if(!empty($report['is_aklamasi']))
                                        <span class="inline-block px-2 py-0.5 rounded font-black text-amber-900 border border-amber-600 bg-amber-100">
                                            ★ TERPILIH (AKLAMASI)
                                        </span>
                                    @elseif($report['is_ended'])
                                        <span class="inline-block px-2 py-0.5 rounded font-black text-emerald-900 border border-emerald-600 bg-emerald-100">
                                            ★ TERPILIH (HASIL TETAP)
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded font-bold text-amber-900 border border-amber-600 bg-amber-100">
                                            UNGGUL SEMENTARA
                                        </span>
                                    @endif
                                @elseif($report['is_tie'])
                                    <span class="text-amber-800 font-bold">HASIL SEIMBANG</span>
                                @else
                                    <span class="text-slate-500 font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- BAGIAN 3: KEPUTUSAN PENETAPAN RESMI -->
        <div class="border-2 border-black p-4 rounded-xl mb-6 bg-slate-50">
            <h4 class="text-xs font-black uppercase tracking-wider text-center underline mb-2">
                @if($report['is_ended'])
                    KEPUTUSAN SIDANG PLENO KOMISI PEMILIHAN UMUM MAHASISWA
                @else
                    CATATAN PLENO HASIL PERHITUNGAN SEMENTARA
                @endif
            </h4>
            
            @if(!empty($report['is_aklamasi']))
                <div class="text-xs text-justify leading-relaxed">
                    Berdasarkan Berkas Pendaftaran &amp; Hasil Verifikasi Faktual Komisi Pemilihan Umum Mahasiswa yang telah disahkan dalam Sidang Pleno, karena hanya terdapat <strong>1 Pasangan Calon Tunggal</strong> yang memenuhi syarat administratif, maka berdasarkan Peraturan Tata Tertib Pemira Polbeng (Opsi Penetapan Aklamasi), Komisi Pemilihan Umum Mahasiswa Politeknik Negeri Bengkalis secara sah dan mengikat:
                </div>
            @elseif($report['is_ended'])
                <div class="text-xs text-justify leading-relaxed">
                    Berdasarkan hasil penghitungan suara yang telah berakhir sesuai batas waktu resmi dan memenuhi kuorum sidang pleno, Komisi Pemilihan Umum Mahasiswa Politeknik Negeri Bengkalis secara sah dan mengikat:
                </div>
                
                @if($report['winner'])
                    <div class="my-3 text-center p-3 bg-white border border-slate-300 rounded-lg">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-600 block">MENETAPKAN PEMENANG RESMI:</span>
                        <h3 class="text-base font-black uppercase text-slate-900 mt-1">
                            {{ $report['winner']['leader_name'] }}
                            @if($report['winner']['vice_leader_name'])
                                &amp; {{ $report['winner']['vice_leader_name'] }}
                            @endif
                        </h3>
                        <p class="text-xs font-bold text-slate-700 mt-0.5">
                            (Paslon Nomor Urut 0{{ $report['winner']['number'] }})
                        </p>
                        <p class="text-xs font-extrabold uppercase mt-1 text-emerald-900">
                            SEBAGAI {{ $report['category']->getPillarType() === 'BEM' ? 'PRESIDEN & WAKIL PRESIDEN MAHASISWA (BEM KM)' : ($report['category']->getPillarType() === 'MPM' ? 'KETUA UMUM MAJELIS PERMUSYAWARATAN MAHASISWA (MPM)' : 'KETUA HIMPUNAN MAHASISWA JURUSAN') }} TERPILIH POLITEKNIK NEGERI BENGKALIS PERIODE 2026/2027
                        </p>
                    </div>
                @elseif($report['is_tie'])
                    <div class="my-3 text-center p-3 bg-white border border-amber-300 rounded-lg text-amber-900 font-bold text-xs">
                        Perolehan suara antar pasangan calon adalah seimbang (draw). Ketetapan final mengacu pada Anggaran Dasar/Anggaran Rumah Tangga KPU Mahasiswa Polbeng melalui musyawarah pleno lanjutan.
                    </div>
                @else
                    <div class="my-3 text-center p-3 bg-white border border-slate-300 rounded-lg text-slate-500 font-bold text-xs">
                        Sesi pemungutan suara belum memiliki rekapitulasi suara masuk definitif.
                    </div>
                @endif

                <div class="text-[10px] text-slate-600 italic">
                    Keputusan ini bersifat final, mengikat, dan tidak dapat diganggu gugat sejak ditandatanganinya Berita Acara ini oleh seluruh komisioner KPU Mahasiswa dan para saksi.
                </div>
            @else
                <div class="text-xs text-justify leading-relaxed text-amber-950 p-2 bg-amber-50 rounded border border-amber-200">
                    <strong>Pemberitahuan:</strong> Sesi pemungutan suara masih aktif berlangsung hingga <strong>{{ $report['end_time_formatted'] }}</strong>.
                    Kandidat nomor urut <strong>0{{ $report['winner']['number'] ?? '-' }}</strong> saat ini memimpin perolehan suara sementara. Keputusan penetapan pemenang definitif akan disahkan oleh Komisi Pemilihan Umum Mahasiswa setelah waktu pemilihan resmi berakhir.
                </div>
            @endif
        </div>

        <!-- INTEGRITAS DIGITAL & HASH BUKTI -->
        <div class="p-2.5 rounded-lg border border-slate-300 text-[10px] font-mono text-slate-600 mb-8 flex flex-col sm:flex-row justify-between gap-1 bg-slate-50/50">
            <div>
                <strong>SHA-256 Document Hash:</strong> {{ $report['document_checksum'] }}
            </div>
            <div class="text-right">
                <strong>Status Dokumen:</strong> {{ $report['is_ended'] ? 'HASIL TETAP (RESMI & MENGIKAT)' : 'DRAFT SEMENTARA (BERJALAN)' }}
            </div>
        </div>

        <!-- LEMBAR TANDA TANGAN RESMI -->
        <div class="pt-4 text-xs font-sans">
            <div class="text-right mb-4">
                Bengkalis, {{ $report['official_date'] }}
            </div>

            <!-- Tanda Tangan Saksi-Saksi Paslon -->
            <div class="mb-6">
                <div class="text-center font-bold uppercase tracking-wider text-[11px] mb-3">
                    SAKSI-SAKSI PASANGAN CALON / KANDIDAT
                </div>
                <div class="grid grid-cols-2 gap-8 text-center">
                    <div>
                        <span class="block font-medium">Saksi Paslon 01</span>
                        <div class="h-16 flex items-end justify-center">
                            <span class="text-[10px] text-slate-400 italic">(Tanda Tangan &amp; Nama Terang)</span>
                        </div>
                        <div class="border-b border-black w-48 mx-auto mt-2"></div>
                        <span class="block text-[11px] font-bold mt-1">NIM: ........................................</span>
                    </div>

                    <div>
                        <span class="block font-medium">Saksi Paslon 02</span>
                        <div class="h-16 flex items-end justify-center">
                            <span class="text-[10px] text-slate-400 italic">(Tanda Tangan &amp; Nama Terang)</span>
                        </div>
                        <div class="border-b border-black w-48 mx-auto mt-2"></div>
                        <span class="block text-[11px] font-bold mt-1">NIM: ........................................</span>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan Panitia KPR, PanWas & Pimpinan Kampus -->
            <div class="border-t border-slate-300 pt-6">
                <div class="text-center font-bold uppercase tracking-wider text-[11px] mb-4">
                    KOMISI PEMILIHAN RAYA (KPR) &amp; PANWAS POLITEKNIK NEGERI BENGKALIS
                </div>
                
                <div class="grid grid-cols-2 gap-8 text-center mb-6">
                    <div>
                        <span class="block font-bold">Ketua KPR (Komisi Pemilihan Raya)</span>
                        <div class="h-16 flex items-end justify-center">
                            <span class="text-[10px] text-slate-400 italic">[ Cap Stempel &amp; Tanda Tangan ]</span>
                        </div>
                        <div class="border-b border-black w-48 mx-auto mt-2"></div>
                        <span class="block text-xs font-black mt-1">Danang Prakoso</span>
                        <span class="block text-[10px] font-mono text-slate-600">NIM: 120120001</span>
                    </div>

                    <div>
                        <span class="block font-bold">Ketua PanWas (Panitia Pengawas)</span>
                        <div class="h-16 flex items-end justify-center">
                            <span class="text-[10px] text-slate-400 italic">[ Tanda Tangan ]</span>
                        </div>
                        <div class="border-b border-black w-48 mx-auto mt-2"></div>
                        <span class="block text-xs font-black mt-1">Rizky Alamsyah</span>
                        <span class="block text-[10px] font-mono text-slate-600">NIM: 120140025</span>
                    </div>
                </div>

                <!-- Mengetahui Direktur / Wadir III Polbeng -->
                <div class="text-center pt-2">
                    <span class="block text-[11px] font-medium text-slate-700">Mengetahui &amp; Mengesahkan,</span>
                    <span class="block text-xs font-bold uppercase mt-0.5">Wakil Direktur III Bidang Kemahasiswaan dan Kerjasama</span>
                    <span class="block text-xs font-bold uppercase">Politeknik Negeri Bengkalis</span>
                    
                    <div class="h-20 flex items-end justify-center">
                        <span class="text-[10px] text-slate-400 italic">[ Tanda Tangan &amp; Cap Dinas Resmi ]</span>
                    </div>
                    <div class="border-b border-black w-64 mx-auto mt-2"></div>
                    <span class="block text-xs font-black mt-1">Marhadi Sastra, M.Sc.</span>
                    <span class="block text-[10px] font-mono text-slate-600">NIP: 19820514 200812 1 002</span>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
