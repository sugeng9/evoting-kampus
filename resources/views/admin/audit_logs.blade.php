@extends('layouts.app')

@section('title', 'Seluruh Log Audit Keamanan - KPU Kampus')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Audit Log -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 mb-2">
                <span>Immutable Security Trail</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Catatan Audit Keamanan Sistem</h1>
            <p class="text-xs text-slate-300 mt-1">Seluruh interaksi pemilih, percobaan akses, dan perubahan status sesi terekam permanen.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Tabel Log Lengkap -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">ID</th>
                        <th class="py-3 px-6">Waktu Transaksi (WIB)</th>
                        <th class="py-3 px-6">Peristiwa (Event)</th>
                        <th class="py-3 px-6">Aktor &amp; ID</th>
                        <th class="py-3 px-6">Alamat IP</th>
                        <th class="py-3 px-6">User Agent</th>
                        <th class="py-3 px-6">Payload / Parameter</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-6 text-slate-400">#{{ $log->id }}</td>
                            <td class="py-3.5 px-6 text-slate-600 whitespace-nowrap">{{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i:s') }} WIB</td>
                            <td class="py-3.5 px-6">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_contains($log->event_type, 'SUCCESS') ? 'bg-emerald-100 text-emerald-800' : (str_contains($log->event_type, 'FAIL') || str_contains($log->event_type, 'BLOCKED') ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-800') }}">
                                    {{ $log->event_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-slate-700 capitalize font-sans">{{ $log->actor_type }} #{{ $log->actor_id ?: '-' }}</td>
                            <td class="py-3.5 px-6 text-slate-500">{{ $log->ip_address }}</td>
                            <td class="py-3.5 px-6 text-slate-400 truncate max-w-[200px]" title="{{ $log->user_agent }}">{{ $log->user_agent }}</td>
                            <td class="py-3.5 px-6 text-slate-600 truncate max-w-xs" title="{{ json_encode($log->payload) }}">
                                {{ json_encode($log->payload) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada catatan log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
