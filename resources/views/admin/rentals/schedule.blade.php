@extends('layouts.admin')

@section('title', 'Jadwal & Monitoring Sewa')
@section('subtitle', 'Pemantauan jadwal pemakaian unit kamera per tanggal dan deteksi keterlambatan pengembalian')

@section('content')
<!-- Metric Status Header -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Sedang Keluar (Disewa)</p>
            <h3 id="activeRentalsCount" class="text-2xl font-extrabold text-slate-900 mt-1">0 Unit</h3>
        </div>
        <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
    </div>

    <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal Ambil Hari Ini</p>
            <h3 id="pickupTodayCount" class="text-2xl font-extrabold text-amber-600 mt-1">0 Unit</h3>
        </div>
        <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terlambat Kembali (Overdue)</p>
            <h3 id="overdueCount" class="text-2xl font-extrabold text-rose-600 mt-1">0 Unit</h3>
        </div>
        <div class="p-3 bg-rose-100 rounded-lg text-rose-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
</div>

<!-- Tabel Matrix Monitoring Jadwal -->
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <h2 class="font-bold text-slate-800">Daftar Jadwal Pemakaian Kamera</h2>
        <span class="text-xs text-slate-400">Diperbarui secara real-time</span>
    </div>

    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">Unit Kamera</th>
                <th class="px-6 py-3">Penyewa (Kontak)</th>
                <th class="px-6 py-3">Jadwal Ambil</th>
                <th class="px-6 py-3">Periode Sewa</th>
                <th class="px-6 py-3">Status Jadwal</th>
            </tr>
        </thead>
        <tbody id="scheduleTableBody" class="divide-y divide-slate-200">
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">Memuat jadwal sewa kamera...</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        const tableBody = document.getElementById('scheduleTableBody');
        const today = new Date().toISOString().split('T')[0];

        const formatTime = (timeStr) => {
            if (!timeStr) return '-';
            return timeStr.substring(0, 5);
        };

        // Helper: cek apakah tanggal = hari ini
        const isToday = (dateStr) => {
            if (!dateStr) return false;
            return dateStr === today;
        };

        // Fetch dengan fallback
        const loadSchedule = () => {
            fetch('/api/admin/rentals/schedule', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => {
                if (!res.ok) {
                    // Fallback ke endpoint /api/rentals
                    return fetch('/api/rentals', {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    });
                }
                return res;
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
                // Normalisasi response jadi array
                let rentals = [];
                if (Array.isArray(response)) rentals = response;
                else if (Array.isArray(response.data)) rentals = response.data;
                else if (Array.isArray(response.data?.data)) rentals = response.data.data;

                // Filter hanya transaksi relevan (paid & picked_up)
                const activeSchedules = rentals.filter(r => ['paid', 'picked_up'].includes(r.status));

                let activeCount = 0;
                let overdueCount = 0;
                let pickupTodayCount = 0;

                tableBody.innerHTML = '';

                if (activeSchedules.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Tidak ada jadwal sewa aktif saat ini.</td></tr>';
                    document.getElementById('activeRentalsCount').textContent = '0 Unit';
                    document.getElementById('overdueCount').textContent = '0 Unit';
                    document.getElementById('pickupTodayCount').textContent = '0 Unit';
                    return;
                }

                activeSchedules.forEach(rental => {
                    const camera = rental.camera || { name: '-' };
                    const user = rental.user || { name: '-', phone: '-' };

                    const isOverdue = rental.status === 'picked_up' && rental.end_date < today;
                    const isPickupToday = rental.status === 'paid' && isToday(rental.pickup_date);

                    if (rental.status === 'picked_up') activeCount++;
                    if (isOverdue) overdueCount++;
                    if (isPickupToday) pickupTodayCount++;

                    // Badge status
                    let scheduleBadge = '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-blue-100 text-blue-700">Sedang Disewa</span>';
                    let rowStyle = 'hover:bg-slate-50';

                    if (rental.status === 'paid') {
                        scheduleBadge = isPickupToday
                            ? '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-amber-100 text-amber-700 animate-pulse">🔔 SIAP DIAMBIL HARI INI</span>'
                            : '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-emerald-100 text-emerald-700">Menunggu Pick Up</span>';
                        if (isPickupToday) rowStyle = 'bg-amber-50/50 hover:bg-amber-50';
                    } else if (isOverdue) {
                        scheduleBadge = '<span class="px-2.5 py-1 text-xs rounded-full font-semibold bg-rose-100 text-rose-700 animate-pulse">⚠️ OVERDUE (TERLAMBAT)</span>';
                        rowStyle = 'bg-rose-50/50 hover:bg-rose-50';
                    }

                    // ⬇️ INFO JADWAL AMBIL
                    const pickupDate = rental.pickup_date || null;
                    const pickupTime = formatTime(rental.pickup_time);
                    const pickupMethod = rental.pickup_method === 'delivery' ? 'Dikirim' : 'Ambil di Toko';

                    let pickupHtml = '<span class="text-xs text-slate-400">-</span>';
                    if (pickupDate) {
                        pickupHtml = `
                            <div class="text-xs">
                                <div class="font-semibold ${isPickupToday ? 'text-amber-600' : 'text-slate-700'}">
                                    ${isPickupToday ? '🔔 ' : ''}${pickupDate}
                                </div>
                                <div class="text-slate-500">${pickupTime} • ${pickupMethod}</div>
                            </div>
                        `;
                    }

                    const tr = document.createElement('tr');
                    tr.className = `${rowStyle} transition`;
                    tr.innerHTML = `
                        <td class="px-6 py-4 font-bold text-slate-900">${camera.name}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">${user.name}</div>
                            <div class="text-xs text-slate-400">${user.phone || '-'}</div>
                        </td>
                        <td class="px-6 py-4">${pickupHtml}</td>
                        <td class="px-6 py-4 text-xs">
                            <div>Mulai: <span class="font-medium text-slate-700">${rental.start_date}</span></div>
                            <div>Kembali: <span class="font-bold ${isOverdue ? 'text-rose-600' : 'text-slate-700'}">${rental.end_date}</span></div>
                        </td>
                        <td class="px-6 py-4">${scheduleBadge}</td>
                    `;
                    tableBody.appendChild(tr);
                });

                document.getElementById('activeRentalsCount').textContent = `${activeCount} Unit`;
                document.getElementById('overdueCount').textContent = `${overdueCount} Unit`;
                document.getElementById('pickupTodayCount').textContent = `${pickupTodayCount} Unit`;
            })
            .catch(err => {
                console.error("Error loading schedule:", err);
                tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-rose-500 font-medium">Gagal memuat jadwal: ${err.message}</td></tr>`;
            });
        };

        loadSchedule();
    });
</script>
@endpush