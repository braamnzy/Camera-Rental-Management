@extends('layouts.admin')

@section('title', 'Kelola Transaksi Penyewaan')
@section('subtitle', 'Verifikasi status pembayaran, penyerahan unit (pick up), dan pengembalian kamera (return)')

@section('content')
<!-- Filter & Status Tabs -->
<div class="mb-6 flex flex-wrap gap-2">
    <button onclick="filterStatus('all', this)" class="status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-[#2563EB] text-white transition" data-status="all">Semua Transaksi</button>
    <button onclick="filterStatus('pending_payment', this)" class="status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-status="pending_payment">Menunggu Bayar</button>
    <button onclick="filterStatus('paid', this)" class="status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-status="paid">Lunas (Siap Ambil)</button>
    <button onclick="filterStatus('picked_up', this)" class="status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-status="picked_up">Sedang Disewa</button>
    <button onclick="filterStatus('returned', this)" class="status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-status="returned">Selesai</button>
</div>

<!-- Tabel Transaksi -->
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">ID / Penyewa</th>
                <th class="px-6 py-3">Unit Kamera</th>
                <th class="px-6 py-3">Tanggal Sewa</th>
                <th class="px-6 py-3">Jadwal Ambil</th>
                <th class="px-6 py-3">Total Bayar</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-right">Aksi Status</th>
            </tr>
        </thead>
        <tbody id="rentalsTableBody" class="divide-y divide-slate-200">
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-slate-400 font-medium">Memuat data transaksi...</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        const tableBody = document.getElementById('rentalsTableBody');
        let allRentals = [];

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(angka || 0);

        const statusBadges = {
            'pending_payment': '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-amber-100 text-amber-700">Menunggu Bayar</span>',
            'paid':            '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-emerald-100 text-emerald-700">Lunas (Siap Ambil)</span>',
            'picked_up':       '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-blue-100 text-blue-700">Sedang Disewa</span>',
            'returned':        '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-slate-100 text-slate-700">Selesai</span>',
            'cancelled':       '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-rose-100 text-rose-700">Dibatalkan</span>'
        };

        const loadRentals = () => {
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

                allRentals = rentals;
                renderRentals(allRentals);
            })
            .catch(err => {
                console.error("Error loading rentals:", err);
                tableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-rose-500 font-medium">Gagal memuat data transaksi: ${err.message}</td></tr>`;
            });
        };

        // Helper: cek apakah tanggal tertentu = hari ini
        const isToday = (dateStr) => {
            if (!dateStr) return false;
            const today = new Date().toISOString().split('T')[0];
            return dateStr === today;
        };

        // Helper: format jam (10:00:00 → 10:00)
        const formatTime = (timeStr) => {
            if (!timeStr) return '-';
            return timeStr.substring(0, 5);
        };

        const renderRentals = (rentals) => {
            if (!Array.isArray(rentals) || rentals.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">Tidak ada data transaksi ditemukan.</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            rentals.forEach(rental => {
                const user = rental.user || { name: 'Customer', phone: '-' };
                const camera = rental.camera || { name: '-' };
                const badge = statusBadges[rental.status] || `<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-slate-100">${rental.status}</span>`;

                // ⬇️ INFO JADWAL AMBIL
                const pickupDate = rental.pickup_date || null;
                const pickupTime = formatTime(rental.pickup_time);
                const pickupMethod = rental.pickup_method === 'delivery' ? 'Dikirim' : 'Ambil di Toko';
                const isPickupToday = isToday(pickupDate);

                let pickupHtml = '<span class="text-xs text-slate-400">-</span>';
                if (pickupDate) {
                    pickupHtml = `
                        <div class="text-xs">
                            <div class="font-semibold ${isPickupToday ? 'text-amber-600' : 'text-slate-700'}">
                                ${isPickupToday ? '🔔 ' : ''}${pickupDate}
                            </div>
                            <div class="text-slate-500">
                                ${pickupTime} • ${pickupMethod}
                            </div>
                            ${rental.pickup_notes ? `<div class="text-[10px] italic text-slate-400 mt-0.5 truncate max-w-[150px]" title="${rental.pickup_notes}">"${rental.pickup_notes}"</div>` : ''}
                        </div>
                    `;
                }

                // ⬇️ AKSI BUTTON
                let actionButtons = '-';
                if (rental.status === 'paid') {
                    const urgency = isPickupToday ? 'bg-amber-600 hover:bg-amber-700 animate-pulse' : 'bg-blue-600 hover:bg-blue-700';
                    actionButtons = `<button onclick="updateStatus(${rental.id}, 'picked_up')" class="px-3 py-1.5 ${urgency} text-white text-xs font-semibold rounded transition">Serahkan Unit</button>`;
                } else if (rental.status === 'picked_up') {
                    actionButtons = `<button onclick="updateStatus(${rental.id}, 'returned')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded transition">Konfirmasi Return</button>`;
                } else if (rental.status === 'pending_payment') {
                    actionButtons = `<button onclick="updateStatus(${rental.id}, 'cancelled')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded transition">Batalkan</button>`;
                }

                const tr = document.createElement('tr');
                tr.className = isPickupToday && rental.status === 'paid' 
                    ? 'bg-amber-50/50 hover:bg-amber-50 transition' 
                    : 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900">#TRX-${rental.id}</div>
                        <div class="text-xs text-slate-500">${user.name} (${user.phone || '-'})</div>
                    </td>
                    <td class="px-6 py-4 font-semibold text-slate-800">${camera.name}</td>
                    <td class="px-6 py-4 text-xs">
                        <div>Mulai: <span class="font-medium text-slate-700">${rental.start_date}</span></div>
                        <div>Selesai: <span class="font-medium text-slate-700">${rental.end_date}</span> (${rental.total_days || 1} Hari)</div>
                    </td>
                    <td class="px-6 py-4">${pickupHtml}</td>
                    <td class="px-6 py-4 font-bold text-[#2563EB]">${formatRupiah(rental.total_price)}</td>
                    <td class="px-6 py-4">${badge}</td>
                    <td class="px-6 py-4 text-right">${actionButtons}</td>
                `;
                tableBody.appendChild(tr);
            });
        };

        window.updateStatus = (id, newStatus) => {
            if (!confirm(`Ubah status transaksi #${id} menjadi ${newStatus.toUpperCase()}?`)) return;

            fetch(`/api/rentals/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Status berhasil diperbarui.');
                loadRentals();
            })
            .catch(err => {
                console.error("Error updating status:", err);
                alert('Gagal memperbarui status transaksi.');
            });
        };

        // Perbaikan: event.target diganti dengan parameter `btn`
        window.filterStatus = (status, btn) => {
            document.querySelectorAll('.status-filter-btn').forEach(b => {
                b.className = 'status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
            });
            if (btn) {
                btn.className = 'status-filter-btn px-4 py-2 text-xs font-semibold rounded-lg bg-[#2563EB] text-white transition';
            }

            if (status === 'all') {
                renderRentals(allRentals);
            } else {
                renderRentals(allRentals.filter(r => r.status === status));
            }
        };

        loadRentals();
    });
</script>
@endpush