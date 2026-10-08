@extends('layouts.admin')

@section('title', 'Edit Data Kamera')
@section('subtitle', 'Perbarui informasi dan spesifikasi unit kamera')

@section('content')
<div class="max-w-2xl bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
    <form action="{{ route('admin.cameras.update', $camera->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Kamera</label>
            <input type="text" name="name" value="{{ old('name', $camera->name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="misal: Sony Alpha A7 III">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Merk / Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $camera->brand) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="Sony / Canon / Fujifilm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Sewa per Hari (Rp)</label>
                <input type="number" name="daily_rate" value="{{ old('daily_rate', $camera->daily_rate) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="250000">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Jumlah Stok</label>
                <input type="number" name="stock" value="{{ old('stock', $camera->stock) }}" min="0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="3">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Status Unit</label>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]">
                    <option value="available" {{ old('status', $camera->status) === 'available' ? 'selected' : '' }}>Tersedia (Available)</option>
                    <option value="maintenance" {{ old('status', $camera->status) === 'maintenance' ? 'selected' : '' }}>Perbaikan (Maintenance)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi & Kelengkapan</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="Termasuk 2 Baterai, Charger, Bag, Memory 64GB...">{{ old('description', $camera->description) }}</textarea>
        </div>

        <!-- Upload & Preview Foto Kamera -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Ganti Foto Display Kamera (Opsional)</label>
            <input type="file" name="image" id="imageInput" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" onchange="previewImage(event)">
            
            <div class="mt-3 flex items-center space-x-4">
                <!-- Preview Foto Lama / Baru -->
                <div>
                    <span class="block text-xs text-slate-500 mb-1">Foto Saat Ini / Preview:</span>
                    <img id="imagePreview" src="{{ asset('storage/' . $camera->image) }}" alt="{{ $camera->name }}" class="w-48 h-32 object-cover rounded-lg border border-slate-200 shadow-sm">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 flex justify-end space-x-3">
            <a href="{{ route('admin.cameras.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Batal</a>
            <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold rounded-lg">Perbarui Kamera</button>
        </div>
    </form>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection