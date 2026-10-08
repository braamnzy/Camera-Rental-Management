@extends('layouts.admin')

@section('title', 'Monitoring Jadwal Sewa')
@section('subtitle', 'Matriks penggunaan kamera dan peringatan unit jatuh tempo')

@section('content')
<div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-wrap gap-4 items-center justify-between text-xs font-semibold">
    <div class="flex items-center space-x-4">
        <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1.5"></span> Sedang Disewa (Picked Up)</span>
        <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5"></span> Sudah Kembali (Returned)</span>
        <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-[#EF4444] mr-1.5"></span> Terlambat / Overdue</span>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">Unit Kamera</th>
                <th class="px-6 py-3">Penyewa Aktif</th>
                <th class="px-6 py-3">Periode Sewa</th>
                <th class="px-6 py-3">Batas Tanggal Kembali</th>
                <th class="px-6 py-3">Status Jadwal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($schedules ?? [] as $item)
            @php
                // Cek kondisi Overdue: Status masih 'picked_up' DAN tanggal selesai sudah lewat dari hari ini
                $isOverdue = ($item->status === 'picked_up') && (\Carbon\Carbon::parse($item->end_date)->isPast());
            @endphp
            <tr class="{{ $isOverdue ? 'bg-rose-50/70 border-l-4 border-[#EF4444]' : 'hover:bg-slate-50' }}">
                <td class="px-6 py-4 font-bold text-slate-900">{{ $item->camera->name ?? '-' }}</td>
                <td class="px-6 py-4 font-semibold text-slate-800">{{ $item->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-xs">{{ $item->start_date }} s/d {{ $item->end_date }}</td>
                <td class="px-6 py-4 font-medium {{ $isOverdue ? 'text-[#EF4444] font-bold' : 'text-slate-700' }}">
                    {{ $item->end_date }}
                </td>
                <td class="px-6 py-4">
                    @if($isOverdue)
                        <span class="px-3 py-1 bg-[#EF4444] text-white text-xs font-bold rounded-full animate-pulse">
                            ⚠️ OVERDUE (TERLAMBAT)
                        </span>
                    @else
                        <span class="px-2.5 py-1 text-xs rounded-full font-semibold
                            {{ $item->status === 'picked_up' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                        </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-slate-400">Tidak ada jadwal penyewaan kamera yang aktif saat ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection