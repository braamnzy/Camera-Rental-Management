@extends('layouts.admin')

@section('title', 'Inventaris Kamera')
@section('subtitle', 'Daftar seluruh unit kamera yang tersedia untuk disewakan')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div></div>

    <a href="{{ route('admin.cameras.create') }}" class="px-4 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Kamera Baru
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">Foto Display</th>
                <th class="px-6 py-3">Nama Kamera</th>
                <th class="px-6 py-3">Merk</th>
                <th class="px-6 py-3">Harga / Hari</th>
                <th class="px-6 py-3">Stok</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($cameras ?? [] as $camera)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-3">
                    <img src="{{ asset('storage/' . $camera->image) }}" alt="{{ $camera->name }}" class="w-16 h-12 object-cover rounded-md border border-slate-200">
                </td>
                <td class="px-6 py-4 font-bold text-slate-900">{{ $camera->name }}</td>
                <td class="px-6 py-4">{{ $camera->brand }}</td>
                <td class="px-6 py-4 font-medium text-slate-800">Rp {{ number_format($camera->daily_rate, 0, ',', '.') }}</td>
                <td class="px-6 py-4 font-semibold">{{ $camera->stock }} Unit</td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 text-xs rounded-full font-semibold {{ $camera->status === 'available' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ ucfirst($camera->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('admin.cameras.edit', $camera->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition">Edit</a>
                    <form action="{{ route('admin.cameras.destroy', $camera->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kamera ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-md transition">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada unit kamera. Tambahkan unit pertama!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection