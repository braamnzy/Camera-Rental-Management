@extends('layouts.customer')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" id="bookingContainer">
    <div class="text-center py-12 text-slate-500 font-medium" id="loadingState">
        Memuat rincian pesanan sewa...
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        const container = document.getElementById('bookingContainer');

        if (!token) {
            alert('Sesi Anda telah berakhir. Silakan login terlebih dahulu.');
            window.location.href = '/login';
            return;
        }

        const urlParts = window.location.pathname.split('/');
        const rentalId = urlParts[urlParts.length - 1];

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(angka || 0);

        // 1. Ambil detail pesanan
        fetch(`/api/rentals/${rentalId}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(async res => {
            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.message || `HTTP ${res.status}: Pesanan tidak ditemukan.`);
            }
            return res.json();
        })
        .then(response => {
            const rental = response.data || response;
            const camera = rental.camera || { name: 'Kamera', image_url: null };

            // Format jam (10:00:00 → 10:00)
            const pickupTime = rental.pickup_time
                ? rental.pickup_time.substring(0, 5)
                : '-';

            const isDelivery = rental.pickup_method === 'delivery';
            const pickupMethodLabel = isDelivery
                ? `Dikirim - ${rental.delivery_location === 'dalam_kota' ? 'Dalam Kota' : 'Luar Kota'}`
                : 'Ambil di Toko';

            container.innerHTML = `
                <div class="mb-6">
                    <a href="/my-rentals" class="text-xs font-semibold text-slate-500 hover:text-[#2563EB] inline-flex items-center transition">
                        ← Kembali ke Riwayat Sewa
                    </a>
                </div>

                <h2 class="text-2xl font-bold text-slate-900 mb-6">Konfirmasi Pesanan Sewa</h2>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-6">
                    <!-- Info Kamera -->
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-4 mb-4">
                        <div class="w-20 h-20 bg-slate-100 rounded-xl flex items-center justify-center overflow-hidden border border-slate-200 flex-shrink-0">
                            ${camera.image_url
                                ? `<img src="${camera.image_url}" class="w-full h-full object-cover">`
                                : `<span class="text-xs text-slate-400">Cam</span>`}
                        </div>
                        <div>
                            <span class="inline-block px-2 py-0.5 bg-blue-50 text-[#2563EB] text-[10px] font-bold rounded mb-1 uppercase">${camera.brand || 'CAMERA'}</span>
                            <h3 class="font-bold text-slate-900 text-lg">${camera.name}</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Durasi: <span class="font-semibold text-slate-700">${rental.total_days} Hari</span>
                                (${rental.start_date} s/d ${rental.end_date})
                            </p>
                        </div>
                    </div>

                    <!-- INFO PENGAMBILAN / PENGIRIMAN -->
                    <div class="${isDelivery ? 'bg-purple-50 border-purple-100' : 'bg-blue-50 border-blue-100'} border rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 ${isDelivery ? 'text-purple-600' : 'text-[#2563EB]'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <h3 class="text-xs font-bold ${isDelivery ? 'text-purple-700' : 'text-[#2563EB]'} uppercase tracking-wide">
                                ${isDelivery ? '🚚 Info Pengiriman' : '📦 Info Pengambilan Barang'}
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-white rounded-lg p-3 border ${isDelivery ? 'border-purple-100' : 'border-blue-100'}">
                                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Metode</p>
                                <p class="text-sm font-bold ${isDelivery ? 'text-purple-700' : 'text-slate-900'}">${pickupMethodLabel}</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 border ${isDelivery ? 'border-purple-100' : 'border-blue-100'}">
                                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">
                                    ${isDelivery ? 'Jadwal Kirim' : 'Jadwal Ambil'}
                                </p>
                                <p class="text-sm font-bold text-slate-900">
                                    ${rental.pickup_date || '-'} <span class="${isDelivery ? 'text-purple-600' : 'text-[#2563EB]'}">${pickupTime}</span>
                                </p>
                            </div>
                        </div>

                        ${isDelivery ? `
                            <div class="mt-3 bg-white rounded-lg p-3 border border-purple-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Biaya Pengiriman</p>
                                <p class="text-sm font-bold text-purple-700">+${formatRupiah(rental.delivery_fee)}</p>
                            </div>
                        ` : ''}

                        ${rental.pickup_notes ? `
                            <div class="mt-3 bg-white rounded-lg p-3 border ${isDelivery ? 'border-purple-100' : 'border-blue-100'}">
                                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan Anda</p>
                                <p class="text-xs text-slate-700 italic">"${rental.pickup_notes}"</p>
                            </div>
                        ` : ''}

                        <p class="text-[10px] text-slate-500 mt-3">
                            ${isDelivery
                                ? 'ℹ️ Admin akan konfirmasi alamat lengkap via WhatsApp sebelum pengiriman.'
                                : 'ℹ️ Harap datang tepat waktu dengan membawa <strong>KTP asli</strong> untuk pengambilan unit.'
                            }
                        </p>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-2">Rincian Biaya</p>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">Harga Sewa (${rental.total_days} hari)</span>
                            <span class="text-slate-800 font-semibold">
                                ${formatRupiah(rental.total_price - (isDelivery ? rental.delivery_fee : 0))}
                            </span>
                        </div>
                        ${isDelivery ? `
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">Biaya Kirim (${rental.delivery_location === 'dalam_kota' ? 'Dalam Kota' : 'Luar Kota'})</span>
                                <span class="text-purple-700 font-semibold">+${formatRupiah(rental.delivery_fee)}</span>
                            </div>
                        ` : ''}
                        <div class="border-t border-slate-200 mt-2 pt-2 flex justify-between">
                            <span class="font-bold text-slate-700 text-sm">TOTAL PEMBAYARAN</span>
                            <span class="text-2xl font-extrabold text-[#2563EB]">${formatRupiah(rental.total_price)}</span>
                        </div>
                    </div>
                </div>

                <button id="pay-button" class="w-full bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold py-4 rounded-xl shadow-md text-base transition flex items-center justify-center">
                    Bayar Sekarang (Midtrans)
                </button>
            `;

            // 2. Integrasi Pembayaran Midtrans Snap
            document.getElementById('pay-button').addEventListener('click', async () => {
                const btn = document.getElementById('pay-button');
                btn.textContent = "Menghubungi Midtrans...";
                btn.disabled = true;

                try {
                    const response = await fetch('/api/payments/snap-token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify({ rental_id: rental.id })
                    });

                    const paymentData = await response.json();
                    const snapToken = paymentData.snap_token || paymentData.token;

                    if (snapToken && window.snap) {
                        window.snap.pay(snapToken, {
                            onSuccess: function(result) {
                                alert("Pembayaran berhasil! Menyegarkan status...");
                                setTimeout(() => {
                                    window.location.href = "/my-rentals";
                                }, 3000);
                            },
                            onPending: function(result) {
                                alert("Menunggu penyelesaian pembayaran Anda.");
                                window.location.href = "/my-rentals";
                            },
                            onError: function(result) {
                                alert("Pembayaran gagal!");
                                btn.textContent = "Bayar Sekarang (Midtrans)";
                                btn.disabled = false;
                            },
                            onClose: function() {
                                alert("Pop-up pembayaran ditutup. Anda dapat melanjutkan pembayaran nanti via Riwayat Sewa.");
                                btn.textContent = "Bayar Sekarang (Midtrans)";
                                btn.disabled = false;
                            }
                        });
                    } else {
                        alert(paymentData.message || "Gagal mendapatkan token pembayaran Midtrans.");
                        btn.textContent = "Bayar Sekarang (Midtrans)";
                        btn.disabled = false;
                    }
                } catch (error) {
                    console.error("Payment Snap Error:", error);
                    alert("Terjadi kesalahan sistem saat menghubungi Payment Gateway.");
                    btn.textContent = "Bayar Sekarang (Midtrans)";
                    btn.disabled = false;
                }
            });
        })
        .catch(err => {
            console.error("Booking Details Error:", err);
            container.innerHTML = `
                <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                    <p class="text-rose-500 font-bold mb-2">Gagal memuat rincian pesanan.</p>
                    <p class="text-xs text-slate-400 mb-4">${err.message}</p>
                    <a href="/catalog" class="px-4 py-2 bg-[#2563EB] text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                        ← Kembali ke Katalog
                    </a>
                </div>`;
        });
    });
</script>
@endpush