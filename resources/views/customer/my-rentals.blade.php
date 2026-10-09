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

<!-- Modal Nota Digital -->
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

            <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>Order ID:</strong> <span id="notaOrderId"></span></p>
                <p><strong>Penyewa:</strong> <span id="notaUserName">Loading...</span> (<span id="notaUserPhone">-</span>)</p>
                <p><strong>Unit Equipment:</strong> <span id="notaCameraName"></span></p>
                <p><strong>Periode Sewa:</strong> <span id="notaStartDate"></span> s/d <span id="notaEndDate"></span></p>
            </div>

            <!-- ⬇️ INFO PENGAMBILAN / PENGIRIMAN ⬇️ -->
            <div id="notaPickupSection" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <p id="notaPickupTitle" class="text-[10px] font-bold text-blue-900 uppercase mb-2">📦 Info Pengambilan</p>
                <div class="space-y-1 text-xs text-gray-700">
                    <p><strong>Metode:</strong> <span id="notaPickupMethod">-</span></p>
                    <p><strong>Tanggal:</strong> <span id="notaPickupDate">-</span></p>
                    <p><strong>Jam:</strong> <span id="notaPickupTime">-</span></p>
                    <p id="notaDeliveryFeeRow" class="hidden">
                        <strong>Biaya Kirim:</strong> <span id="notaDeliveryFee" class="text-purple-700 font-semibold">-</span>
                    </p>
                    <p id="notaPickupNotesRow" class="hidden">
                        <strong>Catatan:</strong> <span id="notaPickupNotes" class="italic">-</span>
                    </p>
                </div>
            </div>
            <!-- ⬆️ END INFO ⬆️ -->

            <!-- Rincian Biaya -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                <p class="text-[10px] font-bold text-gray-500 uppercase mb-2">Rincian Biaya</p>
                <div class="space-y-1 text-xs text-gray-700">
                    <div class="flex justify-between">
                        <span>Harga Sewa</span>
                        <span id="notaRentalPrice" class="font-semibold">-</span>
                    </div>
                    <div id="notaDeliveryFeeRow2" class="flex justify-between hidden">
                        <span>Biaya Kirim</span>
                        <span id="notaDeliveryFee2" class="text-purple-700 font-semibold">-</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-1 mt-1">
                        <span class="font-bold">TOTAL</span>
                        <span id="notaTotal" class="font-bold text-[#10B981]">-</span>
                    </div>
                </div>
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

