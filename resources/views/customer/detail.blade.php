@extends('layouts.customer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" id="detailContainer">
    <div class="text-center py-12 text-slate-400 font-medium" id="loadingState">
        Memuat detail unit kamera...
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('detailContainer');
        const pathParts = window.location.pathname.split('/');
        const cameraId = pathParts[pathParts.length - 1];

        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(angka || 0);

        fetch(`/api/cameras/${cameraId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(async res => {
            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.message || `HTTP ${res.status}: Gagal memuat detail kamera.`);
            }
            return res.json();
        })
        .then(response => {
            const cam = response.data || response;
            renderDetail(cam);
        })
        .catch(err => {
            console.error("Detail Fetch Error:", err);
            container.innerHTML = `
                <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                    <p class="text-rose-500 font-bold mb-2">Gagal memuat detail unit kamera.</p>
                    <p class="text-xs text-slate-400 mb-4">${err.message}</p>
                    <a href="/catalog" class="px-4 py-2 bg-[#2563EB] text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                        ← Kembali ke Katalog
                    </a>
                </div>`;
        });

        function renderDetail(cam) {
            const isAvailable = (cam.stock > 0) && (cam.status === 'available');
            const today = new Date().toISOString().split('T')[0];

            container.innerHTML = `
                <div class="mb-6">
                    <a href="/catalog" class="text-xs font-semibold text-slate-500 hover:text-[#2563EB] inline-flex items-center transition">
                        ← Kembali ke Katalog
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm">
                    <!-- Foto Display Unit -->
                    <div class="bg-slate-100 rounded-xl overflow-hidden h-80 lg:h-96 border border-slate-200 flex items-center justify-center relative">
                        ${cam.image_url 
                            ? `<img src="${cam.image_url}" alt="${cam.name}" class="w-full h-full object-cover">` 
                            : `<span class="text-slate-400 text-sm">Foto Display Tidak Tersedia</span>`}
                        <div class="absolute top-4 right-4">
                            ${isAvailable 
                                ? `<span class="bg-emerald-100 text-emerald-800 text-xs px-3 py-1 rounded-full font-bold">Tersedia (${cam.stock} Unit)</span>` 
                                : `<span class="bg-rose-100 text-rose-800 text-xs px-3 py-1 rounded-full font-bold">Tidak Tersedia</span>`}
                        </div>
                    </div>

                    <!-- Informasi & Form Sewa -->
                    <div class="flex flex-col justify-between">
                        <div>
                            <span class="inline-block px-2.5 py-1 bg-blue-50 text-[#2563EB] text-xs font-bold rounded mb-3 uppercase tracking-wider">${cam.brand}</span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-2">${cam.name}</h1>
                            <div class="text-xl font-bold text-[#2563EB] mb-6">
                                ${formatRupiah(cam.daily_rate)} <span class="text-xs text-slate-400 font-normal">/ hari</span>
                            </div>
                            <div class="border-t border-b border-slate-100 py-4 mb-6">
                                <h3 class="text-xs font-bold uppercase text-slate-400 mb-2">Deskripsi & Kelengkapan</h3>
                                <p class="text-sm text-slate-600 leading-relaxed">${cam.description || 'Tidak ada deskripsi rinci untuk unit ini.'}</p>
                            </div>
                        </div>

                        <!-- Form Booking + Pickup + Delivery -->
                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800 mb-3">Pilih Tanggal Sewa</h3>

                            <!-- Tanggal Sewa -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                                        Tanggal Mulai <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" id="startDate" min="${today}"
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                                        Tanggal Selesai <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" id="endDate" min="${today}"
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                                </div>
                            </div>

                            <!-- SECTION PENGAMBILAN BARANG -->
                            <div class="border-t border-slate-200 pt-4 mb-4">
                                <h3 class="text-sm font-bold text-slate-800 mb-3">Sistem Pengambilan Barang</h3>

                                <!-- Metode Pengambilan (dua-duanya aktif) -->
                                <div class="mb-3">
                                    <label class="block text-xs font-semibold text-slate-600 mb-2">
                                        Metode <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex gap-3">
                                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                                            <input type="radio" name="pickupMethod" value="pickup" checked
                                                   class="text-[#2563EB] focus:ring-[#2563EB]">
                                            <span>Ambil di Toko</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                                            <input type="radio" name="pickupMethod" value="delivery"
                                                   class="text-[#2563EB] focus:ring-[#2563EB]">
                                            <span>Dikirim</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Jadwal Pengambilan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                                            <span id="labelPickupDate">Tanggal Ambil</span> <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" id="pickupDate" min="${today}"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                                            <span id="labelPickupTime">Jam Ambil</span> <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="time" id="pickupTime" min="09:00" max="20:00" value="10:00"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 mb-3">Toko buka jam 09:00 – 20:00 WIB</p>

                                <!-- DROPDOWN LOKASI (muncul kalau pilih Dikirim) -->
                                <div id="deliverySection" class="hidden mb-3 bg-purple-50 border border-purple-200 rounded-lg p-3">
                                    <label class="block text-xs font-semibold text-purple-800 mb-2">
                                        🚚 Lokasi Pengantaran <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="deliveryLocation"
                                            class="w-full px-3 py-2 border border-purple-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                        <option value="dalam_kota">Dalam Kota (+Rp 25.000)</option>
                                        <option value="luar_kota">Luar Kota (+Rp 50.000)</option>
                                    </select>
                                    <p class="text-[10px] text-purple-600 mt-2">Pastikan alamat lengkap dikonfirmasi via WhatsApp ke admin.</p>
                                </div>

                                <!-- Catatan -->
                                <div class="mb-2">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                                        Catatan (Opsional)
                                    </label>
                                    <textarea id="pickupNotes" rows="2"
                                              placeholder="Misal: Tolong siapkan baterai cadangan"
                                              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none"></textarea>
                                </div>
                            </div>

                            <!-- Estimasi Total -->
                            <div class="flex justify-between items-center pt-3 border-t border-slate-200 mb-4">
                                <div>
                                    <span class="text-xs text-slate-500 block">Estimasi Total (<span id="totalDaysText">0</span> Hari)</span>
                                    <span id="totalPriceText" class="text-xl font-extrabold text-[#2563EB]">${formatRupiah(0)}</span>
                                    <span id="deliveryFeeText" class="text-xs text-purple-600 block mt-1"></span>
                                </div>
                            </div>

                            <button id="btnSewa" ${!isAvailable ? 'disabled' : ''}
                                    class="w-full py-3 bg-[#2563EB] hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-sm rounded-lg transition shadow-sm">
                                ${isAvailable ? 'Sewa Sekarang' : 'Unit Tidak Tersedia'}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // === AMBIL ELEMEN ===
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const pickupDateInput = document.getElementById('pickupDate');
            const pickupTimeInput = document.getElementById('pickupTime');
            const pickupNotesInput = document.getElementById('pickupNotes');
            const totalDaysText = document.getElementById('totalDaysText');
            const totalPriceText = document.getElementById('totalPriceText');
            const deliveryFeeText = document.getElementById('deliveryFeeText');
            const btnSewa = document.getElementById('btnSewa');

            // === ELEMEN DELIVERY ===
            const deliverySection = document.getElementById('deliverySection');
            const deliveryLocation = document.getElementById('deliveryLocation');
            const labelPickupDate = document.getElementById('labelPickupDate');
            const labelPickupTime = document.getElementById('labelPickupTime');

            // === KONSTANTA BIAYA ONGKIR (harus sama dengan backend) ===
            const DELIVERY_FEE = {
                'dalam_kota': 25000,
                'luar_kota': 50000,
                'ambil_toko': 0,
            };

            // === AUTO-FILL: pickup_date = start_date ===
            startDateInput.addEventListener('change', () => {
                if (startDateInput.value) {
                    pickupDateInput.value = startDateInput.value;
                    pickupDateInput.min = startDateInput.value;
                }
                calculateTotal();
            });

            endDateInput.addEventListener('change', calculateTotal);

            // === TOGGLE DELIVERY SECTION + UBAH LABEL ===
            document.querySelectorAll('input[name="pickupMethod"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    const method = document.querySelector('input[name="pickupMethod"]:checked').value;
                    if (method === 'delivery') {
                        deliverySection.classList.remove('hidden');
                        labelPickupDate.textContent = 'Tanggal Kirim';
                        labelPickupTime.textContent = 'Jam Kirim';
                    } else {
                        deliverySection.classList.add('hidden');
                        labelPickupDate.textContent = 'Tanggal Ambil';
                        labelPickupTime.textContent = 'Jam Ambil';
                    }
                    calculateTotal();
                });
            });

            deliveryLocation.addEventListener('change', calculateTotal);

            // === KALKULASI TOTAL (termasuk ongkir) ===
            function calculateTotal() {
                const startVal = startDateInput.value;
                const endVal = endDateInput.value;

                if (!startVal || !endVal) {
                    totalDaysText.textContent = '0';
                    totalPriceText.textContent = formatRupiah(0);
                    deliveryFeeText.textContent = '';
                    return null;
                }

                const start = new Date(startVal);
                const end = new Date(endVal);

                if (end < start) {
                    alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
                    endDateInput.value = startVal;
                    return calculateTotal();
                }

                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                const rentalPrice = diffDays * (cam.daily_rate || 0);

                // Hitung ongkir
                const method = document.querySelector('input[name="pickupMethod"]:checked')?.value || 'pickup';
                let deliveryFee = 0;

                if (method === 'delivery') {
                    deliveryFee = DELIVERY_FEE[deliveryLocation.value] || 0;
                    deliveryFeeText.textContent = `+ Ongkir ${deliveryLocation.value === 'dalam_kota' ? 'Dalam Kota' : 'Luar Kota'}: ${formatRupiah(deliveryFee)}`;
                } else {
                    deliveryFeeText.textContent = '';
                }

                const totalPrice = rentalPrice + deliveryFee;

                totalDaysText.textContent = diffDays;
                totalPriceText.textContent = formatRupiah(totalPrice);

                return { diffDays, rentalPrice, deliveryFee, totalPrice, startVal, endVal };
            }

            // === SUBMIT BOOKING ===
            btnSewa.addEventListener('click', async () => {
                const token = localStorage.getItem('token');
                if (!token) {
                    alert('Silakan login terlebih dahulu untuk melakukan pemesanan.');
                    window.location.href = '/login';
                    return;
                }

                const calc = calculateTotal();
                if (!calc || calc.diffDays <= 0) {
                    alert('Silakan pilih tanggal mulai dan tanggal selesai sewa terlebih dahulu.');
                    return;
                }

                const pickupDate = pickupDateInput.value;
                const pickupTime = pickupTimeInput.value;
                const pickupMethod = document.querySelector('input[name="pickupMethod"]:checked')?.value || 'pickup';
                const pickupNotes = pickupNotesInput.value;
                const deliveryLocationVal = pickupMethod === 'delivery' ? deliveryLocation.value : null;

                if (!pickupDate) {
                    alert('Silakan pilih tanggal pengambilan.');
                    return;
                }
                if (!pickupTime) {
                    alert('Silakan pilih jam pengambilan.');
                    return;
                }
                if (pickupDate < calc.startVal) {
                    alert('Tanggal pengambilan tidak boleh sebelum tanggal sewa mulai.');
                    return;
                }
                if (pickupMethod === 'delivery' && !deliveryLocationVal) {
                    alert('Silakan pilih lokasi pengantaran.');
                    return;
                }

                btnSewa.disabled = true;
                btnSewa.textContent = 'Membuat Pesanan...';

                try {
                    const response = await fetch('/api/rentals', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify({
                            camera_id: cam.id,
                            start_date: calc.startVal,
                            end_date: calc.endVal,
                            quantity: 1,
                            pickup_date: pickupDate,
                            pickup_time: pickupTime,
                            pickup_method: pickupMethod,
                            pickup_notes: pickupNotes || null,
                            delivery_location: deliveryLocationVal,  // ← KIRIM FIELD BARU
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            const firstError = Object.values(result.errors)[0]?.[0] || result.message;
                            throw new Error(firstError);
                        }
                        throw new Error(result.message || 'Gagal membuat transaksi booking.');
                    }

                    const rentalData = result.data || result;
                    window.location.href = `/booking/${rentalData.id}`;

                } catch (err) {
                    console.error("Booking Error:", err);
                    alert(`Gagal: ${err.message}`);
                    btnSewa.disabled = false;
                    btnSewa.textContent = 'Sewa Sekarang';
                }
            });
        }
    });
</script>
@endpush