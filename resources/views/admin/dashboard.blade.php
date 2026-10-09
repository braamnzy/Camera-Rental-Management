@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('subtitle', 'Ikhtisar aktivitas bisnis dan pendapatan FrameFlow')

@section('content')

<!-- Metric Cards Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Pendapatan -->
    <div class="bg-[#F8FAFC] border border-slate-200 p-6 rounded-xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pendapatan</p>
                <h3 id="statTotalRevenue" class="text-2xl font-extrabold text-slate-900 mt-2">
                    Rp 0
                </h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg text-[#2563EB]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Penyewaan Aktif -->
    <div class="bg-[#F8FAFC] border border-slate-200 p-6 rounded-xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penyewaan Aktif</p>
                <h3 id="statActiveRentals" class="text-2xl font-extrabold text-slate-900 mt-2">
                    0 Unit
                </h3>
            </div>
            <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Menunggu Pembayaran -->
    <div class="bg-[#F8FAFC] border border-slate-200 p-6 rounded-xl shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Pembayaran</p>
                <h3 id="statPendingRentals" class="text-2xl font-extrabold text-slate-900 mt-2">
                    0 Transaksi
                </h3>
            </div>
            <div class="p-3 bg-rose-100 rounded-lg text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- SECTION BARU: Verifikasi KTP User Baru -->
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm mb-8">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-[#F8FAFC]">
        <div>
            <h2 class="font-bold text-slate-800">Persetujuan Akun User Baru (KTP)</h2>
            <p class="text-xs text-slate-500">Tinjau foto KTP dan berikan persetujuan (ACC) akun customer</p>
        </div>
        <span id="pendingUsersBadge" class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">0 Pending</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3">Nama Lengkap</th>
                    <th class="px-6 py-3">Email & No HP</th>
                    <th class="px-6 py-3">Foto KTP</th>
                    <th class="px-6 py-3">Tanggal Daftar</th>
                    <th class="px-6 py-3 text-center">Aksi (ACC)</th>
                </tr>
            </thead>
            <tbody id="pendingUsersBody" class="divide-y divide-slate-200">
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">Memuat data verifikasi KTP...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Transaksi Terbaru Table -->
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-[#F8FAFC]">
        <h2 class="font-bold text-slate-800">Transaksi Terbaru</h2>
        <a href="/admin/rentals" class="text-xs font-semibold text-[#2563EB] hover:underline">Lihat Semua →</a>
    </div>

    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs text-slate-500 uppercase border-b border-slate-200">
            <tr>
                <th class="px-6 py-3">Penyewa</th>
                <th class="px-6 py-3">Kamera</th>
                <th class="px-6 py-3">Tanggal Sewa</th>
                <th class="px-6 py-3">Total Bayar</th>
                <th class="px-6 py-3">Status</th>
            </tr>
        </thead>
        <tbody id="recentRentalsBody" class="divide-y divide-slate-200">
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">Memuat data dashboard...</td>
            </tr>
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        const tableBody = document.getElementById('recentRentalsBody');
        const pendingUsersBody = document.getElementById('pendingUsersBody');
        const pendingUsersBadge = document.getElementById('pendingUsersBadge');

        if (!token || token === 'undefined' || token === 'null') {
            localStorage.removeItem('token');
            window.location.href = '/login';
            return;
        }

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka || 0);

        // --- 1. MOUNT DATA VERIFIKASI KTP USER PENDING ---
        const fetchPendingUsers = () => {
            fetch('/api/admin/users/pending', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(response => {
                const users = response.data || [];
                pendingUsersBadge.textContent = `${users.length} Pending`;
                pendingUsersBody.innerHTML = '';

                if (users.length === 0) {
                    pendingUsersBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Tidak ada pendaftaran user baru yang perlu diverifikasi.</td></tr>';
                    return;
                }

                users.forEach(user => {
                    const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
                    const ktpLink = user.ktp_image 
                        ? `<a href="${user.ktp_image}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                             Lihat KTP ↗
                           </a>`
                        : `<span class="text-xs text-slate-400">Tidak ada KTP</span>`;

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition';
                    tr.innerHTML = `
                        <td class="px-6 py-4 font-semibold text-slate-900">${user.name}</td>
                        <td class="px-6 py-4">
                            <div class="text-slate-800 font-medium">${user.email}</div>
                            <div class="text-xs text-slate-400 mt-0.5">${user.phone || '-'}</div>
                        </td>
                        <td class="px-6 py-4">${ktpLink}</td>
                        <td class="px-6 py-4 text-xs text-slate-500">${createdDate}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="approveUser(${user.id})" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                                    Setujui (ACC)
                                </button>
                                <button onclick="rejectUser(${user.id})" class="px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-semibold hover:bg-rose-700 transition">
                                    Tolak
                                </button>
                            </div>
                        </td>
                    `;
                    pendingUsersBody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error("Pending Users Fetch Error:", err);
                pendingUsersBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-rose-500">Gagal memuat data verifikasi user.</td></tr>';
            });
        };

        // Helper Eksekusi ACC / Tolak
        window.approveUser = (userId) => {
            if (!confirm('Apakah Anda yakin ingin menyetujui akun pengguna ini?')) return;
            fetch(`/api/admin/users/${userId}/approve`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Akun berhasil disetujui');
                fetchPendingUsers();
            })
            .catch(err => alert('Gagal memproses persetujuan akun.'));
        };

        window.rejectUser = (userId) => {
            if (!confirm('Apakah Anda yakin ingin menolak akun pengguna ini?')) return;
            fetch(`/api/admin/users/${userId}/reject`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Akun telah ditolak');
                fetchPendingUsers();
            })
            .catch(err => alert('Gagal memproses penolakan akun.'));
        };

        // Jalankan fetch pending users
        fetchPendingUsers();


        // --- 2. MOUNT DATA TRANSAKSI TERBARU & METRIC CARDS ---
        const statusConfig = {
            'pending_payment': { label: 'PENDING PAYMENT', class: 'bg-amber-100 text-amber-700' },
            'paid': { label: 'PAID (SIAP AMBIL)', class: 'bg-emerald-100 text-emerald-700' },
            'picked_up': { label: 'PICKED UP (DISEWA)', class: 'bg-blue-100 text-blue-700' },
            'returned': { label: 'RETURNED (SELESAI)', class: 'bg-slate-100 text-slate-700' },
            'cancelled': { label: 'CANCELLED', class: 'bg-rose-100 text-rose-700' }
        };

        fetch('/api/rentals', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(async res => {
            if (res.status === 401) {
                localStorage.removeItem('token');
                alert('Sesi telah berakhir. Silakan login kembali.');
                window.location.href = '/login';
                return null;
            }
            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.message || `HTTP ${res.status}: Gagal memuat API /api/rentals`);
            }
            return res.json();
        })
        .then(response => {
            if (!response) return;

            const rentals = Array.isArray(response) ? response : (response.data || []);

            let totalRevenue = 0;
            let activeRentalsCount = 0;
            let pendingRentalsCount = 0;

            rentals.forEach(r => {
                if (['paid', 'picked_up', 'returned'].includes(r.status)) {
                    totalRevenue += parseFloat(r.total_price || 0);
                }
                if (r.status === 'picked_up') {
                    activeRentalsCount += (parseInt(r.quantity) || 1);
                }
                if (r.status === 'pending_payment') {
                    pendingRentalsCount++;
                }
            });

            document.getElementById('statTotalRevenue').textContent = formatRupiah(totalRevenue);
            document.getElementById('statActiveRentals').textContent = `${activeRentalsCount} Unit`;
            document.getElementById('statPendingRentals').textContent = `${pendingRentalsCount} Transaksi`;

            const recentRentals = rentals.slice(0, 5);
            tableBody.innerHTML = '';

            if (recentRentals.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada transaksi terbaru.</td></tr>';
                return;
            }

            recentRentals.forEach(rental => {
                const userName = rental.user?.name || 'Customer';
                const cameraName = rental.camera?.name || 'Kamera';
                const config = statusConfig[rental.status] || { label: rental.status || 'UNKNOWN', class: 'bg-slate-100 text-slate-700' };

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-semibold text-slate-900">${userName}</td>
                    <td class="px-6 py-4">${cameraName}</td>
                    <td class="px-6 py-4 text-xs">${rental.start_date || '-'} s/d ${rental.end_date || '-'}</td>
                    <td class="px-6 py-4 font-medium">${formatRupiah(rental.total_price)}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs rounded-full font-semibold ${config.class}">
                            ${config.label}
                        </span>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        })
        .catch(err => {
            console.error("Dashboard Fetch Error:", err);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-rose-500 font-medium">
                        Gagal memuat ringkasan data dashboard.<br>
                        <span class="text-xs text-slate-500 font-mono mt-1 inline-block bg-rose-50 px-3 py-1 rounded border border-rose-200">
                            Penyebab: ${err.message}
                        </span>
                    </td>
                </tr>
            `;
        });
    });
</script>
@endpush

@endsection