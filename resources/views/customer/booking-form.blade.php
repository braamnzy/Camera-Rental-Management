@extends('layouts.customer')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" id="bookingContainer">
    <div class="text-center py-12 text-gray-500">Memuat rincian pesanan...</div>
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

        const urlParts = window.location.pathname.split('/');
        const rentalId = urlParts[urlParts.length - 1];
        const container = document.getElementById('bookingContainer');

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);

        // 1. Ambil detail pesanan
        fetch(`/api/rentals/${rentalId}`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(data => {
                const rental = data.data || data;
                const camera = rental.camera; // Asumsi backend (Resource) meng-embed relasi camera

                container.innerHTML = `
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Konfirmasi Pesanan Sewa</h2>
            
            <div class="bg-white p-6 rounded-xl shadow border border-gray-100 mb-6">
                <div class="flex items-center gap-4 border-b pb-4 mb-4">
                    <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                        ${camera.image ? `<img src="${camera.image}" class="h-full object-contain">` : `<span class="text-xs">Cam</span>`}
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">${camera.name}</h3>
                        <p class="text-sm text-gray-500">Durasi: ${rental.total_days} Hari (${rental.start_date} s/d ${rental.end_date})</p>
                    </div>
                </div>
                <div class="flex justify-between items-center bg-blue-50 p-4 rounded">
                    <span class="font-bold text-gray-700">TOTAL PEMBAYARAN:</span>
                    <span class="text-2xl font-bold text-[#2563EB]">${formatRupiah(rental.total_price)}</span>
                </div>
            </div>

            <button id="pay-button" class="w-full bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold py-4 rounded-lg shadow-lg text-lg transition">
                Bayar Sekarang (Midtrans)
            </button>
        `;

                // 2. Logika Midtrans
                document.getElementById('pay-button').addEventListener('click', async () => {
                    const btn = document.getElementById('pay-button');
                    btn.textContent = "Menghubungi Midtrans...";
                    btn.disabled = true;

                    try {
                        // Tembak API buatan Backend 2 untuk mendapatkan Snap Token
                        const response = await fetch('/api/payments/snap-token', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({
                                rental_id: rental.id
                            })
                        });

                        const paymentData = await response.json();

                        if (paymentData.snap_token) {
                            // Panggil library Midtrans Snap yang sudah kita pasang di Layout utama
                            window.snap.pay(paymentData.snap_token, {
                                onSuccess: function(result) {
                                    /* You may add your own implementation here */
                                    alert("Pembayaran berhasil!");
                                    console.log(result);
                                    window.location.href = "/my-rentals";
                                },
                                onPending: function(result) {
                                    /* You may add your own implementation here */
                                    alert("Menunggu pembayaran Anda!");
                                    console.log(result);
                                    window.location.href = "/my-rentals";
                                },
                                onError: function(result) {
                                    /* You may add your own implementation here */
                                    alert("Pembayaran gagal!");
                                    console.log(result);
                                    btn.textContent = "Bayar Sekarang (Midtrans)";
                                    btn.disabled = false;
                                },
                                onClose: function() {
                                    /* You may add your own implementation here */
                                    alert('Anda menutup pop-up sebelum menyelesaikan pembayaran.');
                                    btn.textContent = "Bayar Sekarang (Midtrans)";
                                    btn.disabled = false;
                                }
                            });
                        } else {
                            alert("Gagal mendapatkan token pembayaran");
                            btn.textContent = "Bayar Sekarang (Midtrans)";
                            btn.disabled = false;
                        }
                    } catch (error) {
                        console.error(error);
                        alert("Terjadi kesalahan sistem.");
                        btn.textContent = "Bayar Sekarang (Midtrans)";
                        btn.disabled = false;
                    }
                });
            })
            .catch(err => {
                container.innerHTML = `<div class="text-center py-12 text-red-500">Gagal memuat pesanan. Pastikan ID pesanan benar.</div>`;
            });
    });
</script>