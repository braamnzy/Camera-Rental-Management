@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header Banner -->
    <div class="bg-[#1E293B] text-white rounded-xl p-8 mb-8 shadow-lg">
        <h1 class="text-3xl font-bold mb-2">Sewa Peralatan Fotografi & Videografi Profesional</h1>
        <p class="text-gray-300">Pilihan unit mirrorless, lensa, dan action cam kondisi prima dengan proses verifikasi instan.</p>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div class="w-full md:w-1/2 relative">
            <input type="text" id="searchInput" placeholder="Cari: nama kamera atau merk..."
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-[#2563EB] focus:border-[#2563EB] outline-none">
        </div>
        <div class="flex gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0" id="categoryFilters">
            <button data-cat="all" class="filter-btn bg-[#1E293B] text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition">SEMUA</button>
            <button data-cat="mirrorless" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">MIRRORLESS</button>
            <button data-cat="dslr" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">DSLR</button>
            <button data-cat="lensa" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">LENSA</button>
            <button data-cat="actioncam" class="filter-btn bg-blue-100 text-[#2563EB] px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition hover:bg-blue-200">ACTION CAM</button>
        </div>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-6">Daftar Unit Kamera Tersedia</h2>

    <!-- Grid Container untuk Kamera -->
    <div id="camerasGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-full text-center py-12 text-gray-500">
            Memuat daftar kamera...
        </div>
    </div>

    <!-- ⬇️ PAGINATION CONTAINER -->
    <div id="paginationContainer" class="mt-10 flex flex-col sm:flex-row justify-between items-center gap-4 hidden">
        <span id="paginationInfo" class="text-sm text-slate-500"></span>
        <div id="paginationButtons" class="flex gap-2"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const camerasGrid = document.getElementById('camerasGrid');
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationButtons = document.getElementById('paginationButtons');

    let currentPage = 1;
    let activeCategory = 'all';
    let activeSearch = '';

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(angka || 0);

    // Keyword filter kategori (client-side)
    const CATEGORY_KEYWORDS = {
        'mirrorless': ['mirrorless', 'sony', 'fujifilm', 'lumix', 'panasonic', 'olympus', 'nikon z', 'canon eos r'],
        'dslr':       ['dslr', 'canon eos', 'nikon d'],
        'lensa':      ['lens', 'lensa', 'tamron', 'sigma'],
        'actioncam':  ['action', 'gopro', 'dji', 'insta360', 'osmo'],
    };

    // === RENDER CARD ===
    const renderCameras = (cameras) => {
        camerasGrid.innerHTML = '';

        if (!Array.isArray(cameras) || cameras.length === 0) {
            camerasGrid.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400">Tidak ada kamera yang cocok dengan filter.</div>';
            return;
        }

        cameras.forEach(cam => {
            const isAvailable = (cam.stock > 0) && (cam.status === 'available');
            const stockBadge = isAvailable
                ? '<span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-semibold">Tersedia</span>'
                : '<span class="bg-rose-100 text-rose-800 text-xs px-2.5 py-1 rounded-full font-semibold">Tidak Tersedia</span>';

            const cardHtml = `
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="h-48 bg-slate-100 flex items-center justify-center overflow-hidden relative">
                        ${cam.image_url
                            ? `<img src="${cam.image_url}" alt="${cam.name}" class="w-full h-full object-cover">`
                            : `<span class="text-xs text-slate-400">Foto Display ${cam.name}</span>`}
                        <div class="absolute top-2 right-2">${stockBadge}</div>
                    </div>
                    <div class="p-5">
                        <span class="inline-block px-2 py-1 bg-blue-50 text-[#2563EB] text-xs font-bold rounded mb-2 uppercase tracking-wide">${cam.brand || '-'}</span>
                        <h3 class="text-lg font-bold text-slate-900 mb-1 leading-tight">${cam.name || '-'}</h3>
                        <p class="text-xs text-slate-500 mb-4 line-clamp-2">${cam.description || 'Tidak ada deskripsi'}</p>

                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-100">
                            <div>
                                <span class="text-[#2563EB] font-extrabold text-lg">${formatRupiah(cam.daily_rate)}</span>
                                <span class="text-slate-400 text-xs">/hari</span>
                            </div>
                            <a href="/catalog/${cam.id}" class="bg-[#2563EB] hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-xs transition">
                                Detail Unit
                            </a>
                        </div>
                    </div>
                </div>
            `;
            camerasGrid.insertAdjacentHTML('beforeend', cardHtml);
        });
    };

    // === RENDER PAGINATION ===
    const renderPagination = (meta) => {
        if (!meta || meta.last_page <= 1) {
            paginationContainer.classList.add('hidden');
            return;
        }

        paginationContainer.classList.remove('hidden');

        const from = (meta.current_page - 1) * meta.per_page + 1;
        const to = Math.min(meta.current_page * meta.per_page, meta.total);
        paginationInfo.textContent = `Menampilkan ${from}–${to} dari ${meta.total} kamera`;

        const prevDisabled = meta.current_page === 1;
        const nextDisabled = meta.current_page === meta.last_page;

        paginationButtons.innerHTML = `
            <button ${prevDisabled ? 'disabled' : ''}
                    onclick="goToPage(${meta.current_page - 1})"
                    class="px-4 py-2 text-sm font-semibold rounded-lg border transition
                           ${prevDisabled ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'}">
                ← Prev
            </button>
            <span class="px-4 py-2 text-sm font-semibold rounded-lg bg-[#2563EB] text-white">
                ${meta.current_page} / ${meta.last_page}
            </span>
            <button ${nextDisabled ? 'disabled' : ''}
                    onclick="goToPage(${meta.current_page + 1})"
                    class="px-4 py-2 text-sm font-semibold rounded-lg border transition
                           ${nextDisabled ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'}">
                Next →
            </button>
        `;
    };

    // === LOAD DATA ===
    const loadCameras = (page = 1) => {
        currentPage = page;

        const params = new URLSearchParams({ page: page });
        if (activeSearch) params.append('search', activeSearch);

        camerasGrid.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400">Memuat katalog...</div>';

        fetch(`/api/cameras?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
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
            // Normalisasi array
            let cameras = [];
            if (Array.isArray(response)) cameras = response;
            else if (Array.isArray(response.data)) cameras = response.data;
            else if (Array.isArray(response.data?.data)) cameras = response.data.data;
            else throw new Error("Respon server bukan array");

            // Filter kategori client-side (sementara, backend belum punya field category)
            if (activeCategory !== 'all') {
                const keywords = CATEGORY_KEYWORDS[activeCategory] || [activeCategory];
                cameras = cameras.filter(cam => {
                    const text = `${cam.name || ''} ${cam.brand || ''}`.toLowerCase();
                    return keywords.some(kw => text.includes(kw));
                });
            }

            renderCameras(cameras);
            renderPagination(response.meta);
        })
        .catch(err => {
            console.error("Fetch Katalog Error:", err);
            camerasGrid.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <p class="text-rose-500 font-semibold mb-1">Gagal memuat data dari server.</p>
                    <p class="text-xs text-slate-400 font-mono">Penyebab: ${err.message}</p>
                </div>
            `;
            paginationContainer.classList.add('hidden');
        });
    };

    // === NAVIGASI HALAMAN ===
    window.goToPage = (page) => {
        if (page < 1) return;
        loadCameras(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // === SEARCH (debounce) ===
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                activeSearch = e.target.value.toLowerCase().trim();
                loadCameras(1);
            }, 400);
        });
    }

    // === FILTER BUTTON ===
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
                b.classList.remove('bg-[#1E293B]', 'text-white');
                b.classList.add('bg-blue-100', 'text-[#2563EB]', 'hover:bg-blue-200');
            });
            btn.classList.remove('bg-blue-100', 'text-[#2563EB]', 'hover:bg-blue-200');
            btn.classList.add('bg-[#1E293B]', 'text-white');

            activeCategory = btn.dataset.cat || 'all';
            loadCameras(1);
        });
    });

    // === INITIAL LOAD ===
    loadCameras(1);
});
</script>
@endpush