@push('scripts')
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
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(angka || 0);

        const statusConfig = {
            'pending_payment': { label: 'MENUNGGU BAYAR', color: 'bg-amber-100 text-amber-800' },
            'paid':            { label: 'LUNAS / SIAP DIAMBIL', color: 'bg-emerald-100 text-emerald-800' },
            'picked_up':       { label: 'SEDANG DISEWA', color: 'bg-blue-100 text-blue-800' },
            'returned':        { label: 'SELESAI', color: 'bg-gray-100 text-gray-800' },
            'cancelled':       { label: 'DIBATALKAN', color: 'bg-red-100 text-red-800' },
        };

        const renderRentals = (rentals) => {
            container.innerHTML = '';

            if (!Array.isArray(rentals) || rentals.length === 0) {
                container.innerHTML = '<div class="text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border border-gray-100">Belum ada transaksi di kategori ini.</div>';
                return;
            }

            rentals.forEach(rental => {
                const config = statusConfig[rental.status] || { label: rental.status, color: 'bg-gray-100 text-gray-800' };
                const camera = rental.camera || { name: 'Kamera Dihapus' };

                let actionHtml = '';
                if (rental.status === 'pending_payment') {
                    actionHtml = `<a href="/booking/${rental.id}" class="inline-block bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-semibold px-4 py-2 rounded transition">Bayar Sekarang</a>`;
                } else if (['paid', 'picked_up', 'returned'].includes(rental.status)) {
                    const rentalDataStr = encodeURIComponent(JSON.stringify(rental));
                    actionHtml = `<button type="button" data-nota="${rentalDataStr}" class="btn-nota inline-block border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 text-sm font-semibold px-4 py-2 rounded transition">Lihat Nota Digital</button>`;
                }

                // Format jam (10:00:00 → 10:00)
                const pickupTime = rental.pickup_time ? rental.pickup_time.substring(0, 5) : '-';
                const pickupDate = rental.pickup_date || '-';
                const isDelivery = rental.pickup_method === 'delivery';

                // ⬇️ INFO PICKUP / DELIVERY DI CARD
                const pickupInfoHtml = isDelivery
                    ? `<p class="text-xs text-purple-600 font-semibold mb-2">
                         🚚 Dikirim (${rental.delivery_location === 'dalam_kota' ? 'Dalam Kota' : 'Luar Kota'}): 
                         <strong>${pickupDate} ${pickupTime}</strong>
                       </p>`
                    : `<p class="text-xs text-slate-500 mb-2">
                         📦 Ambil: <strong>${pickupDate} ${pickupTime}</strong>
                       </p>`;

                const cardHtml = `
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="hidden md:block w-2 h-full min-h-[4rem] rounded-full ${config.color.split(' ')[0]}"></div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-900">#ORD-${rental.id}</span>
                                    <span class="text-xs px-2 py-1 rounded font-semibold ${config.color}">${config.label}</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">${camera.name}</h3>
                                <p class="text-sm text-gray-500 mb-1">Tanggal Sewa: ${rental.start_date} s/d ${rental.end_date} (${rental.total_days} Hari)</p>
                                ${pickupInfoHtml}
                                <p class="text-[#2563EB] font-bold">${formatRupiah(rental.total_price)}</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex flex-col items-end">${actionHtml}</div>
                    </div>`;
                container.insertAdjacentHTML('beforeend', cardHtml);
            });

            // Event delegation untuk tombol nota
            container.querySelectorAll('.btn-nota').forEach(btn => {
                btn.addEventListener('click', () => {
                    const data = JSON.parse(decodeURIComponent(btn.dataset.nota));
                    showNota(data);
                });
            });
        };

        // === FETCH RENTALS ===
        fetch('/api/rentals', {
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
            let rentals = [];
            if (Array.isArray(response)) rentals = response;
            else if (Array.isArray(response.data)) rentals = response.data;
            else if (Array.isArray(response.data?.data)) rentals = response.data.data;

            allRentals = rentals.sort((a, b) => b.id - a.id);
            renderRentals(allRentals);
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `<div class="text-center py-12 text-red-500 font-semibold">Gagal memuat riwayat transaksi: ${err.message}</div>`;
        });

        // === FILTER TABS ===
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('border-[#2563EB]', 'text-[#2563EB]');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                tab.classList.remove('border-transparent', 'text-gray-500');
                tab.classList.add('border-[#2563EB]', 'text-[#2563EB]');

                const status = tab.getAttribute('data-status');
                if (status === 'all') renderRentals(allRentals);
                else renderRentals(allRentals.filter(r => r.status === status));
            });
        });

        // === FUNGSI MODAL NOTA ===
        const notaModal = document.getElementById('notaModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const printBtn = document.getElementById('printBtn');

        function showNota(rental) {
            const isDelivery = rental.pickup_method === 'delivery';
            const deliveryFee = parseFloat(rental.delivery_fee || 0);
            const totalPrice = parseFloat(rental.total_price || 0);
            const rentalPrice = totalPrice - deliveryFee;

            // Data dasar
            document.getElementById('notaOrderId').textContent = `ORD-${rental.id}`;
            document.getElementById('notaCameraName').textContent = rental.camera ? rental.camera.name : '-';
            document.getElementById('notaStartDate').textContent = rental.start_date;
            document.getElementById('notaEndDate').textContent = rental.end_date;

            // Rincian biaya
            document.getElementById('notaRentalPrice').textContent = formatRupiah(rentalPrice);
            document.getElementById('notaTotal').textContent = formatRupiah(totalPrice);

            // Section pickup/delivery
            const pickupSection = document.getElementById('notaPickupSection');
            const pickupTitle = document.getElementById('notaPickupTitle');

            if (isDelivery) {
                pickupSection.classList.remove('bg-blue-50', 'border-blue-200');
                pickupSection.classList.add('bg-purple-50', 'border-purple-200');
                pickupTitle.classList.remove('text-blue-900');
                pickupTitle.classList.add('text-purple-900');
                pickupTitle.textContent = '🚚 Info Pengiriman';

                document.getElementById('notaPickupMethod').textContent =
                    `Dikirim - ${rental.delivery_location === 'dalam_kota' ? 'Dalam Kota' : 'Luar Kota'}`;

                // Tampilkan biaya kirim
                document.getElementById('notaDeliveryFeeRow').classList.remove('hidden');
                document.getElementById('notaDeliveryFee').textContent = `+${formatRupiah(deliveryFee)}`;
                document.getElementById('notaDeliveryFeeRow2').classList.remove('hidden');
                document.getElementById('notaDeliveryFee2').textContent = `+${formatRupiah(deliveryFee)}`;
            } else {
                pickupSection.classList.remove('bg-purple-50', 'border-purple-200');
                pickupSection.classList.add('bg-blue-50', 'border-blue-200');
                pickupTitle.classList.remove('text-purple-900');
                pickupTitle.classList.add('text-blue-900');
                pickupTitle.textContent = '📦 Info Pengambilan';

                document.getElementById('notaPickupMethod').textContent = 'Ambil di Toko';

                // Sembunyikan biaya kirim
                document.getElementById('notaDeliveryFeeRow').classList.add('hidden');
                document.getElementById('notaDeliveryFeeRow2').classList.add('hidden');
            }

            // Jadwal
            const pickupTime = rental.pickup_time ? rental.pickup_time.substring(0, 5) : '-';
            document.getElementById('notaPickupDate').textContent = rental.pickup_date || '-';
            document.getElementById('notaPickupTime').textContent = pickupTime;

            // Catatan
            const notesRow = document.getElementById('notaPickupNotesRow');
            if (rental.pickup_notes) {
                document.getElementById('notaPickupNotes').textContent = rental.pickup_notes;
                notesRow.classList.remove('hidden');
            } else {
                notesRow.classList.add('hidden');
            }

            // Data user
            if (rental.user) {
                document.getElementById('notaUserName').textContent = rental.user.name;
                document.getElementById('notaUserPhone').textContent = rental.user.phone || '-';
            } else {
                fetch('/api/auth/me', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    const u = data.user || data.data || {};
                    document.getElementById('notaUserName').textContent = u.name || '-';
                    document.getElementById('notaUserPhone').textContent = u.phone || '-';
                })
                .catch(() => {});
            }

            notaModal.classList.remove('hidden');
        }

        // Close modal
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                notaModal.classList.add('hidden');
            });
        }

        notaModal.addEventListener('click', (e) => {
            if (e.target === notaModal) notaModal.classList.add('hidden');
        });

        // Print
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                const printContent = document.getElementById('notaPrintArea').innerHTML;
                const printWindow = window.open('', '', 'height=600,width=500');
                printWindow.document.write('<html><head><title>Cetak Nota CamRent</title>');
                printWindow.document.write('<script src="https://cdn.tailwindcss.com"><\/script>');
                printWindow.document.write('</head><body class="p-6">');
                printWindow.document.write(printContent);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
            });
        }
    });
</script>
@endpush