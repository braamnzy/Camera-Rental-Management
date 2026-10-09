@extends('layouts.admin')

@section('title', 'Inventaris Kamera')
@section('subtitle', 'Kelola daftar unit kamera, stok, dan status ketersediaan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <!-- Search Bar -->
    <div class="relative w-full sm:w-80">
        <input type="text" id="searchInput" placeholder="Cari nama kamera atau merk..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>
    
    <!-- Tombol Tambah Kamera -->
    <a href="/admin/cameras/create" class="inline-flex items-center px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold rounded-lg shadow-sm transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Kamera Baru
    </a>
</div>

<!-- Tabel Inventaris Kamera -->
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">Display Unit</th>
                <th class="px-6 py-3">Nama & Merk</th>
                <th class="px-6 py-3">Harga / Hari</th>
                <th class="px-6 py-3">Stok Unit</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody id="camerasTableBody" class="divide-y divide-slate-200">
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">Memuat data inventaris kamera...</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        const tableBody = document.getElementById('camerasTableBody');
        const searchInput = document.getElementById('searchInput');
        let allCameras = [];

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka || 0);

        const loadCameras = () => {
            fetch('/api/cameras', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(response => {
                allCameras = response.data || response;
                renderCameras(allCameras);
            })
            .catch(err => {
                console.error("Error loading cameras:", err);
                tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-rose-500 font-medium">Gagal memuat data inventaris kamera.</td></tr>';
            });
        };

        const renderCameras = (cameras) => {
            if (!Array.isArray(cameras) || cameras.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada unit kamera di inventaris.</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            cameras.forEach(cam => {
                const isAvailable = cam.status === 'available';
                const statusBadge = isAvailable
                    ? '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-emerald-100 text-emerald-700">Tersedia</span>'
                    : '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-rose-100 text-rose-700">Maintenance</span>';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="w-16 h-12 bg-slate-100 rounded border border-slate-200 overflow-hidden">
                            ${cam.image_url 
                                ? `<img src="${cam.image_url}" alt="${cam.name}" class="w-full h-full object-cover">` 
                                : `<div class="w-full h-full flex items-center justify-center text-[10px] text-slate-400">No Image</div>`}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900">${cam.name}</div>
                        <div class="text-xs text-slate-400">${cam.brand}</div>
                    </td>
                    <td class="px-6 py-4 font-semibold text-slate-800">${formatRupiah(cam.daily_rate)}</td>
                    <td class="px-6 py-4 font-medium">${cam.stock} Unit</td>
                    <td class="px-6 py-4">${statusBadge}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="/admin/cameras/${cam.id}/edit" class="inline-block px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded transition">
                            Edit
                        </a>
                        <button onclick="deleteCamera(${cam.id})" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded transition">
                            Hapus
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        };

        window.deleteCamera = (id) => {
            if (!confirm('Apakah Anda yakin ingin menghapus kamera ini?')) return;

            fetch(`/api/cameras/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Kamera berhasil dihapus.');
                loadCameras();
            })
            .catch(err => {
                console.error("Error deleting camera:", err);
                alert('Gagal menghapus kamera.');
            });
        };

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allCameras.filter(c => 
                c.name.toLowerCase().includes(query) || 
                c.brand.toLowerCase().includes(query)
            );
            renderCameras(filtered);
        });

        loadCameras();
    });
</script>
@endpush