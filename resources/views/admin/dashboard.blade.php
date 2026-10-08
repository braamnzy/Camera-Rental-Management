@extends('layouts.admin')

@section('title', 'Dashboard Ringkasan')
@section('subtitle', 'Ikhtisar aktivitas bisnis dan pendapatan CamRent')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Pendapatan -->
    <div class="bg-[#F8FAFC] border border-slate-200 p-6 rounded-xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pendapatan</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">
                    Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg text-[#2563EB]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    
    <div class="bg-[#F8FAFC] border border-slate-200 p-6 rounded-xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penyewaan Aktif</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">
                    {{ $activeRentalsCount ?? 0 }} Unit
                </h3>
            </div>
            <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    
    <div class="bg-[#F8FAFC] border border-slate-200 p-6 rounded-xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Pembayaran</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">
                    {{ $pendingRentalsCount ?? 0 }} Transaksi
                </h3>
            </div>
            <div class="p-3 bg-rose-100 rounded-lg text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>


<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-[#F8FAFC]">
        <h2 class="font-bold text-slate-800">Transaksi Terbaru</h2>
        <a href="{{ route('admin.rentals.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">Lihat Semua →</a>
    </div>

    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">Penyewa</th>
                <th class="px-6 py-3">Kamera</th>
                <th class="px-6 py-3">Durasi Sewa</th>
                <th class="px-6 py-3">Total Bayar</th>
                <th class="px-6 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($recentRentals ?? [] as $rental)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-semibold text-slate-900">{{ $rental->user->name ?? '-' }}</td>
                <td class="px-6 py-4">{{ $rental->camera->name ?? '-' }}</td>
                <td class="px-6 py-4 text-xs">{{ $rental->start_date }} s/d {{ $rental->end_date }}</td>
                <td class="px-6 py-4 font-medium">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 text-xs rounded-full font-semibold
                        {{ $rental->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $rental->status === 'pending_payment' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $rental->status === 'picked_up' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $rental->status === 'returned' ? 'bg-slate-100 text-slate-700' : '' }}
                        {{ $rental->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                        {{ strtoupper(str_replace('_', ' ', $rental->status)) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada transaksi terbaru.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection