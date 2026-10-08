@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" id="detailContainer">
    <div class="text-center py-12 text-gray-500">Memuat detail kamera...</div>
</div>
@endsection

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('detailContainer');
        // Ambil ID dari URL (asumsi URL: /catalog/{id})
        const urlParts = window.location.pathname.split('/');
        const cameraId = urlParts[urlParts.length - 1];

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);

        fetch(`/api/cameras/${cameraId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error("Kamera tidak ditemukan");
                return res.json();
            })
            .then(data => {
                const cam = data.data || data; // Tergantung format API Resource backend

                const stockLabel = cam.stock > 0 ?
                    `<span class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-bold tracking-wide">STOK TERSEDIA (${cam.stock} UNIT)</span>` :
                    `<span class="bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-bold tracking-wide">STOK HABIS</span>`;

                const isButtonDisabled = cam.stock <= 0 ? 'disabled opacity-50 cursor-not-allowed' : 'hover:bg-[#1D4ED8]';

                container.innerHTML = `
            <!-- Breadcrumb -->
            <nav class="text-sm mb-6 text-gray-500">
                <a href="/catalog" class="hover:text-blue-600">Katalog</a> &gt; <span>${cam.brand}</span> &gt; <span class="text-gray-800 font-medium">${cam.name}</span>
            </nav>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden flex flex-col lg:flex-row">
                <!-- Sisi Kiri: Gambar dan Info -->
                <div class="w-full lg:w-2/3 p-8 border-r border-gray-100">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">${cam.name}</h1>
                    <div class="mb-6">${stockLabel}</div>
                    
                    <div class="h-80 bg-gray-100 rounded-lg mb-8 flex items-center justify-center overflow-hidden">
                        ${cam.image ? `<img src="${cam.image}" class="h-full object-contain">` : `<span class="text-gray-400">Gambar tidak tersedia</span>`}
                    </div>

                    <h3 class="text-xl font-bold mb-4">Spesifikasi & Detail</h3>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">${cam.description}</p>
                </div>

                <!-- Sisi Kanan: Kalkulator Sewa -->
                <div class="w-full lg:w-1/3 p-8 bg-slate-50 flex flex-col">
                    <h3 class="text-lg font-bold mb-6 border-b pb-2">Kalkulator Sewa</h3>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Rentang Tanggal</label>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <label class="text-xs text-gray-500">Mulai</label>
                                <input type="date" id="startDate" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm focus:ring-[#2563EB]" min="${new Date().toISOString().split('T')[0]}">
                            </div>
                            <div class="w-1/2">
                                <label class="text-xs text-gray-500">Selesai</label>
                                <input type="date" id="endDate" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm focus:ring-[#2563EB]">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded border border-gray-200 mb-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">Tarif / Hari</span>
                            <span class="font-medium" id="dailyRateDisplay" data-rate="${cam.daily_rate}">${formatRupiah(cam.daily_rate)}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-4">
                            <span class="text-gray-500">Durasi</span>
                            <span class="font-medium text-[#2563EB]"><span id="daysDisplay">0</span> Hari</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-3">
                            <span>Total Harga</span>
                            <span class="text-[#2563EB]" id="totalPriceDisplay">Rp 0</span>
                        </div>
                    </div>

                    <button id="btnBooking" class="mt-auto w-full bg-[#2563EB] text-white font-bold py-3 px-4 rounded shadow ${isButtonDisabled} transition">
                        Lanjut Sewa Sekarang
                    </button>
                    <p id="bookingError" class="text-red-500 text-xs text-center mt-2 hidden"></p>
                </div>
            </div>
        `;

                // Logika Kalkulator Tanggal
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                const daysDisplay = document.getElementById('daysDisplay');
                const totalPriceDisplay = document.getElementById('totalPriceDisplay');
                const rate = parseFloat(cam.daily_rate);

                let totalDays = 0;

                const calculateTotal = () => {
                    if (startDateInput.value && endDateInput.value) {
                        const start = new Date(startDateInput.value);
                        const end = new Date(endDateInput.value);
                        const diffTime = Math.abs(end - start);
                        // Ditambah 1 karena sewa tanggal 10 s/d 10 dihitung 1 hari
                        totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                        if (end < start) {
                            daysDisplay.textContent = '0';
                            totalPriceDisplay.textContent = 'Rp 0';
                            totalDays = 0;
                            alert("Tanggal selesai tidak boleh sebelum tanggal mulai");
                            endDateInput.value = '';
                            return;
                        }

                        daysDisplay.textContent = totalDays;
                        totalPriceDisplay.textContent = formatRupiah(rate * totalDays);
                    }
                };

                startDateInput.addEventListener('change', () => {
                    // Set minimum end date sama dengan start date
                    endDateInput.min = startDateInput.value;
                    calculateTotal();
                });
                endDateInput.addEventListener('change', calculateTotal);

                // Aksi Tombol Booking
                document.getElementById('btnBooking').addEventListener('click', () => {
                    if (cam.stock <= 0) return;

                    const token = localStorage.getItem('token');
                    const errDisplay = document.getElementById('bookingError');

                    if (!token) {
                        // Jika belum login, arahkan ke halaman login
                        window.location.href = '/login';
                        return;
                    }

                    if (totalDays === 0) {
                        errDisplay.textContent = "Silakan pilih tanggal sewa terlebih dahulu.";
                        errDisplay.classList.remove('hidden');
                        return;
                    }

                    errDisplay.classList.add('hidden');

                    // Simpan data booking sementara di SessionStorage atau arahkan langsung ke halaman booking
                    // Di sini kita langsung tembak API POST /api/rentals buatan Backend 2

                    document.getElementById('btnBooking').textContent = "Memproses...";

                    fetch('/api/rentals', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({
                                camera_id: cam.id,
                                start_date: startDateInput.value,
                                end_date: endDateInput.value,
                                quantity: 1 // Sesuai kesepakatan Opsi A di soal
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.id) { // Asumsi backend mengembalikan data rental yang baru dibuat beserta ID-nya
                                // Pindah ke halaman form pembayaran / konfirmasi
                                window.location.href = `/booking/${data.id}`;
                            } else {
                                errDisplay.textContent = data.message || "Gagal melakukan booking. Stok mungkin tidak cukup untuk tanggal tersebut.";
                                errDisplay.classList.remove('hidden');
                                document.getElementById('btnBooking').textContent = "Lanjut Sewa Sekarang";
                            }
                        })
                        .catch(err => {
                            errDisplay.textContent = "Terjadi kesalahan koneksi.";
                            errDisplay.classList.remove('hidden');
                            document.getElementById('btnBooking').textContent = "Lanjut Sewa Sekarang";
                        });
                });

            })
            .catch(err => {
                container.innerHTML = `<div class="text-center py-12 text-red-500">${err.message}</div>`;
            });
    });
</script>