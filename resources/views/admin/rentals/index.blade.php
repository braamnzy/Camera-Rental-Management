@extends('layouts.admin')

@section('title', 'Kelola Transaksi Sewa')
@section('subtitle', 'Verifikasi status transaksi, serahkan unit, dan atur pengembalian')

@section('content')
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">ID Order / Customer</th>
                <th class="px-6 py-3">Unit Kamera</th>
                <th class="px-6 py-3">Tanggal Sewa</th>
                <th class="px-6 py-3">Total Tagihan</th>
                <th class="px-6 py-3">Status Rental</th>
                <th class="px-6 py-3 text-right">Aksi Tindakan Admin</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($rentals ?? [] as $rental)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4">
                    <div class="font-bold text-slate-900">#{{ $rental->id }}</div>
                    <div class="text-xs text-slate-500">{{ $rental->user->name ?? 'N/A' }} ({{ $rental->user->phone ?? '-' }})</div>
                </td>
                <td class="px-6 py-4 font-medium text-slate-800">{{ $rental->camera->name ?? '-' }}</td>
                <td class="px-6 py-4 text-xs">
                    <div>Mulai: {{ $rental->start_date }}</div>
                    <div>Selesai: {{ $rental->end_date }}</div>
                </td>
                <td class="px-6 py-4 font-semibold text-slate-900">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 text-xs rounded-full font-semibold
                        {{ $rental->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $rental->status === 'picked_up' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $rental->status === 'returned' ? 'bg-slate-100 text-slate-700' : '' }}
                        {{ $rental->status === 'pending_payment' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $rental->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                        {{ strtoupper(str_replace('_', ' ', $rental->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-1">
                    @if($rental->status === 'paid')
                        <form action="{{ route('admin.rentals.update-status', $rental->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="picked_up">
                            <button type="submit" class="px-3 py-1.5 bg-[#2563EB] text-white text-xs font-semibold rounded-md hover:bg-blue-700">Serahkan Unit (Pick Up)</button>
                        </form>
                    @endif

                    @if($rental->status === 'picked_up')
                        <form action="{{ route('admin.rentals.update-status', $rental->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="returned">
                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-md hover:bg-emerald-700">Konfirmasi Pengembalian</button>
                        </form>
                    @endif

                    @if(in_array($rental->status, ['pending_payment', 'paid']))
                        <form action="{{ route('admin.rentals.update-status', $rental->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Batalkan transaksi ini?')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 text-xs font-semibold rounded-md hover:bg-rose-100">Batalkan</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada transaksi penyewaan masuk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection