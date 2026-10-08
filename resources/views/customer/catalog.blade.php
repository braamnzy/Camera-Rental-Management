@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header Bagian Atas (Banner) -->
    <div class="bg-[#1E293B] text-white rounded-xl p-8 mb-8 shadow-lg">
        <h1 class="text-3xl font-bold mb-2">Sewa Peralatan Fotografi & Videografi Profesional</h1>
        <p class="text-gray-300">Pilihan unit mirrorless, lensa, dan action cam kondisi prima dengan proses verifikasi instan.</p>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div class="w-full md:w-1/2 relative">
            <input type="text" id="searchInput" placeholder="Cari: [ Input Kamera / Merk ]" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-[#2563EB] focus:border-[#2563EB] outline-none">
        </div>
        <div class="flex gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0" id="categoryFilters">
            <!-- Filter Kategori Aktif: bg-[#1E293B] text-white -->
            <button data-cat="all" class="filter-btn bg-[#1E293B] text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition">SEMUA</button>
            <button data-cat="mirrorless" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">MIRRORLESS</button>
            <button data-cat="dslr" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">DSLR</button>
            <button data-cat="lensa" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">LENSA</button>
        </div>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-6">Daftar Unit Kamera Tersedia</h2>

    <!-- Grid Container untuk Kamera -->
    <div id="camerasGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Tampilan Loading sementara data ditarik dari API -->
        <div class="col-span-full text-center py-12 text-gray-500">
            Memuat daftar kamera...
        </div>
    </div>
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const camerasGrid = document.getElementById('camerasGrid');
        let allCameras = []; // Menyimpan semua data untuk filter lokal

        // Fungsi Format Rupiah
        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        };

        // Fungsi Render Card Kamera
        const renderCameras = (cameras) => {
            camerasGrid.innerHTML = '';

            if (cameras.length === 0) {
                camerasGrid.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500">Kamera tidak ditemukan.</div>';
                return;
            }

            cameras.forEach(cam => {
                const stockBadge = cam.stock > 0 ?
                    '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-semibold">Tersedia</span>' :
                    '<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded font-semibold">Habis</span>';

                const cardHtml = `
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden relative">
                        <!-- Menampilkan gambar, asumsi backend mengembalikan full URL atau asset path -->
                        ${cam.image ? `<img src="${cam.image}" alt="${cam.name}" class="w-full h-full object-cover">` : `<span class="text-gray-400">Display ${cam.name}</span>`}
                        <div class="absolute top-2 right-2">${stockBadge}</div>
                    </div>
                    <div class="p-5">
                        <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded mb-2 uppercase tracking-wide">${cam.brand}</span>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight">${cam.name}</h3>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">${cam.description || 'Tidak ada deskripsi'}</p>
                        
                        <div class="flex items-center justify-between mt-auto">
                            <div>
                                <span class="text-[#2563EB] font-bold text-lg">${formatRupiah(cam.daily_rate)}</span><span class="text-gray-500 text-sm">/hr</span>
                            </div>
                            <a href="/catalog/${cam.id}" class="bg-green-100 text-green-700 hover:bg-green-200 font-semibold px-4 py-2 rounded-lg text-sm transition">
                                DETAIL
                            </a>
                        </div>
                    </div>
                </div>
            `;
                camerasGrid.innerHTML += cardHtml;
            });
        };

        // Ambil Data dari API Backend
        fetch('/api/cameras', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                // Asumsi backend mengembalikan data dalam format: { data: [...] } (karena menggunakan Pagination/Resource API)
                allCameras = data.data || data;
                renderCameras(allCameras);
            })
            .catch(err => {
                console.error(err);
                camerasGrid.innerHTML = '<div class="col-span-full text-center py-12 text-red-500">Gagal memuat data dari server.</div>';
            });

        // Implementasi Pencarian Sederhana (Client-side)
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const keyword = e.target.value.toLowerCase();
            const filtered = allCameras.filter(cam =>
                cam.name.toLowerCase().includes(keyword) ||
                cam.brand.toLowerCase().includes(keyword)
            );
            renderCameras(filtered);
        });
    });
</script>