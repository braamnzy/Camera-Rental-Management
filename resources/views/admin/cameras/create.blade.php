@extends('layouts.admin')

@section('title', 'Tambah Kamera Baru')
@section('subtitle', 'Unggah dan lengkapi data spesifikasi kamera baru')

@section('content')
<div class="max-w-2xl bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
    <form id="createCameraForm" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Kamera</label>
            <input type="text" name="name" id="name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="misal: Sony Alpha A7 III">
            <span id="error-name" class="text-xs text-red-500 mt-1 block hidden"></span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Merk / Brand</label>
                <input type="text" name="brand" id="brand" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="Sony / Canon / Fujifilm">
                <span id="error-brand" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Sewa per Hari (Rp)</label>
                <input type="number" name="daily_rate" id="daily_rate" min="0" oninput="if(this.value < 0) this.value = 0;" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="250000">
                <span id="error-daily_rate" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Jumlah Stok</label>
                <input type="number" name="stock" id="stock" min="1" value="1" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="3">
                <span id="error-stock" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Status Unit</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]">
                    <option value="available">Tersedia (Available)</option>
                    <option value="maintenance">Perbaikan (Maintenance)</option>
                </select>
                <span id="error-status" class="text-xs text-red-500 mt-1 block hidden"></span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi & Kelengkapan</label>
            <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#2563EB]" placeholder="Termasuk 2 Baterai, Charger, Bag, Memory 64GB..."></textarea>
            <span id="error-description" class="text-xs text-red-500 mt-1 block hidden"></span>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Unggah Foto Display Kamera (Max 2MB)</label>
            <input type="file" name="image" id="imageInput" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" onchange="previewImage(event)">
            <span id="error-image" class="text-xs text-red-500 mt-1 block hidden"></span>

            <div class="mt-3">
                <img id="imagePreview" src="#" alt="Preview Foto" class="hidden w-48 h-32 object-cover rounded-lg border border-slate-200 shadow-sm">
            </div>
        </div>

        <div class="pt-4 flex justify-end space-x-3">
            <a href="/admin/cameras" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50">Batal</a>
            <button type="submit" id="btnSubmit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold rounded-lg">Simpan Kamera</button>
        </div>
    </form>
</div>
@endsection

@stack('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('createCameraForm');
        const token = localStorage.getItem('token');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!token) {
                alert('Sesi Anda berakhir. Silakan login kembali.');
                window.location.href = '/login';
                return;
            }

            const btn = document.getElementById('btnSubmit');
            btn.textContent = 'Menyimpan...';
            btn.disabled = true;

            // Reset pesan error
            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });

            // Gunakan FormData untuk mengirim file multipart
            const formData = new FormData(form);

            try {
                const response = await fetch('/api/cameras', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Kamera berhasil ditambahkan!');
                    window.location.href = '/admin/cameras';
                } else if (response.status === 422) {
                    // Validasi error dari StoreCameraRequest
                    const errors = data.errors || {};
                    Object.keys(errors).forEach(field => {
                        const errorEl = document.getElementById(`error-${field}`);
                        if (errorEl) {
                            errorEl.textContent = errors[field][0];
                            errorEl.classList.remove('hidden');
                        }
                    });
                    btn.textContent = 'Simpan Kamera';
                    btn.disabled = false;
                } else {
                    alert(data.message || 'Gagal menyimpan data.');
                    btn.textContent = 'Simpan Kamera';
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                btn.textContent = 'Simpan Kamera';
                btn.disabled = false;
            }
        });
    });
</script>