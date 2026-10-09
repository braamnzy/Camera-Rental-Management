@extends('layouts.admin')

@section('title', 'Edit Data Kamera')
@section('subtitle', 'Perbarui informasi dan spesifikasi unit kamera')

@section('content')
<div class="max-w-2xl bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
    <div id="loadingState" class="text-center py-8 text-slate-500 font-medium">
        Memuat data kamera...
    </div>

    <form id="editCameraForm" class="space-y-4 hidden">
        <input type="hidden" name="_method" value="PUT">

        <!-- Nama Kamera -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Kamera</label>
            <input type="text" name="name" id="name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="misal: Sony Alpha A7 III">
            <span id="error-name" class="text-xs text-red-500 mt-1 block hidden"></span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Merk / Brand -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Merk / Brand</label>
                <input type="text" name="brand" id="brand" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="Sony / Canon / Fujifilm">
                <span id="error-brand" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
            <!-- Harga Sewa -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Sewa per Hari (Rp)</label>
                <input type="number" name="daily_rate" id="daily_rate" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="250000">
                <span id="error-daily_rate" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Stok -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Jumlah Stok</label>
                <input type="number" name="stock" id="stock" min="0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="3">
                <span id="error-stock" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
            <!-- Status Unit -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Status Unit</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]">
                    <option value="available">Tersedia (Available)</option>
                    <option value="maintenance">Perbaikan (Maintenance)</option>
                </select>
                <span id="error-status" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
        </div>

        <!-- Deskripsi -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi & Kelengkapan</label>
            <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="Termasuk 2 Baterai, Charger, Bag, Memory 64GB..."></textarea>
            <span id="error-description" class="text-xs text-red-500 mt-1 block hidden"></span>
        </div>

        <!-- Upload & Preview Foto Kamera -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Ganti Foto Display Kamera (Opsional)</label>
            <input type="file" name="image" id="imageInput" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" onchange="previewImage(event)">
            <span id="error-image" class="text-xs text-red-500 mt-1 block hidden"></span>

            <div class="mt-3 flex items-center space-x-4">
                <div>
                    <span class="block text-xs text-slate-500 mb-1">Foto Saat Ini / Preview Baru:</span>
                    <img id="imagePreview" src="#" alt="Preview" class="w-48 h-32 object-cover rounded-lg border border-slate-200 shadow-sm">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 flex justify-end space-x-3">
            <a href="/admin/cameras" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Batal</a>
            <button type="submit" id="btnSubmit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold rounded-lg">Perbarui Kamera</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
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

    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        if (!token) {
            alert('Sesi berakhir. Silakan login kembali.');
            window.location.href = '/login';
            return;
        }

        const urlParts = window.location.pathname.split('/');
        const cameraId = urlParts[urlParts.indexOf('cameras') + 1];

        const form = document.getElementById('editCameraForm');
        const loadingState = document.getElementById('loadingState');
        const preview = document.getElementById('imagePreview');

        // 1. Ambil data kamera
        fetch(`/api/cameras/${cameraId}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(async res => {
            const text = await res.text();
            let json;
            try { json = JSON.parse(text); }
            catch (e) {
                console.error('Response bukan JSON:', text.substring(0, 300));
                throw new Error(`HTTP ${res.status}: Response bukan JSON`);
            }
            if (!res.ok) throw new Error(json.message || `HTTP ${res.status}`);
            return json;
        })
        .then(response => {
            const cam = response.data || response;

            document.getElementById('name').value = cam.name || '';
            document.getElementById('brand').value = cam.brand || '';
            document.getElementById('daily_rate').value = cam.daily_rate || 0;
            document.getElementById('stock').value = cam.stock || 0;
            document.getElementById('status').value = cam.status || 'available';
            document.getElementById('description').value = cam.description || '';

            if (cam.image_url) {
                preview.src = cam.image_url;
            } else {
                preview.classList.add('hidden');
            }

            loadingState.classList.add('hidden');
            form.classList.remove('hidden');
        })
        .catch(err => {
            console.error(err);
            loadingState.textContent = `Gagal memuat: ${err.message}`;
            loadingState.classList.add('text-red-500');
        });

        // 2. Submit form update
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btnSubmit');
            btn.textContent = 'Memperbarui...';
            btn.disabled = true;

            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });

            const formData = new FormData(form);

            try {
                const response = await fetch(`/api/cameras/${cameraId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Data kamera berhasil diperbarui!');
                    window.location.href = '/admin/cameras';
                } else if (response.status === 422) {
                    const errors = data.errors || {};
                    Object.keys(errors).forEach(field => {
                        const errorEl = document.getElementById(`error-${field}`);
                        if (errorEl) {
                            errorEl.textContent = errors[field][0];
                            errorEl.classList.remove('hidden');
                        }
                    });
                    btn.textContent = 'Perbarui Kamera';
                    btn.disabled = false;
                } else {
                    alert(data.message || 'Gagal memperbarui data.');
                    btn.textContent = 'Perbarui Kamera';
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                btn.textContent = 'Perbarui Kamera';
                btn.disabled = false;
            }
        });
    });
</script>
@endpush