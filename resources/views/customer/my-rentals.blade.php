@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Riwayat Transaksi Sewa Saya</h1>

    <!-- Tab Filter Status -->
    <div class="flex gap-2 overflow-x-auto border-b border-gray-200 pb-2 mb-8" id="statusFilters">
        <button data-status="all" class="filter-tab px-4 py-2 border-b-2 border-[#2563EB] text-[#2563EB] font-semibold whitespace-nowrap transition">SEMUA PESANAN</button>
        <button data-status="pending_payment" class="filter-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">MENUNGGU BAYAR</button>
        <button data-status="paid" class="filter-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">SIAP DIAMBIL</button>
        <button data-status="picked_up" class="filter-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">SEDANG DISEWA</button>
        <button data-status="returned" class="filter-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap transition">SELESAI</button>
    </div>

    <!-- Container Daftar Rental -->
    <div id="rentalsContainer" class="space-y-6">
        <div class="text-center py-12 text-gray-500">Memuat riwayat transaksi...</div>
    </div>
</div>

<!-- Modal untuk Nota Digital (Hidden by default) -->
<div id="notaModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 overflow-y-auto flex items-center justify-center px-4">
    <div class="bg-white max-w-md w-full rounded-xl shadow-2xl p-6 relative">
        <!-- Tombol Tutup -->
        <button id="closeModalBtn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Area Cetak Nota -->
        <div id="notaPrintArea" class="border border-dashed border-gray-300 p-6 rounded-lg mt-2">
            <div class="text-center border-b pb-4 mb-4">
                <h2 class="text-xl font-bold text-gray-900">STORE NOTA - CAMRENT</h2>
                <p class="text-xs text-gray-500">Jl. Kamera Utama No. 42, Purwokerto</p>
            </div>

            <div class="space-y-2 text-sm text-gray-700 mb-6">
                <p><strong>Order ID:</strong> <span id="notaOrderId"></span></p>
                <p><strong>Penyewa:</strong> <span id="notaUserName"></span> (<span id="notaUserPhone"></span>)</p>
                <p><strong>Unit Equipment:</strong> <span id="notaCameraName"></span></p>
                <p><strong>Periode Sewa:</strong> <span id="notaStartDate"></span> s/d <span id="notaEndDate"></span></p>
            </div>

            <div class="bg-gray-50 p-4 rounded text-center">
                <p class="text-xs text-gray-500 mb-1">TOTAL LUNAS (MIDTRANS SNAP):</p>
                <p class="text-2xl font-bold text-[#10B981]" id="notaTotal"></p>
                <p class="text-xs font-semibold text-green-700 mt-1 bg-green-100 py-1 rounded">PAID / SETTLEMENT</p>
            </div>

            <p class="text-xs text-center mt-6 text-gray-400">
                Tunjukkan nota digital ini beserta KTP asli saat pengambilan unit di store CamRent.
            </p>
        </div>

        <div class="mt-6 flex gap-3">
            <button id="printBtn" class="w-full bg-[#1E293B] hover:bg-gray-800 text-white font-semibold py-2 rounded shadow transition">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        const container = document.getElementById('rentalsContainer');
        const tabs = document.querySelectorAll('.filter-tab');
        let allRentals = [];

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);

        // Pemetaan warna dan teks badge berdasarkan status backend
        const statusConfig = {
            'pending_payment': {
                label: 'MENUNGGU BAYAR',
                color: 'bg-amber-100 text-amber-800'
            },
            'paid': {
                label: 'LUNAS / SIAP DIAMBIL',
                color: 'bg-emerald-100 text-emerald-800'
            },
            'picked_up': {
                label: 'SEDANG DISEWA',
                color: 'bg-blue-100 text-blue-800'
            },
            'returned': {
                label: 'SELESAI',
                color: 'bg-gray-100 text-gray-800'
            },
            'cancelled': {
                label: 'DIBATALKAN',
                color: 'bg-red-100 text-red-800'
            }
        };

        // Fungsi Render Daftar Rental
        const renderRentals = (rentals) => {
            container.innerHTML = '';

            if (rentals.length === 0) {
                container.innerHTML = '<div class="text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border border-gray-100">Belum ada transaksi di kategori ini.</div>';
                return;
            }

            rentals.forEach(rental => {
                const config = statusConfig[rental.status] || {
                    label: rental.status,
                    color: 'bg-gray-100 text-gray-800'
                };
                const camera = rental.camera || {
                    name: 'Kamera Dihapus'
                }; // Safeguard jika relasi kosong

                // Atur Tombol Aksi berdasarkan status
                let actionHtml = '';
                if (rental.status === 'pending_payment') {
                    actionHtml = `<a href="/booking/${rental.id}" class="inline-block bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold px-4 py-2 rounded transition">Bayar Sekarang</a>`;
                } else if (rental.status === 'paid' || rental.status === 'picked_up' || rental.status === 'returned') {
                    // Konversi objek JSON ke string agar aman disisipkan ke HTML
                    const rentalDataStr = encodeURIComponent(JSON.stringify(rental));
                    actionHtml = `<button onclick="showNota('${rentalDataStr}')" class="inline-block border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 text-sm font-semibold px-4 py-2 rounded transition">Lihat Nota Digital</button>`;
                }

                const cardHtml = `
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <!-- Indikator Warna Status di Kiri -->
                        <div class="hidden md:block w-2 h-full min-h-[4rem] rounded-full ${config.color.split(' ')[0]}"></div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900">#ORD-${rental.id}</span>
                                <span class="text-xs px-2 py-1 rounded font-semibold ${config.color}">${config.label}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">${camera.name}</h3>
                            <p class="text-sm text-gray-500 mb-2">Tanggal Sewa: ${rental.start_date} s/d ${rental.end_date} (${rental.total_days} Hari)</p>
                            <p class="text-[#2563EB] font-bold">${formatRupiah(rental.total_price)}</p>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0 flex flex-col items-end">
                        ${actionHtml}
                    </div>
                </div>
            `;
                container.innerHTML += cardHtml;
            });
        };

        // Ambil Data dari API
        fetch('/api/rentals', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(data => {
                allRentals = data.data || data;

                // Urutkan dari yang terbaru (opsional, asumsikan backend sudah mengurutkan)
                allRentals.sort((a, b) => b.id - a.id);

                renderRentals(allRentals);
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="text-center py-12 text-red-500">Gagal memuat riwayat transaksi dari server.</div>';
            });

        // Logika Filter Tab
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                // Reset style semua tab
                tabs.forEach(t => {
                    t.classList.remove('border-[#2563EB]', 'text-[#2563EB]');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                // Aktifkan tab yang diklik
                const btn = e.target;
                btn.classList.remove('border-transparent', 'text-gray-500');
                btn.classList.add('border-[#2563EB]', 'text-[#2563EB]');

                const status = btn.getAttribute('data-status');
                if (status === 'all') {
                    renderRentals(allRentals);
                } else {
                    const filtered = allRentals.filter(r => r.status === status);
                    renderRentals(filtered);
                }
            });
        });
    });

    // Fungsi Global untuk Menampilkan Modal Nota
    window.showNota = function(rentalDataStr) {
        const rental = JSON.parse(decodeURIComponent(rentalDataStr));
        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);

        // Ambil data user dari localStorage token atau dari objek rental (jika backend memuat relasi user)
        // Untuk nota sederhana, kita panggil API me() lagi, atau asumsikan nama diambil dari API.
        const token = localStorage.getItem('token');

        document.getElementById('notaOrderId').textContent = `ORD-${rental.id}`;
        document.getElementById('notaCameraName').textContent = rental.camera ? rental.camera.name : '-';
        document.getElementById('notaStartDate').textContent = rental.start_date;
        document.getElementById('notaEndDate').textContent = rental.end_date;
        document.getElementById('notaTotal').textContent = formatRupiah(rental.total_price);

        // Tarik nama penyewa dari endpoint me
        fetch('/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.user) {
                    document.getElementById('notaUserName').textContent = data.user.name;
                    document.getElementById('notaUserPhone').textContent = data.user.phone || '-';
                }
            });

        document.getElementById('notaModal').classList.remove('hidden');
    };

    // Menutup Modal
    document.getElementById('closeModalBtn').addEventListener('click', () => {
        document.getElementById('notaModal').classList.add('hidden');
    });

    // Fungsi Cetak (Print HTML Div)
    document.getElementById('printBtn').addEventListener('click', () => {
        const printContent = document.getElementById('notaPrintArea').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = `
        <div style="padding: 40px; font-family: sans-serif; max-width: 500px; margin: auto;">
            ${printContent}
        </div>
    `;
        window.print();
        // Kembalikan ke UI awal setelah selesai nge-print
        document.body.innerHTML = originalContent;
        window.location.reload();
    });
</script>